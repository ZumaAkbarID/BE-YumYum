<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Traits\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Order extends Controller
{
    use ResponseJson;

    public $currentDate;

    function __construct()
    {
        $this->currentDate = date('YYYY-MM-DD');
    }

    function incoming(): JsonResponse
    {
    }
}
