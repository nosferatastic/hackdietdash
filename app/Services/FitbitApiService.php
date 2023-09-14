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



    /*
    * Retrieve and store weight data from Fitbit API for a given date, providing in string "Y-m-d"
    */
    public function fitbit_get_weight($date) {
        $fitbit_auth = \App\Models\FitbitAuth::where('user_id','=',Auth::user()->id)->first();
        if(!isset($fitbit_auth)) {
            \App::abort(401);
        }
        
        //Retrieve weight data for given date via API call
        $api_response = json_decode($this->getWeightData($date));

        //If there's an error, just return that
        if(isset($api_response->error)) {
            return $api_response;
        }

        //Handle storage of weight data. We go by the first weight data returned to the API. 
        //And only store if we do not have existing info.
        //Create data service
        $data_service = new \App\Services\FitbitDataService();

        if(!$data_service->userHasExistingData($date) && isset($api_response->response->weight[0])) {
            //Store data from API response
            $weight_to_store = $api_response->response->weight[0];
            if($data_service->storeWeightData($weight_to_store)) {
                return "Successfully stored.";
            } else {
                return "Could not store.";
            }
        } else {
            return "Already stored.";
        }

    }

    /*
    * Retrieve weight range for Fitbit, using two dates provided in string "Y-m-d". 
    * This function needs to be broken into multiple functions and merged with the above one (by adding default null value for end)
    */
    public function fitbit_get_weight_range($start, $end = null) {
        //Check for existing fitbit auth object
        $fitbit_auth = \App\Models\FitbitAuth::where('user_id','=',Auth::user()->id)->first();
        if(!isset($fitbit_auth)) {
            \App::abort(401);
        }
        $data_service = new \App\Services\FitbitDataService();
        $date_ranges = $data_service->createBatchDateRanges($start, $end);
        foreach($date_ranges as $range) {
            ['start' => $start, 'end' => $end] = $range;
            $api_response = json_decode($this->getWeightDataRange($start, $end));
            //If there's an error, just return that
            if(isset($api_response->error)) {
                return $api_response;
            }
            $weightsArray = $api_response->response->weight;
            $current_date = new \Carbon\Carbon($start);
            //We add day here in order to be adding to current date also
            $end_date = new \Carbon\Carbon($end); $end_date = $end_date->addDays(1);
                do {
                    //Retrieve weight data for this iteration
                    $weightData = array_shift($weightsArray);
                    if(!$weightData) {
                        break;
                    }
                    //Different scenarios depending on relation between current checking date and weight data date
                    if($current_date->format('Y-m-d') < $weightData->date ) {
                        //If current iteration date is BEFORE this one, set current iteration date TO this one, and then go on to check if we can add
                        $current_date = new \Carbon\Carbon($weightData->date);
                    } else if($weightData->date < $current_date->format('Y-m-d')) {
                        //If current iteration date is AFTER this one OR we already have data stored, we're not even gonna try to add it as we've already considered that date
                        continue;
                    } else {
                        //Move currentDate to next date
                        $current_date = $current_date->addDays(1);
                    }
                    //We only go on to add if we don't have existing data for that date
                    if(!$data_service->userHasExistingData($weightData->date)) { 
                        //If we have got this far and don't have any existing data, we can store this!
                        $data_service->storeWeightData($weightData);
                    }
                } while ($current_date->notEqualTo($end_date));
        }
        return "Successfully stored.";

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