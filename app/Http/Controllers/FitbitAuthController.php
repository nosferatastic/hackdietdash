<?php

namespace App\Http\Controllers;
use \App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Carbon\Carbon;

class FitbitAuthController extends Controller
{

    /*
    * Begins the process of requesting authorisation for Fitbit API. Service redirects away from our site to auth page hosted by FB.
    */
    public function fitbit_auth() {
        $fitbit_service = new \App\Services\FitbitAuthService();
        //This will be a redirect to fitbit authorisation page 
        //(or directly back to us if user logged in and has authorised this application already)
        return $fitbit_service->requestAuthorisation();
    }

    /*
    * After the user approves Fitbit authorisation, the user is brought back here, where we capture the webhook and perform code exchange.
    */
    public function fitbit_webhook_capture(Request $request) {
        $fitbit_service = new \App\Services\FitbitAuthService();
        if($request->error_description) {
            //The connection did not work (usually user declined). So we delete the fitbit_auth object to restart the process.
            $fitbit_auth = $fitbit_service->deleteFitbitAuth();
        } else {
            //Success! We have the code. So we exchange it. What if it's not valid? TODO
            $code = $request->code;
            $fitbit_auth = $fitbit_service->codeExchange($code)->getFitbitAuth();
        }

        //If the auth object and access token exist we can safely assume everything went well
        if($fitbit_auth?->access_token) {
            return Redirect::route('profile.edit')->with('success',"Fitbit Account linked successfully.");
        } else {
            //If it doesn't, we have a problem. Return to original pre-auth request state and go back to the source
            return Redirect::route('profile.edit')->with('error',"There was a problem linking your Fitbit Account.");
        }

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
        $api_service = new \App\Services\FitbitApiService();
        $api_response = json_decode($api_service->getWeightData($date));

        //If there's an error, just return that
        if(isset($api_response->error)) {
            return $api_response;
        }

        //Handle storage of weight data. We go by the first weight data returned to the API. 
        //And only store if we do not have existing info.
        $existing = \App\Models\WeightData::where(\DB::raw('DATE(datetime)'),'=',$date)->first();

        if(!isset($existing) && isset($api_response->response->weight[0])) {
            $weight_to_store = $api_response->response->weight[0];
            $new_data = new \App\Models\WeightData([
                'user_id' => Auth::user()->id,
                'weightlbs' => $weight_to_store->weight,
                'datetime' => $weight_to_store->date." ".$weight_to_store->time
            ]);
            $new_data->save();
            return "Successfully stored.";
        } else {
            return "Already stored.";
        }

    }

    /*
    * Retrieve weight range for Fitbit, using two dates provided in string "Y-m-d". 
    * This will be moved to its own controller down the line.
    * This function needs to be broken into multiple functions and merged with the above one (by adding default null value for end)
    */
    public function fitbit_get_weight_range($start, $end) {
        //Check for existing fitbit auth object
        $fitbit_auth = \App\Models\FitbitAuth::where('user_id','=',Auth::user()->id)->first();
        if(!isset($fitbit_auth)) {
            \App::abort(401);
        }
        //Initialise date objects
        $current_date = new \Carbon\Carbon($start);
        $end_date = new \Carbon\Carbon($end);
        $date_ranges = array();

        //Limit is 31 days so we have to split it up if we're doing that and do multiple retrievals.
        if($current_date->diffInDays($end_date) > 30) {
            do {
                $startDate = new \Carbon\Carbon($current_date);
                //New end date is the smallest of END DATE or 30 days past current start date
                $current_date->addDays(30);
                $date_ranges[] = ['start' => $startDate->format('Y-m-d'), 'end' => min($current_date->format('Y-m-d'), $end_date->format('Y-m-d'))];
            } while ($current_date->lessThan($end_date));
        } else {
            $date_ranges[] = ['start' => $start, 'end' => $end];
        }
        
        $api_service = new \App\Services\FitbitApiService();
        foreach($date_ranges as $range) {
            ['start' => $start, 'end' => $end] = $range;
            $api_response = json_decode($api_service->getWeightDataRange($start, $end));
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
                    //Look for existing data in DB matching date for this data
                    $existing = \App\Models\WeightData::where(\DB::raw('DATE(datetime)'),'=',$weightData->date)->first();
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
                    if(!$existing) { 
                        //If we have got this far and don't have any existing data, we can store this!
                        $new_data = new \App\Models\WeightData([
                            'user_id' => Auth::user()->id,
                            'weightlbs' => $weightData->weight,
                            'datetime' => $weightData->date." ".$weightData->time
                        ]);
                        $new_data->save();
                    }
                } while ($current_date->notEqualTo($end_date));
        }
        return "Successfully stored.";

    }
}