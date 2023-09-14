<?php

namespace App\Http\Controllers\Data;
use \App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Carbon\Carbon;

class WeightDataController extends Controller
{

    /*
    * Array function for sorting by datetime.
    */
    function sortByDateTime($a, $b) {
        if ($a['datetime'] > $b['datetime']) {
            return 1;
        } elseif ($a['datetime'] < $b['datetime']) {
            return -1;
        }
        return 0;
    }

    //The below two functions should be moved to a WeightDataContainer model for cleaner code.
    /*
    * Moving average according to the hacker's diet (exponentially weighted average).
    */
    private function moving_average($weight_data, $range = 10) {
        foreach($weight_data as $key => $data) {
                $store[$key % $range] = $data['weightlbs'];
                $smoother = 0.9;
                if($key == 0) {
                    $weight_data[$key]['weightma'] = $weight_data[$key]['weightlbs'];
                } else {
                    $weight_data[$key]['weightma'] = number_format($weight_data[$key-1]['weightma'] + (1 - $smoother)*($weight_data[$key]['weightlbs'] - $weight_data[$key-1]['weightma']),2);
                }
        }
        return $weight_data;
    }

    /*
    * Interpolation of missing day data by smoothing between existing data points on intermediate days.
    */
    private function interpolate_missing_days($weight_data) {
        $iterate_data = $weight_data;
        foreach($iterate_data as $key => $data) {
            if($key +1 < count($iterate_data)) {
                $cur_date = new Carbon($data['datetime']);
                $cur_weight = $data['weightlbs'];
                $next_date = new Carbon($iterate_data[$key+1]['datetime']);
                $next_weight = $iterate_data[$key+1]['weightlbs'];
                $date_diff = $cur_date->diffInDays($next_date);
                $i = 1;
                while($i < $date_diff) {
                    $new_point = array();
                    $date_calc = $cur_date;
                    $date_calc = $date_calc->addDays($i);
                    $new_point['datetime'] = $date_calc->jsonSerialize();
                    $new_point['weightlbs'] = number_format($cur_weight + $i*(($next_weight - $cur_weight)/($date_diff)),1);
                    $new_point['interpolated'] = 1;
                    $weight_data[] = $new_point;
                    $i++;
                    $cur_date = new Carbon($data['datetime']);
                }
            }
        }
        usort($weight_data, array($this,'sortByDateTime'));
        return $weight_data;
    }

    /*
    * Display dashboard for weight data
    */
    public function show(Request $request) {
        $user = Auth::user();
        //Prepare weight data array
        $weight_data = $user->getWeightData('datetime asc')->toArray();
        $weight_data = $this->interpolate_missing_days($weight_data);
        $weight_data = $this->moving_average($weight_data);

        return Inertia::render('Data/WeightDashboard', [
            'weightData' => $weight_data
        ]);
    }

    /*
    * Display list of weight data to be managed
    */
    public function index(Request $request) {
        $user = Auth::user();
        $weight_data = $user->getWeightData('datetime asc')->keyBy('date')->toArray();

        $start_date = $user->getTrackingSettings()->tracking_start_date;
        $date_range = \Carbon\CarbonPeriod::since($start_date)->until(date('Y-m-d'));
        $dates = array();
        foreach ($date_range as $key => $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return Inertia::render('Data/WeightDataIndex', [
            'weightData' => $weight_data,
            'dates' => $dates
        ]);
    }

    /*
    * Ajax call for retrieving this user's weight data
    */
    public function get_weight_data(Request $request) {
        $user = Auth::user();

        //Let's put the code for retrieving new datapoints here, for the time being, as it's loaded whenever loading the dashboard graph.
        //Retrieve most recent datapoint for this user
        $latest_data = \App\Models\WeightData::select(\DB::raw('max(datetime) as most_recent'))->where('user_id','=',$user->id)->first();
        //Retrieve the date of this, and CURRENT date, in Y-m-d for easier API work,
        $latest_date = new \Carbon\Carbon($latest_data->most_recent); $latest_date = $latest_date->toDateString();
        $today = new \Carbon\Carbon(); $today = $today->toDateString();
        //If there is a gap, we should attempt to retrieve data from the Fitbit API for the date range.
        if($latest_date < $today) {
            $apiService = new \App\Services\FitbitApiService();
            $apiService->fitbit_get_weight_range($latest_date, $today);
        }
        //End this bit

        $weight_data = $user->getWeightData('datetime asc')->toArray();
        $weight_data = $this->interpolate_missing_days($weight_data);
        $weight_data = $this->moving_average($weight_data);
        
        return json_encode($weight_data);
    }

}