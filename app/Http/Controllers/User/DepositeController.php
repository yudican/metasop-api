<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\UserDeposite;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Duitku\Pop;
use Exception;

class DepositeController extends PaymentController
{
    // get payment method 
    public function getPaymentMethod(Request $request)
    {
        // guzzle http client post request
        $client = new Client();
        $orderId = date('Y-m-d H:i:s');
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
                'status' => 'Error',
                'message' => 'Metode Pembayaran Tidak Tersedia',
                'error' => $th->getMessage(),
                "signature" => env('DUITKU_MERCHANT_CODE') . $request->deposit_amount . $orderId . env('DUITKU_API_KEY')
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
        $paymentAmount      = intval($request->deposit_amount); // Amount
        $email              = $user->email; // your customer email
        $productDetails     = "Deposit";
        $merchantOrderId    = '#' . time(); // from merchant, unique   
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
                'message' => 'Deposit gagal',
                'message-dev' => $e->getMessage(),
                'data' => $params
            ], 400);
        }
    }

    // list deposite
    public function getListDeposit(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $role = auth()->user()->role;

        $deposit =  UserDeposite::query();
        if ($search) {
            $deposit->where(function ($query) use ($search) {
                $query->where('payment_code', 'like', "%$search%");
                $query->orWhere('deposit_amount', 'like', "%$search%");
                $query->orWhere('payment_name', 'like', "%$search%");
                $query->orWhere('trx_id', 'like', "%$search%");
                $query->orWhere('ref', 'like', "%$search%");
            });
        }

        if ($status) {
            $deposit->where('status', $status);
        }


        if (in_array($role->role_type, ['member', 'reseller'])) {
            $deposit->where('user_id', auth()->user()->id);
        }


        $deposits = $deposit->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $deposits,
            'message' => 'List deposit'
        ]);
    }

    public function detailTransaction($ref)
    {
        $user = UserDeposite::where('ref', $ref)->first();

        return response()->json([
            'data' => $user,
        ]);
    }

    public function updateTransactionStatus(Request $request, $ref)
    {
        $transaction = UserDeposite::where('ref', $ref)->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
                'data' => null,
            ], 404);
        }

        $data =  [
            'status' => $request->status
        ];

        if ($transaction->status == 0) {
            $transaction->update($data);
        }

        return response()->json([
            'data' => $transaction,
        ]);
    }
}
