<?php

namespace App\Http\Controllers;
use App\Models\C2B;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class customer2Business extends Controller
{
    //token generation
    public function token(){
        $consumerKey = env('MPESA_CONSUMER_KEY');
        $consumerSecret = env('MPESA_CONSUMER_SECRET');
        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $response = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->get($url);

        return $response->successful() ? $response['access_token'] : null;
    }
    //register url
    public function registerUrl()
    {
        $accessToken = $this->token();
        $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';
        $ShortCode = 600997;
        $ResponseType = 'Completed'; 
        $ConfirmationURL = env('APP_URL').'b2c/confirmation';
        $ValidationURL = env('APP_URL').'b2c/validation';

        $response = Http::withToken($accessToken)->post($url, [
            'ShortCode' => $ShortCode,
            'ResponseType' => $ResponseType,
            'ConfirmationURL' => $ConfirmationURL,
            'ValidationURL' => $ValidationURL
        ]);

        return $response;
    }
    //validation
    public function validation(){
        $data = file_get_contents('php://input');
        Storage::disk('local')->put('validation.txt', $data);

        //validation logic
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);

        /*return response()->json([
            'ResultCode' => 'C2B00011',
            'ResultDesc' => 'Rejected'
        ]);*/

    }
    //confirmation
    public function confirmation(){
        $data = file_get_contents('php://input');
        Storage::disk('local')->put('confirmation.txt', $data);

        //save to database
    }
}
