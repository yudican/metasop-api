<?php

namespace App\Http\Controllers;

use App\Models\UserBalance;
use App\Models\UserDeposite;
use Duitku\Config;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public $duitkuConfig;
    // constructor
    public function __construct()
    {
        $config = new Config(env('DUITKU_API_KEY'), env("DUITKU_MERCHANT_CODE"));
        // false for production mode
        // true for sandbox mode
        $config->setSandboxMode(env('DUITKU_SANBOX_MODE', 'true'));
        // set sanitizer (default : true)
        $config->setSanitizedMode(env('DUITKU_SANITIZE', 'false'));
        // set log parameter (default : true)
        $config->setDuitkuLogs(false);

        $this->duitkuConfig = $config;
    }

    //  duiktu payment callback
    public function duitkuCallback(Request $request)
    {

        $duitku_api_key = env('DUITKU_API_KEY');
        $duitku_merchant_code = $request->merchantCode;
        $duitku_amount = $request->amount;
        $merchantOrderId = $request->merchantOrderId;

        $resultCode = $request->resultCode;

        $signature = $request->signature;
        $signature_check = hash('sha256', $duitku_merchant_code . $duitku_amount . $merchantOrderId . $duitku_api_key);

        if ($signature_check == $signature) {
            if ($resultCode == '00') {
                UserDeposite::where('trx_id', $merchantOrderId)->update([
                    'status' => 1,
                ]);

                $user_id = UserDeposite::where('trx_id', $merchantOrderId)->first()->user_id;
                UserBalance::create([
                    'user_id' => $user_id,
                    'balance' =>  $duitku_amount,
                    'type' => 'CR',
                ]);
            }

            if ($resultCode == '01') {
                UserDeposite::where('trx_id', $merchantOrderId)->update([
                    'status' => 3,
                ]);
            }

            return response()->json([
                'message' => 'Success',
                'data' => $request->all(),
            ], 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'data' => 'Signature Not Match',
            ], 400);
        }
    }
}
