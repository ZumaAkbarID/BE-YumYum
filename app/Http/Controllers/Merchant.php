<?php

namespace App\Http\Controllers;

use App\Models\Merchant as ModelsMerchant;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Merchant extends Controller
{
    use ResponseJson;

    function all(Request $request): JsonResponse
    {
        try {

            $merchants = ModelsMerchant::query()->withBase64Id();

            if ($request->has('open'))
                $merchants = $merchants->where('is_open', strtolower($request->open) == 'true' ? 1 : 0);

            if ($request->has('limit_product') && $request->limit_product > 0)
                $merchants = $merchants->with('product', function ($q) use ($request) {
                    if ($request->has('hide_inacticve_product') && $request->hide_inacticve_product)
                        $q = $q->where('active', 1);

                    $q = $q->limit($request->limit_product);

                    if ($request->has('hide_category') && !$request->hide_category)
                        $q = $q->with('category', function ($qc) {
                            $qc->withBase64Id();
                        });

                    $q->orderBy('active', 'desc')
                        ->withBase64Id()
                        ->withBase64CategoryId()
                        ->withBase64MerchantId();
                });

            if ($request->has('search'))
                $merchants = $merchants->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%');

            return $this->response_success(
                'Success!',
                200,
                $merchants->get()
                    ->makeHidden(['id', 'device_id', 'created_at', 'updated_at'])
                    ->toArray()
            );
        } catch (\Exception $e) {
            return $this->response_error('Failed to get Merchant!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }

    function detail(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|string'
        ]);

        try {
            $merchants = ModelsMerchant::query()
                ->withBase64Id();
            if ($request->has('limit_product') && $request->limit_product > 0 || $request->limit_product == -1)
                $merchants = $merchants->with('product', function ($q) use ($request) {
                    if ($request->has('hide_inacticve_product') && $request->hide_inacticve_product)
                        $q = $q->where('active', 1);

                    if ($request->limit_product > 0)
                        $q = $q->limit($request->limit_product);

                    if ($request->has('hide_category') && !$request->hide_category)
                        $q = $q->with('category', function ($qc) {
                            $qc->withBase64Id();
                        });

                    $q->orderBy('active', 'desc')
                        ->withBase64Id()
                        ->withBase64CategoryId()
                        ->withBase64MerchantId();
                });

            return $this->response_success(
                'Success!',
                200,
                $merchants
                    ->find(base64_decode($request->id))
                    ->makeHidden(['id'])->toArray()
            );
        } catch (\Exception $e) {
            return $this->response_error('Failed to get Merchant!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
