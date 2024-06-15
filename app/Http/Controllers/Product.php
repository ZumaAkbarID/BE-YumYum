<?php

namespace App\Http\Controllers;

use App\Models\Product as ModelsProduct;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Product extends Controller
{
    use ResponseJson;

    function all(Request $request): JsonResponse
    {
        $request->validate([
            'limit_product' => 'nullable|numeric'
        ]);

        try {
            $products = ModelsProduct::query()->withBase64Id();

            if ($request->limit_product > 0)
                $products = $products->limit($request->limit_product);

            if ($request->has('hide_category') && !$request->hide_category)
                $products = $products->with('category', function ($q) {
                    $q->withBase64Id();
                });

            if ($request->has('hide_merchant') && !$request->hide_merchant)
                $products = $products->with('merchant', function ($qc) {
                    $qc->withBase64Id();
                });

            if ($request->has('search'))
                $products = $products->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');

            return $this->response_success('Success!', 200, $products->orderBy('active', 'desc')->get()->makeHidden(['id'])->toArray());
        } catch (\Exception $e) {
            return $this->response_error('Failed to get Products!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }

    function detail(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        try {
            $products = ModelsProduct::query()
                ->where('id', base64_decode($request->id))
                ->withBase64Id();

            if ($request->has('hide_category') && !$request->hide_category)
                $products = $products->with('category', function ($q) {
                    $q->withBase64Id();
                });

            if ($request->has('hide_merchant') && !$request->hide_merchant)
                $products = $products->with('merchant', function ($qc) {
                    $qc->withBase64Id();
                });

            if ($request->has('search'))
                $products = $products->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');

            return $this->response_success('Success!', 200, $products->first()->makeHidden(['id'])->toArray());
        } catch (\Exception $e) {
            return $this->response_error('Failed to get Products!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
