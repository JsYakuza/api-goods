<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'Pending';
    const STATUS_BUYED = 'Buyed';
    const STATUS_RENTED = 'Rented';
    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_BUYED,
        self::STATUS_RENTED,
    ];

    const AVAILABLE_HOURS_FOR_RENT = [
        4,
        8,
        12,
        24
    ];

    protected $fillable = [
        'title',
        'price',
        'status',
        'rented_time',
        'user_id',
        'max_rented_time',
        'slug'
    ];

    public function purchases(): HasOne
    {
        return $this->hasOne(Purchase::class);
    }

    public function buyOrRent(array $data, bool $isRent): JsonResponse
    {
        $productStatus = $isRent ? self::STATUS_RENTED : self::STATUS_BUYED;
        $purchaseStatus = $isRent ? Purchase::STATUS_RENT : Purchase::STATUS_SELLING;

        try {
            $product = self::findOrFail($data['product_id']);

            if ($product->status !== self::STATUS_PENDING) {
                return response()->json([
                    'Message' => "Product is {$product->status}",
                ], 404);
            }

            $rentedTime = '9999-12-31 23:59:59';
            $maxRentedTime = '9999-12-31 23:59:59';

            if ($isRent) {

                if ($this->checkAvailableHoursForRent($data['rented_time'])) {
                    return $this->returnAnswerForAvailableHoursForRent();
                }

                $rentedTime = Carbon::now()
                    ->addHours($data['rented_time'])
                    ->format('Y-m-d H:i:s');

                $maxRentedTime = Carbon::now()
                    ->addDay()
                    ->format('Y-m-d H:i:s');
            }

            $product->update([
                'status' => $productStatus,
                'rented_time' => $rentedTime,
                'max_rented_time' => $maxRentedTime,
                'user_id' => $data['user_id']
            ]);
            $user = User::findOrFail($data['user_id']);
            Purchase::create([
                'status' => $purchaseStatus,
                'user_id' => $data['user_id'],
                'product_id' => $data['product_id']
            ]);

            return response()->json([
                'product' => $product,
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function continueRent(array $data): JsonResponse
    {
        try {
            $product = self::findOrFail($data['product_id']);

            if ($product->status !== self::STATUS_RENTED) {
                return response()->json([
                    'Message' => "Product is {$product->status}",
                ], 404);
            }

            if ($product->user_id !== (int) $data['user_id']) {
                return response()->json([
                    'Message' => "Rented by another User",
                ], 404);
            }

            if ($this->checkAvailableHoursForRent($data['rented_time'])) {
                return $this->returnAnswerForAvailableHoursForRent();
            }

            $rentedTime = Carbon::parse($product->rented_time)->addHours($data['rented_time']);
            $maxRentedTime = Carbon::parse($product->max_rented_time);

            if ($rentedTime > $maxRentedTime) {
                return response()->json([
                    'Message' => "You can't rent product more than 24 hours",
                ], 404);
            }

            $product->update([
                'status' => self::STATUS_RENTED,
                'rented_time' => $rentedTime->format('Y-m-d H:i:s'),
                'user_id' => $data['user_id']
            ]);
            $user = User::findOrFail($data['user_id']);
            Purchase::create([
                'status' => Purchase::STATUS_RENT,
                'user_id' => $data['user_id'],
                'product_id' => $data['product_id']
            ]);

            return response()->json([
                'product' => $product,
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    private function checkAvailableHoursForRent(string $rentedTime): bool
    {
        return !in_array($rentedTime, self::AVAILABLE_HOURS_FOR_RENT);
    }

    private function returnAnswerForAvailableHoursForRent(): JsonResponse
    {
        $availableHours = implode(', ', self::AVAILABLE_HOURS_FOR_RENT);
        return response()->json([
            'Message' => "You can rent product on {$availableHours}",
        ], 404);
    }

    public function checkStatus(array $data): JsonResponse
    {
        try {
            $product = self::findOrFail($data['product_id']);

            if (!$product->slug) {
                $product->slug = bin2hex(random_bytes(16));
                $product->save();
            }

            return response()->json([
                'status' => $product->status,
                'code' => $product->slug,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
