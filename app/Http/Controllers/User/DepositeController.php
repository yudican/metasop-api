<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\PaymentController;
use App\Models\PaymentMethod;
use App\Models\UserDeposite;
use Duitku\Pop;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DepositeController extends PaymentController
{
    // get payment method 
    public function getPaymentMethod(Request $request)

    {
        // guzzle http client post request
        $client = new Client();
        $orderId = 'INV-DEP-' . time();
        $signature = hash('sha256', env('DUITKU_MERCHANT_CODE') . $request->deposit_amount . $orderId . env('DUITKU_API_KEY'));
        try {
            $response = $client->request('POST', env('DUITKU_URL') . '/merchant/paymentmethod/getpaymentmethod', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    "merchantcode" => env('DUITKU_MERCHANT_CODE'),
                    "amount" => $request->deposit_amount,
                    "datetime" => date('Y-m-d H:i:s'),
                    "signature" => $signature
                ])
            ]);

            $responseJSON = json_decode($response->getBody(), true);


            return response()->json([
                'message' => 'Success',
                'data' => [
                    'payment_methods' => $responseJSON['paymentFee'],
                    'order_id' => $orderId,
                    'signature' => $signature,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error',
                'data' => 'Metode Pembayaran Tidak Tersedia',
                'error' => $th->getMessage(),
            ], 400);
        }
    }

    // create deposite
    public function createDeposite(Request $request)
    {
        // validate request
        $validator = Validator::make($request->all(), [
            'payment' => 'required',
            'deposit_amount' => 'required|integer',
            'order_id' => 'required',
        ]);

        // validate failed
        if ($validator->fails()) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Maaf, Silahkan isi semua form yang tersedia',
                'messages' => $validator->errors(),
            ];
            return response()->json($respon, 401);
        }

        if ($request->deposit_amount < 10000) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Maaf, Minimal deposit Rp. 10.000',
            ];
            return response()->json($respon, 401);
        }

        $user = auth()->user();

        $paymentMethod      = $request->payment['paymentMethod'];
        $paymentAmount      = $request->deposit_amount; // Amount
        $email              = $user->email; // your customer email
        $productDetails     = "Deposit";
        $merchantOrderId    = $request->order_id; // from merchant, unique   
        $additionalParam    = ''; // optional
        $merchantUserInfo   = ''; // optional
        $customerVaName     = $user->name; // display name on bank confirmation display
        $callbackUrl        = route('payment.callback'); // url for callback
        $returnUrl          = 'https://ppob-dashboard.vercel.app/'; // url for redirect
        $expiryPeriod       = 60; // set the expired time in minutes


        // Item Details
        $item1 = array(
            'name'      => $productDetails,
            'price'     => $paymentAmount,
            'quantity'  => 1
        );

        $itemDetails = array(
            $item1
        );

        $params = array(
            'paymentMethod'     => $paymentMethod,
            'paymentAmount'     => $paymentAmount,
            'merchantOrderId'   => $merchantOrderId,
            'productDetails'    => $productDetails,
            'additionalParam'   => $additionalParam,
            'merchantUserInfo'  => $merchantUserInfo,
            'customerVaName'    => $customerVaName,
            'email'             => $email,
            'itemDetails'       => $itemDetails,
            'callbackUrl'       => $callbackUrl,
            'returnUrl'         => $returnUrl,
            'expiryPeriod'      => $expiryPeriod,
        );

        try {
            DB::beginTransaction();
            $responseDuitkuPop = Pop::createInvoice($params, $this->duitkuConfig);
            $response = json_decode($responseDuitkuPop, true);
            UserDeposite::create([
                'user_id' => $user->id, // 'user_id' => auth()->user()->id, // 'use
                'payment_code' => $request->payment['paymentMethod'],
                'payment_name' => $request->payment['paymentName'],
                'payment_fee' => $request->payment['totalFee'],
                'deposit_amount' => $paymentAmount,
                'paymentUrl' => $response['paymentUrl'],
                'status' => 0,
                'ref' => $response['reference'],
                'trx_id' => $merchantOrderId,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Deposite Berhasil, Silahkan Melakukan Pembayaran',
                'data' => $response
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
