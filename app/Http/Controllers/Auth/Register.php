<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Register extends Controller
{
    use ResponseJson;

    private function checkRemoteFile($url): bool
    {
        $fp = @fopen($url, "r");
        if ($fp !== false)
            fclose($fp);

        return ($fp);
    }


    function register_gate(Request $request)
    {
        $request->validate([
            'as_merchant' => 'required|boolean',
            'name' => 'required:min:4',
            'degree' => 'required',
            'email' => 'required|email:dns|unique:users,email',
            'username' => 'required|unique:users,npm',
            'password' => 'required|min:5',
        ]);

        if ($request->as_merchant)
            return true;
        else
            return $this->register_mhs($request);
    }

    function register_mhs(Request $request)
    {
        $isAmikom = true;

        try {
            $endMail = explode("@", strtolower($request->email));
            if ($endMail[1] !== 'students.amikom.ac.id')
                $isAmikom = false;
        } catch (\Exception $e) {
            return $this->response_error(
                "Terjadi kesalahan ketika validasi Email",
                503,
                [
                    "status" => "email_amikom",
                    "message" => $e->getMessage()
                ]
            );
        }

        $patternNim = '/^\d{2}\.\d{2}\.\d{4}$/';

        $year = "20" . str_split($request->username, 2)[0];
        $npm = str_replace(".", "_", $request->username);
        $url = "https://fotomhs.amikom.ac.id/$year/$npm.jpg";

        if ((int) $year < 1992 && (int) $year > date('Y') || preg_match($patternNim, $request->username) === 1)
            $isAmikom = false;

        // time out terus
        // try {
        //     if ($isAmikom)
        //         $isAmikom = $this->checkRemoteFile($url);
        // } catch (\Exception $e) {
        //     return $this->response_error(
        //         "Terjadi kesalahan ketika validasi Username",
        //         503,
        //         [
        //             "status" => "server_amikom",
        //             "message" => $e->getMessage()
        //         ]
        //     );
        // }

        if (!$isAmikom) {
            return $this->response_error(
                "Pendaftaran hanya untuk mahasiswa Universitas Amikom Yogyakarta!",
                400,
                [
                    "status" => "is_not_amikom"
                ]
            );
        }

        try {
            $data = [
                'name' => $request->name,
                'degre' => $request->degree,
                'photo' => $url,
                'npm' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ];

            $user = User::create($data);

            return $this->response_success('Success!', 200, [
                'user' => $user->makeHidden(['id', 'created_at', 'updated_at']),
                'scope' => 'customer'
            ]);
        } catch (\Exception $e) {
            return $this->response_error(
                "Terjadi kesalahan ketika membuat akun",
                500,
                [
                    "status" => "internal_server",
                    "message" => $e->getMessage()
                ]
            );
        }
    }
}
