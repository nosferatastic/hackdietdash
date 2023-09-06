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
    public function store_weight_data(Request $request) {
        if(!$request->datetime || !$request->weightlbs) {
            return redirect()->back();
        }

        
    }

    function sortByDateTime($a, $b) {
        if ($a['datetime'] > $b['datetime']) {
            return 1;
        } elseif ($a['datetime'] < $b['datetime']) {
            return -1;
        }
        return 0;
    }

    private function moving_average($weightData, $range = 10) {
        foreach($weightData as $key => $data) {
                $store[$key % $range] = $data['weightlbs'];
                $smoother = 0.9;
                if($key == 0) {
                    $weightData[$key]['weightma'] = $weightData[$key]['weightlbs'];
                } else {
                    $weightData[$key]['weightma'] = number_format($weightData[$key-1]['weightma'] + (1 - $smoother)*($weightData[$key]['weightlbs'] - $weightData[$key-1]['weightma']),2);
                }
        }
        return $weightData;
    }

    private function interpolate_missing_days($weightData) {
        $iterateData = $weightData;
        foreach($iterateData as $key => $data) {
            if($key +1 < count($iterateData)) {
                $curDate = new Carbon($data['datetime']);
                $curWeight = $data['weightlbs'];
                $nextDate = new Carbon($iterateData[$key+1]['datetime']);
                $nextWeight = $iterateData[$key+1]['weightlbs'];
                $dateDiff = $curDate->diffInDays($nextDate);
                $i = 1;
                while($i < $dateDiff) {
                    $newPoint = array();
                    $dateCalc = $curDate;
                    $dateCalc = $dateCalc->addDays($i);
                    $newPoint['datetime'] = $dateCalc->jsonSerialize();
                    $newPoint['weightlbs'] = number_format($curWeight + $i*(($nextWeight - $curWeight)/($dateDiff)),1);
                    $newPoint['interpolated'] = 1;
                    $weightData[] = $newPoint;
                    $i++;
                    $curDate = new Carbon($data['datetime']);
                }
            }
        }
        usort($weightData, array($this,'sortByDateTime'));
        return $weightData;
    }

    /*
    * Display list of weight data
    */
    public function show(Request $request) {
        $user = Auth::user();
        $weightData = $user->weight_data()->orderBy('datetime','asc')->get()->toArray();
        $weightData = $this->interpolate_missing_days($weightData);
        $weightData = $this->moving_average($weightData);



        return Inertia::render('Data/WeightDashboard', [
            'weightData' => $weightData
        ]);
    }

    public function get_weight_data(Request $request) {
        $user = Auth::user();
        $weightData = $user->weight_data()->orderBy('datetime','asc')->get()->toArray();
        $weightData = $this->interpolate_missing_days($weightData);
        $weightData = $this->moving_average($weightData);
        
        return json_encode($weightData);
    }

    public function fitbit_auth() {
        $fitbitService = new \App\Services\FitbitAuthService(Auth::user());
        return $fitbitService->requestAuthorisation();
    }

    public function fitbit_webhook_capture(Request $request) {
        $code = $request->code;
        $fitbitService = new \App\Services\FitbitAuthService(Auth::user());
        return $fitbitService->captureRedirect($code);
    }

    public function fitbit_get_weight($date) {
        $fitbitAuth = \App\Models\FitbitAuth::where('user_id','=',Auth::user()->id)->first();
        if(!isset($fitbitAuth)) {
            \App::abort(401);
        }
        $url = "https://api.fitbit.com/1/user/".$fitbitAuth->fitbit_user_id."/body/log/weight/date/".$date.".json";
        $authorization = "Authorization: Bearer ".$fitbitAuth->access_token;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization, 'accept-language: en_US' ));
        $server_response = curl_exec($ch);
        curl_close($ch);
        dd(json_decode($server_response)->weight);


    }
}