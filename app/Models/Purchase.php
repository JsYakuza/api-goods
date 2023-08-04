<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class Purchase extends Model
{
    use HasFactory;

    const STATUS_SELLING = 'Selling';
    const STATUS_RENT = 'Rent';
    const STATUSES = [
        self::STATUS_SELLING,
        self::STATUS_RENT,
    ];

    protected $fillable = [
        'status',
        'user_id',
        'product_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    public function getHistory(int $userId): JsonResponse
    {
        try {
            $purchases = Purchase::where('user_id', '=', $userId)
                ->with('product:*')
                ->with('user:*')
                ->get();

            return response()->json([
                'history' => $purchases,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
