<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\User;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends Controller
{
    use ResponseJson;

    function login_gate(Request $request)
    {
        $request->validate([
            'as_merchant' => 'required',
            'username' => 'required',
            'password' => 'required',
            'device_id' => 'required',
        ]);

        if ($request->as_merchant)
            return $this->login_merchant($request);
        else
            return $this->login_mhs($request);
    }

    function login_mhs(Request $request): JsonResponse
    {
        if (Auth::attempt(['npm' => $request->username, 'password' => $request->password], true)) {
            $user = User::where('id', Auth::user()->id)->withBase64Id()->first();

            if (is_null($user->device_id) || $request->device_id !== $user->device_id)
                $user->update([
                    'device_id' => $request->device_id
                ]);

            $user->tokens()->delete();

            return $this->response_success('Success!', 200, [
                'user' => $user->makeHidden(['id', 'created_at', 'updated_at']),
                'scope' => 'customer',
                'token' => $user->createToken('authentication')->plainTextToken
            ]);
        } else {
            return $this->response_error('Invalid Credentials', 401, [
                'error' => 'authorization'
            ]);
        }
    }

    function login_merchant(Request $request): JsonResponse
    {
        if (Auth::guard('merchant')->attempt(['username' => $request->username, 'password' => $request->password], true)) {
            $merchant = Merchant::where('id', Auth::guard('merchant')->user()->id)->withBase64Id()->first();

            if ($request->device_id !== $merchant->device_id)
                $merchant->update([
                    'device_id' => $request->device_id
                ]);

            $merchant->tokens()->delete();

            return $this->response_success('Success!', 200, [
                'merchant' => $merchant->makeHidden(['id', 'created_at', 'updated_at']),
                'scope' => 'merchant',
                'token' => $merchant->createToken('authentication')->plainTextToken
            ]);
        } else {
            return $this->response_error('Invalid Credentials', 401, [
                'error' => 'authorization'
            ]);
        }
    }
}
