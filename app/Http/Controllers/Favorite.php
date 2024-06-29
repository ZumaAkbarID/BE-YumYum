<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Favorite extends Controller
{
    use ResponseJson;

    function toggle_fav(Request $request): JsonResponse
    {
        try {
            $product = Product::find(base64_decode($request->id));
            if (!$product)
                return $this->response_error('Failed!', 404, [
                    'error' => 'not_found'
                ]);

            $user = User::find(Auth::user()->id);

            $user->toggleFavorite($product);

            return $this->response_success('Success!', 200, [
                'status' => 'okay'
            ]);
        } catch (\Exception $e) {
            return $this->response_error('Failed to favorite!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }

    function add_fav(Request $request): JsonResponse
    {
        try {
            $product = Product::find(base64_decode($request->id));
            if (!$product)
                return $this->response_error('Failed!', 404, [
                    'error' => 'not_found'
                ]);

            $user = User::find(Auth::user()->id);
            if ($user->hasFavorited($product))
                return $this->response_success('Already!', 204, [
                    'status' => 'has_favorited'
                ]);

            $user->favorite($product);

            return $this->response_success('Success!', 200, [
                'status' => 'favorited'
            ]);
        } catch (\Exception $e) {
            return $this->response_error('Failed to favorite!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }

    function del_fav(Request $request): JsonResponse
    {
        try {
            $product = Product::find(base64_decode($request->id));
            if (!$product)
                return $this->response_error('Failed!', 404, [
                    'error' => 'not_found'
                ]);

            $user = User::find(Auth::user()->id);
            if (!$user->hasFavorited($product))
                return $this->response_success('Not Favorited!', 204, [
                    'status' => 'not_favorited'
                ]);

            $user->unfavorite($product);

            return $this->response_success('Success!', 200, [
                'status' => 'unfavorited'
            ]);
        } catch (\Exception $e) {
            return $this->response_error('Failed to unfavorite!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }

    function list_fav(): JsonResponse
    {
        try {
            $user = User::where('id', Auth::user()->id)->first();

            return $this->response_success(
                'Success!',
                200,
                Product::whereHas('favoriters', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                    ->withBase64Id()
                    ->get()
                    ->toArray()
            );
        } catch (\Exception $e) {
            return $this->response_error('Failed to get favorite!', 503, [
                'error' => $e->getMessage()
            ]);
        }
    }
}