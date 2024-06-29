<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VerifyToken extends Controller
{
    use ResponseJson;

    function verify(): JsonResponse
    {
        try {
            return $this->response_success('Success!', 200, [
                'scope' => isset(Auth::user()->npm) ? 'customer' : 'merchant',
                'is_valid_token' => true
            ]);
        } catch (\Exception $e) {
            return $this->response_error('Failed!', 422, [
                'is_valid_token' => false
            ]);
        }
    }
}
