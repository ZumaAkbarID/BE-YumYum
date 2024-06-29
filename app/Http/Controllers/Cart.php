<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetails;
use App\Models\TransactionMerchant;
use App\Models\User;
use App\Traits\GeneratesUniqueCode;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Cart extends Controller
{
    use ResponseJson, GeneratesUniqueCode;

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

        $isCashless = $request->cashless ?? true;

        $isThereProblemProduct = false;

        $totalPriceAll = 0;
        $transactionMerchantData = [];
        $transactionDetailData = [];

        try {
            DB::beginTransaction();
            $reff_id = $this->generateUniqueCode();

            foreach ($request->data as $item) {
                if ($isThereProblemProduct)
                    break;

                $totalPriceMerchant = 0;
                $currentIdMerchant = null;

                foreach ($item['product'] as $subItem) {
                    $product = Product::where('id', base64_decode($subItem['id_product']))->with('merchant')->first();
                    if ($product->active == 0 || $product->merchant->is_open == 0) {
                        $isThereProblemProduct = "inactive_merchant_product";
                        break;
                    }

                    if ($currentIdMerchant == null)
                        $currentIdMerchant = $product->merchant->id;

                    if ($currentIdMerchant !== $product->merchant->id) {
                        $isThereProblemProduct = "invalid_product_group";
                        break;
                    }

                    $totalPriceAll += (int) $product->getRawOriginal('price') * (int) $subItem['amount'];
                    $totalPriceMerchant += (int) $product->getRawOriginal('price') * (int) $subItem['amount'];

                    $transactionDetailData[] = [
                        'transaction_id' => ":transaction_id",
                        'transaction_merchant_id' => ":transaction_merchant_id",
                        'merchant_id' => $currentIdMerchant,
                        'product_name' => $product->name,
                        'product_price' => $product->getRawOriginal('price'),
                        'amount' => $subItem['amount']
                    ];
                }

                $transactionMerchantData[] = [
                    'transaction_id' => ":transaction_id",
                    'merchant_id' => $currentIdMerchant,
                    'total_price' => $totalPriceMerchant
                ];
            }

            $createTransaction = Transaction::create([
                'reff_id' => $reff_id,
                'user_id' => Auth::user()->id,
                'cashless' => $isCashless,
                'total_price' => $totalPriceAll
            ]);

            $listTransactionMerchantId = [];

            foreach ($transactionMerchantData as &$item) {
                if ($item['transaction_id'] === ':transaction_id') {
                    $item['transaction_id'] = $createTransaction->id;
                }

                $insertTrx = TransactionMerchant::create($item);
                $listTransactionMerchantId[] = $insertTrx->id;
            }

            $i = 0;
            $currentTrxMerchantId = null; // AIV
            $currentIdMerchant = null; // 123
            foreach ($transactionDetailData as &$item) {
                if ($item['transaction_id'] === ':transaction_id') {
                    $item['transaction_id'] = $createTransaction->id;
                }

                $currentTrxMerchantId = $listTransactionMerchantId[$i];

                if ($currentIdMerchant == null)
                    $currentIdMerchant = $item['merchant_id'];
                elseif ($currentIdMerchant !== $item['merchant_id']) {
                    $i++;

                    $currentIdMerchant = $item['merchant_id'];
                }

                if ($item['transaction_merchant_id'] === ':transaction_merchant_id') {
                    $item['transaction_merchant_id'] = $currentTrxMerchantId;
                }

                TransactionDetails::create($item);
            }

            if ($isThereProblemProduct) {
                DB::rollBack();
                return $this->response_error('Product isn\'t available to purchase in you\'r cart!', 403, [
                    'error' => $isThereProblemProduct
                ]);
            }

            DB::commit();
            return $this->response_success('Success!', 200, [
                'status' => 'saved',
                'midtrans_snap_token' => time(),
                'note_for_fe_dev' => 'saved = data tersimpan, pasti unpaid sih. mau pake midtrans gak?'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->response_error('Failed to checkout!', 503, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
