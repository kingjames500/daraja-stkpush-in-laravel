<?php

namespace App\Http\Controllers;

use App\Models\mpesaStk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    //securing my endpoint using this variables
    private $urltoken = 'TWWK2Umhbnsdhbhjhdlak!64788';
    public $whiteListIp = [
        '196.201.214.200',
        '196.201.214.206',
        '196.201.213.114',
        '196.201.214.207',
        '196.201.214.208',
        '196. 201.213.44',
        '196.201.212.127',
        '196.201.212.138',
        '196.201.212.129',
        '196.201.212.136',
        '196.201.212.74',
        '196.201.212.69'
    ];
    //token Generation
    public function generateToken()
    {
        $consumerKey = env('MPESA_CONSUMER_KEY');
        $consumerSecret = env('MPESA_CONSUMER_SECRET');
        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        // Request headers
        $response = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->get($url);
        //checking if the response will return our accesskey.
        return $response->successful() ? $response['access_token'] : null;
    }

    public function initiateStkSimulation(Request $request)
    {

        $accessToken = $this->generateToken();
        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        //variables to be used in the request
        $Passkey = env('MPESA_PASSKEY');
        $BusinessShortCode = env('MPESA_BUSINESS_SHORTCODE');
        $Timestamp = Carbon::now()->format('YmdHis');
        //concantinate the below values to create the password
        $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);
        $PartyA = $request->input('number'); //phone number
        $PartyB = '174379'; //shortcode
        $Amount = $request->input('amount');
        $PhoneNumber=$request->input('number'); //phone number
        $AccountReference = 'daraja api';
        $TransactionDesc = 'Payment of goods';
        $TransactionType = env('MPESA_TRANSACTION_TYPE');
        $callbackURL = env('APP_URL') .'test/callback?urltoken='.$this->urltoken;

        try {
            $response = Http::withToken($accessToken)->post($url, [
                'BusinessShortCode' => $BusinessShortCode,
                'Password' => $Password,
                'Timestamp' => $Timestamp,
                'TransactionType' => $TransactionType,
                'Amount' => $Amount,
                'PartyA' => $PartyA,
                'PartyB' => $PartyB,
                'PhoneNumber' => $PhoneNumber,
                'CallBackURL' => $callbackURL,
                'AccountReference' => $AccountReference,
                'TransactionDesc' => $TransactionDesc
            ]);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
        Log::info($response->json());

        $res = json_decode($response);
        $ResponseCode = $res->ResponseCode;

        try {
            if ($ResponseCode == 0) {
                $MerchantRequestID = $res->MerchantRequestID;
                $CheckoutRequestID = $res->CheckoutRequestID;
                $CustomerMessage = $res->CustomerMessage;
                //let save this initail request on a database
                $payment = new mpesaStk;
                $payment->phone = $PhoneNumber;
                $payment->amount = $Amount;
                $payment->MerchantRequestID = $MerchantRequestID;
                $payment->CheckoutRequestID = $CheckoutRequestID;
                $payment->reference = $AccountReference;
                $payment->description = $TransactionDesc;
                $payment->status = 'requested'; //requested, completed, failed
                $payment->save();
                return view('welcome', ['response' => $CustomerMessage]);
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

    }
    //callback function
    public function stkPushCallback(Request $request){
        //comparing the tokens
        $compare = strcmp($request->urltoken, $this->urltoken);
        if ($compare !== 0) {
            return response()->json([
                'message' => 'Invalid token'
            ], 401);
        }
        //checking the ip address
        if (!in_array($request->ip(), $this->whiteListIp)) {
            return response()->json([
                'message' => 'Invalid IP'
            ], 401);
        }
        log::info("callback url hit");

        $data = file_get_contents('php://input');
        Storage::disk('local')->put('stk.json', $data);

        //updating the status of the transaction
        $response = json_decode($data);

        Log::info(json_encode($response));
        $ResultCode = $response->Body->stkCallback->ResultCode;

        if ($ResultCode == 0) {
            $MerchantRequestID = $response->Body->stkCallback->MerchantRequestID;
            $CheckoutRequestID = $response->Body->stkCallback->CheckoutRequestID;
            $ResultDesc = $response->Body->stkCallback->ResultDesc;
            $Amount = $response->Body->stkCallback->CallbackMetadata->Item[0]->Value;
            $MpesaReceiptNumber = $response->Body->stkCallback->CallbackMetadata->Item[1]->Value;
            //$balance = $response->Body->stkCallback->CallbackMetadata->Item[2]->Value;
            $TransactionDate = $response->Body->stkCallback->CallbackMetadata->Item[3]->Value;
            $PhoneNumber = $response->Body->stkCallback->CallbackMetadata->Item[4]->Value;

            $payment = mpesaStk::where('CheckoutRequestID', $CheckoutRequestID)->firstOrFail();
            $payment->status = 'completed';
            $payment->MpesaReceiptNumber = $MpesaReceiptNumber;
            $payment->TransactionDate = $TransactionDate;
            $payment->ResultsDesc = $ResultDesc;
            $payment->save();
        } else {
            $CheckoutRequestID = $response->Body->stkCallback->CheckoutRequestID;
            $ResultDesc = $response->Body->stkCallback->ResultDesc;
            $payment = mpesaStk::where('CheckoutRequestID', $CheckoutRequestID)->firstOrFail();
            $payment->ResultsDesc = $ResultDesc;
            $payment->status = 'failed';
            $payment->save();
        }

    }

    //function for reversing a transaction
    public function reverseTransaction()
    {
        $accessToken = $this->generateToken();
        $initiatorPassword = 'Safaricom999!*!';
        $path = Storage::disk('local')->get('SandboxCertificate.cer');
        $pk = openssl_pkey_get_public($path);
        openssl_public_encrypt(
            $initiatorPassword,
            $encrypted,
            $pk,
            OPENSSL_PKCS1_PADDING
        );
        $SecurityCredential = base64_encode($encrypted);
        $CommandID = "TransactionReversal";
        $Initiator = 'testapi';
        $TransactionID = "SDU0QLNPDW";
        $TransactionAmount = '1';
        $ReceiverParty = '600996';
        $Remarks = 'test';
        $ReceiverIdentifierType = '11';
        $ResultURL = env('APP_URL'). '/test/reversetransaction';
        $QueueTimeOutURL = env('APP_URL').'/test/timeout';

        $url = 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';

        $response = Http::withToken($accessToken)->post($url, [
            'Initiator' => $Initiator,
            'SecurityCredential' => $SecurityCredential,
            'CommandID' => $CommandID,
            'TransactionID' => $TransactionID,
            'Amount' => $TransactionAmount,
            'ReceiverParty' => $ReceiverParty,
            'ReceiverIdentifierType' => $ReceiverIdentifierType,
            'ResultURL' => $ResultURL,
            'QueueTimeOutURL' => $QueueTimeOutURL,
            'Remarks' => $Remarks
        ]);
        return ($response)->json();

    }

    //Reversal result url
    public function reversalResult()
    {
        $data = file_get_contents('php://input');
        Storage::disk('local')->put('resulturl.json', $data);
    }

    // function for reversal timeout
    public function ReversalTimeout()
    {
        $data = file_get_contents('php://input');
        Storage::disk('local')->put('reversaltimeout.json', $data);
    }

    //checking transaction status
    public function stkQuery(Request $request)
    {
        $accessToken = $this->generateToken();
        $BusinessShortCode = 174379;
        $PassKey =env('MPESA_PASSKEY');
        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
        $Timestamp = Carbon::now()->format('YmdHis');
        $Password = base64_encode($BusinessShortCode . $PassKey . $Timestamp);
        $CheckoutRequestID = $request->input('CheckoutRequestID');

        $response = Http::withToken($accessToken)->post($url, [

            'BusinessShortCode' => $BusinessShortCode,
            'Timestamp' => $Timestamp,
            'Password' => $Password,
            'CheckoutRequestID' => $CheckoutRequestID
        ]);
        $responseData = $response->json();

        return view('stkquery', ['response' => $responseData]);
    }

    //fetching all transaction on the database
    public function fetchTransactions(Request $request){
        $status = $request->input('status');
        if($status){
            $transactions = mpesaStk::where('status', $status)->get();
        } 
        else{
            $transactions = mpesaStk::all()
            ->sortByDesc('created_at')
            ->take(4);
        }  
        return view('mpesa', ['transactions' => $transactions]); 
    }

    //qr code
    public function qrCode(){
        $accessToken = $this->generateToken();
        $url = 'https://sandbox.safaricom.co.ke/mpesa/qrcode/v1/generate';
        $MerchantName = 'Kingjames';
        $RefNo = 'Invoice Test';
        $Amount = '1';
        $TrxCode = 'BG';
        $CPI = env('MPESA_BUSINESS_SHORTCODE');
        $Size = '300x300';

        $response = Http::withToken($accessToken)->post($url, [
            'MerchantName' => $MerchantName,
            'MerchantCode' => $CPI,
            'Amount' => $Amount,
            'TrxCode' => $TrxCode,
            'RefNo' => $RefNo,
            'Size' => $Size
        ]);

        return $response->json();

    }
}
