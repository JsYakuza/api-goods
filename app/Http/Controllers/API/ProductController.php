<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function buy(Request $request, Product $product): JsonResponse
    {
        return $product->buyOrRent(data: $request->all(), isRent: false);
    }

    public function rent(Request $request, Product $product): JsonResponse
    {
        return $product->buyOrRent(data: $request->all(), isRent: true);
    }

    public function continueRent(Request $request, Product $product): JsonResponse
    {
        return $product->continueRent(data: $request->all());
    }

    public function checkStatus(Request $request, Product $product): JsonResponse
    {
        return $product->checkStatus(data: $request->all());
    }
}
