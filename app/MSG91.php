<?php
namespace app;

/**
* class MSG91 to send SMS on Mobile Numbers.

* @author Shashank Tiwari
*/
class MSG91 {

    function __construct() {

    }

    private $API_KEY = config('app.msg91_key');
    private $SENDER_ID = config('app.msg91_sender_id');

    public function sendSMS($OTP, $mobileNumber){
        $isError = 0;
        $errorMessage = true;

        //Your message to send, Adding URL encoding.
        $message = urlencode("Welcome to Plusity , Your Verification Code is : $OTP");
     

        //Preparing post parameters
        $postData = array(
            'authkey' => $this->API_KEY,
            'mobiles' => $mobileNumber,
            'message' => $message,
            'sender' => $this->SENDER_ID,
            'route' => '4',
            'response' => 'json'
        );
     
        $url = "https://control.msg91.com/sendhttp.php";
     
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
        ));
     
     
        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
     
     
        //get response
        $output = curl_exec($ch);
     
        //Print error if any
        if (curl_errno($ch)) {
            $isError = true;
            $errorMessage = curl_error($ch);
        }
        curl_close($ch);
        if($isError){
            return array('error' => 1 , 'message' => $errorMessage);
        }else{
            return array('error' => 0 );
        }
    }


}
?>