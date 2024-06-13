<?php

namespace App\Http\Controllers;

use App\Models\Category as ModelsCategory;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Category extends Controller
{
    use ResponseJson;

    function all(Request $request): JsonResponse
    {
        $request->validate([
            'limit_product' => 'nullable|numeric'
        ]);

        try {
            $categories = ModelsCategory::query()->withBase64Id();

            if ($request->limit_product > 0)
                $categories = $categories->with('product', function ($q) use ($request) {
                    $q->limit($request->limit_product)
                        ->orderBy('active', 'desc')
                        ->withBase64Id()
                        ->withBase64CategoryId()
                        ->withBase64MerchantId();
                });

            return $this->response_success('Success!', 200, $categories->get()->makeHidden(['id'])->toArray());
        } catch (\Exception $e) {
            return $this->response_error('Failed to get Categories!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }

    function detail(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|string',
            'limit_product' => 'nullable|numeric'
        ]);

        try {
            $categories = ModelsCategory::query()->withBase64Id()
                ->where('id', base64_decode($request->id));

            if ($request->limit_product > 0)
                $categories = $categories->with('product', function ($q) use ($request) {
                    $q->limit($request->limit_product)
                        ->orderBy('active', 'desc')
                        ->withBase64Id()
                        ->withBase64CategoryId()
                        ->withBase64MerchantId();
                });

            return $this->response_success('Success!', 200, $categories->first()->makeHidden(['id'])->toArray());
        } catch (\Exception $e) {
            return $this->response_error('Failed to get Categories!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }
}