<?php

namespace App\Http\Controllers;

use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Merchant extends Controller
{
    use ResponseJson;

    function all(): JsonResponse
    {
    }
}
