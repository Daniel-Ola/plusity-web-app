<?php

namespace App\Http\Controllers\Api\SMS;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Twilio\Jwt\ClientToken;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class SMSController extends Controller
{
    //
    protected $code, $smsVerifcation;
    public $successStatus = 200;
    public $errorStatus = 401;

    function __construct()
    {
        $this->smsVerifcation = new \App\SmsVerification();
    }

    public function store(Request $request)
    {
        $code = rand(1000, 9999); 
        $request['user_id'] = 1;

        $request['code'] = $code; 

        $this->smsVerifcation->store($request); 

        return $this->sendSms($request); 
    }

    /**
     * 
     * Send SMS
     * 
     */
    public function sendSms($request)
    {
        $accountSid = config('app.twilio')['TWILIO_ACCOUNT_SID'];
        $authToken = config('app.twilio')['TWILIO_AUTH_TOKEN'];

        try
        {
            $client = new Client(['auth' => [$accountSid, $authToken]]);

            $result = $client->post('https://api.twilio.com/2010-04-01/Accounts/'.$accountSid.'/Messages.json',
            ['form_params' => [
                'Body' => 'Your Pluity Verification code is: '. $request->code, //set message body
                'To' => '2348136368738', // $request->contact_number,
                'From' => '+15709722459' //we get this number from twilio
                ]]);

            return response()->json(['success' => true, 'message' => 'SMS Successfully sent.', 'data' => $result], $this->successStatus);
        }
        catch (\Exception $e)
        {
            return response()->json(['success' => false, 'error' => $e->getMessage()], $this->errorStatus);
        }
    }

    public function verifyContact(Request $request)
    {
        $smsVerifcation = $this->smsVerifcation::where('user_id','=',$request->user_id)->latest()->first();

        if($request->code == $smsVerifcation->code)
        {
            $request["status"] = 'verified';

            return $smsVerifcation->updateModel($request);

            return response()->json(['success' => true, 'message' => 'Phonenumber successfully verified.', 'data' => $smsVerifcation], $this->successStatus);
        }
        else
        {
            return response()->json(['success' => false, 'error' => 'Unabe to complete verification'], $this->errorStatus);
        }
    }
}
