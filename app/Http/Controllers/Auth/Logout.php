<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Logout extends Controller
{
    use ResponseJson;

    function logout(): JsonResponse
    {
        try {
            $user = User::find(Auth::user()->id);
            $user->tokens()->delete();

            return $this->response_success('Success!', 200, [
                'status' => 'logged_out'
            ]);
        } catch (\Exception $e) {
            return $this->response_error('Failed to logout!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
