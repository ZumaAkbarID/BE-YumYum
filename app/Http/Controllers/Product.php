<?php

namespace App\Http\Controllers;

use App\Models\Product as ModelsProduct;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Product extends Controller
{
    use ResponseJson;

    function all(Request $request): JsonResponse
    {
        $request->validate([
            'limit_product' => 'nullable|numeric'
        ]);

        try {
            $products = ModelsProduct::query()->withBase64Id()
                ->join('merchants', 'products.merchant_id', '=', 'merchants.id')
                ->where('merchants.is_open', 1);

            if ($request->limit_product > 0) {
                $products = $products->limit($request->limit_product);
            }

            if ($request->has('hide_category') && !$request->hide_category) {
                $products = $products->with('category', function ($q) {
                    $q->withBase64Id();
                });
            }

            if ($request->has('hide_merchant') && !$request->hide_merchant) {
                $products = $products->with('merchant', function ($qc) {
                    $qc->withBase64Id();
                });
            }

            if ($request->has('search')) {
                $products = $products->where(function ($q) use ($request) {
                    $q->where('products.name', 'like', '%' . $request->search . '%')
                        ->orWhere('products.description', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->has('hide_inactive_product') && $request->hide_inactive_product) {
                $products = $products->where('products.active', 1);
            }

            $products = $products->orderBy('merchants.is_open', 'desc')
                ->orderBy('products.active', 'desc')
                ->inRandomOrder()
                ->select('products.*', DB::raw('TO_BASE64(products.id) as encrypted_id'));

            return $this->response_success(
                'Success!',
                200,
                $products->get()->makeHidden(['id'])->toArray()
            );
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
