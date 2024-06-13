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
                ->withBase64Id()
                ->find(base64_decode($request->id))
                ->makeHidden(['id']);

            return $this->response_success('Success!', 200, $merchants->toArray());
        } catch (\Exception $e) {
            return $this->response_error('Failed to get Merchant!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
