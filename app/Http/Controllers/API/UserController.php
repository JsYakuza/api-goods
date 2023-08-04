<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function history(int $userId, Purchase $purchase): JsonResponse
    {
        return $purchase->getHistory($userId);
    }
}
