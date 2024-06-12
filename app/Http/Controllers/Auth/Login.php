<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends Controller
{
    use ResponseJson;

    function login_mhs(Request $request): JsonResponse
    {
        $request->validate(
            [
                'npm' => 'required',
                'password' => 'required'
            ]
        );

        if (Auth::attempt(['npm' => $request->npm, 'password' => $request->password], true)) {
            $user = User::find(Auth::user()->id);

            $user->tokens()->delete();

            return $this->response_success('Success!', 200, [
                'user' => $user,
                'scope' => 'customer',
                'token' => $user->createToken('authentication')->plainTextToken
            ]);
        } else {
            return $this->response_error('Invalid Credentials', 401, [
                'error' => 'authorization'
            ]);
        }
    }
}