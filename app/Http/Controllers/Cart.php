<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Cart extends Controller
{
    use ResponseJson;

    function fetch_by_id(Request $request): JsonResponse
    {
        $request->validate([
            '*.id_product' => 'required|string',
            '*.amount' => 'required|numeric',
        ]);

        try {

            $data = $request->all();

            $productIds = array_map(function ($product) {
                return (int) base64_decode($product['id_product']);
            }, $data);

            $productAmount = array_map(function ($product) {
                return $product['amount'];
            }, $data);

            $query = Product::whereIn('id', $productIds)->with('merchant', function ($qc) {
                $qc->withBase64Id();
            })
                ->orderBy('active', 'desc')
                ->get();

            $result = [];
            $i = 0;
            $totalBayar = 0;
            foreach ($query as $item) {
                if ($item->merchant->is_open == 0)
                    $available = 1;
                else
                    $available = 0;

                $result[] = [
                    'product' => [
                        'id' => base64_encode($item->id),
                        'image' => $item->image,
                        'name' => $item->name,
                        'price' => $item->price,
                        'amount' => $productAmount[$i],
                        'total_price' => "Rp " . number_format($item->getRawOriginal('price') * $productAmount[$i], 0, ',', '.'),
                        'active' => ($available == 0) ? 0 : $item->active
                    ],
                    'merchant' => [
                        'name' => $item->merchant->name,
                        'photo' => $item->merchant->photo,
                        'is_open' => $available
                    ]
                ];

                if ($available == 1 && $item->active == 1)
                    $totalBayar += $item->getRawOriginal('price') * $productAmount[$i];

                $i++;
            }

            return $this->response_success('Success!', 200, [
                "total" => "Rp " . number_format($totalBayar, 0, ',', '.'),
                "products" => $result,
            ]);
        } catch (\Exception $e) {
            return $this->response_error('Failed to get cart!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }

    function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'cashless' => 'required|boolean',
            'data.*.deliver' => 'required|boolean',
            'data.*.note' => 'nullable|string',
            'data.*.product.*.id_product' => 'required|string',
            'data.*.product.*.amount' => 'required|numeric',
        ]);

        $isCashless = $request->cashless;
        $totalMerchant = count($request->data);
        $isThereProblemProduct = false;
        $sample = [];

        try {
            // DB::beginTransaction();

            foreach ($request->data as $item) {
                if ($isThereProblemProduct)
                    break;

                $currentIdMerchant = null;
                $currentMerchantProductCount = count($item['product']);
                $i = 1;

                foreach ($item['product'] as $subItem) {
                    $product = Product::where('id', base64_decode($subItem['id_product']))->with('merchant')->first();
                    if ($product->active == 0 || $product->merchant->is_open == 0) {
                        $isThereProblemProduct = "inactive_merchant_product";
                        break;
                    }

                    if ($currentIdMerchant !== null)
                        $currentIdMerchant = $product->merchant->id;

                    if ($product->merchant_id !== $product->merchant->id) {
                        $isThereProblemProduct = "invalid_product_group";
                        break;
                    }
                }
            }

            if ($isThereProblemProduct)
                return $this->response_error('Product isn\'t available to purchase in you\'r cart!', 403, [
                    'error' => $isThereProblemProduct
                ]);

            // DB::commit();
            return $this->response_success('Success!', 200, [
                'status' => 'saved',
                'data' => $sample,
                'note_for_fe_dev' => 'saved = data tersimpan, pasti unpaid sih'
            ]);
        } catch (\Exception $e) {
            // DB::rollback();
            return $this->response_error('Failed to get cart!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
