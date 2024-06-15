<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            foreach ($query as $item) {
                if ($item->merchant->is_open == 0)
                    $available = 1;
                else
                    $available = 0;

                $result[] = [
                    'product' => [
                        'image' => $item->image,
                        'name' => $item->name,
                        'price' => $item->price,
                        'amount' => $productAmount[$i],
                        'total_price' => $item->getRawOriginal('price') * $productAmount[$i++],
                        'active' => ($available == 0) ? 0 : $item->active
                    ],
                    'merchant' => [
                        'name' => $item->merchant->name,
                        'photo' => $item->merchant->photo,
                        'is_open' => $available
                    ]
                ];
            }

            return $this->response_success('Success!', 200, $result);
        } catch (\Exception $e) {
            //throw $th;
        }
    }
}
