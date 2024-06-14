<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VerifyToken extends Controller
{
    use ResponseJson;

    function verify(): JsonResponse
    {
        try {
            $user = User::where('id', Auth::user()->id)->withBase64Id()->first();

            return $this->response_success('Success!', 200, [
                'user' => $user->makeHidden(['id', 'created_at', 'updated_at']),
                'scope' => 'customer',
                'is_valid_token' => true
            ]);
        } catch (\Exception $e) {
            return $this->response_error('Failed!', 422, [
                'is_valid_token' => false
            ]);
        }
    }
}
