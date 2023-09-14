<?php

namespace App\Http\Controllers;
use \App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Carbon\Carbon;

class FitbitDataController extends Controller
{
    /*
    *   Given a request with weight data, store the data
    */
    public function storeWeightData(Request $request) : string {
        if(!isset($request->datetime)) {
            \App::abort(404);
        }
        [$date, $time] = explode(" ", $request->datetime);
        //Delete existing
        \App\Models\WeightData
                        ::where(\DB::raw('DATE(datetime)'),'=',$date)
                        ->where('user_id','=',Auth::user()->id)
                        ->delete();
        if($request->weightlbs == "") {
            return true;
        }
        $weight_data = (Object) [
            'weight' => $request->weightlbs, 
            'date' => $date, 
            'time' => $time
        ];
        $data_service = new \App\Services\FitbitDataService();
        $new_data = $data_service->storeWeightData($weight_data);
        return json_encode($new_data);
    }
    
}