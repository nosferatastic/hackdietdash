<?php

namespace App\Services;
use Illuminate\Support\Facades\Auth;

class FitbitDataService {

    protected $user;
    protected FitbitAuthService $auth_service;

    function __construct() 
    {
        $this->user = Auth::user();
        //In constructing the auth service we will check for existence of auth in the database, check if it is past expiry, and refresh the token if it is.
        $this->auth_service = new FitbitAuthService();
    }

    /*
    * Given a date (format Y-m-d), returns whether or not data already exists for this date/user
    */
    public function userHasExistingData(string $date) : boolean 
    {
        $existing = \App\Models\WeightData
                        ::where(\DB::raw('DATE(datetime)'),'=',$date)
                        ->where('user_id','=',$this->user->id)
                        ->first();
        return $existing != null;
    }

    /*
    * Given a Fitbit-returned weight/body log, store the data
    */
    public function storeWeightData($weight_data) : ?\App\Models\WeightData
    {
        $new_data = new \App\Models\WeightData([
            'user_id' => Auth::user()->id,
            'weightlbs' => $weight_data->weight,
            'datetime' => $weight_data->date." ".$weight_data->time
        ]);
        $new_data->save();
        return $new_data;
        //In some scenario we may wish to return false if it fails to store
    }

    /*
    *
    */
    public function createBatchDateRanges($start, $end) { 
        //Initialise date objects
        $current_date = new \Carbon\Carbon($start);
        //If the end date is not defined, we just use the start date so we're only getting for one day!
        $end_date = new \Carbon\Carbon($end ?? $start);
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
        return $date_ranges;
    }
}

?>