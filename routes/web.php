<?php

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => true,
        'message' => 'Ngapain kocak kesini'
    ]);
});

Route::get('login', function (Request $request) {
    $amikom_service = 'https://api.forumasisten.or.id/bridge/login';
    $UA = "f0rum-4515t3n";

    $client = new Client([
        'base_uri' => 'https://forumasisten.or.id/',
        'verify' => false,
    ]);

    try {
        $result = $client->post($amikom_service, [
            'headers' => [
                'User-Agent' => $UA
            ],
            'form_params' => [
                'u' => $request->npm,
                'p' => $request->password
            ]
        ]);

        $response = $result->getBody()->getContents();
        $data = json_decode($response);

        if (!User::where('npm', $request->npm)->first())
            User::create([
                'name' => $data->Nama,
                'degre' => $data->Prodi,
                'photo' => $data->Foto,
                'npm' => $data->Nim,
                'email' => $data->Email,
                'password' => Hash::make('passw')
            ]);

        dd($data);
    } catch (\Exception $e) {
        dd($e);
    }
});
