<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\User;
use App\Traits\ResponseJson;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Profile extends Controller
{
    use ResponseJson;

    function full(): JsonResponse
    {
        try {
            $scope = isset(Auth::user()->npm) ? 'customer' : 'merchant';
            $profile = null;

            if ($scope == 'merchant')
                $profile = Merchant::find(Auth::user()->id)->makeHidden(['id', 'created_at', 'updated_at'])->toArray();
            else if ($scope == 'customer')
                $profile = User::find(Auth::user()->id)->makeHidden(['id', 'created_at', 'updated_at'])->toArray();
            else throw new AuthorizationException();

            return $this->response_success('Success!', 200, [
                'scope' => $scope,
                'profile' => $profile,
            ]);
        } catch (\Exception $e) {
            return $this->response_error('Failed to get profile!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
