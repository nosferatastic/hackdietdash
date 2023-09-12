<?php

namespace App\Services;
use Illuminate\Support\Facades\Auth;

class FitbitApiService {

    protected $user;
    protected FitbitAuthService $auth_service;

    function __construct() {
        $this->user = Auth::user();
        //In constructing the auth service we will check for existence of auth in the database, check if it is past expiry, and refresh the token if it is.
        $this->auth_service = new FitbitAuthService();
    }

    public function getAuthService() {
        return $this->auth_service;
    }

    public function getWeightData($date) {
        if(null == ($this->getAuthService()?->getFitbitAuth())) {
            return json_encode(['error' => "No valid auth"]);
        }
        $weightUrl = $this->getWeightDateApiUrl($date);
        $authorization = "Authorization: Bearer ".$this->getAuthService()->getFitbitAuth()->access_token;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$weightUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization, 'accept-language: en_US' ));
        $server_response = json_decode(curl_exec($ch));
        curl_close($ch);
        return json_encode(['success' => 'Successfully retrieved', 'response' => $server_response]);
    }

    public function getWeightDataRange($start, $end) {
        if(null == ($this->getAuthService()?->getFitbitAuth())) {
            return json_encode(['error' => "No valid auth"]);
        }
        $weightUrl = $this->getWeightDateRangeApiUrl($start, $end);
        $authorization = "Authorization: Bearer ".$this->getAuthService()->getFitbitAuth()->access_token;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$weightUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization, 'accept-language: en_US' ));
        $server_response = json_decode(curl_exec($ch));
        curl_close($ch);
        return json_encode(['success' => 'Successfully retrieved', 'response' => $server_response]);
    }

    // Assorted functions for returning URLs

    /*
    *  Returns the API call URL for the GET weight by date call for the logged-in user and specified date (format YYYY-mm-dd).
    */
    private function getWeightDateApiUrl($date) : string {
        $fitbitAuth = $this->getAuthService()?->getFitbitAuth();
        if(!$fitbitAuth) {
            return '';
        }
        return "https://api.fitbit.com/1/user/".$fitbitAuth->fitbit_user_id."/body/log/weight/date/".$date.".json";
    }

    /*
    *  Returns the API call URL for the GET weight by date RANGE call for the logged-in user and specified dates (format YYYY-mm-dd).
    */
    private function getWeightDateRangeApiUrl($start, $end) : string {
        $fitbitAuth = $this->getAuthService()?->getFitbitAuth();
        if(!$fitbitAuth) {
            return '';
        }
        return "https://api.fitbit.com/1/user/".$fitbitAuth->fitbit_user_id."/body/log/weight/date/".$start."/".$end.".json";
    }

}

?>