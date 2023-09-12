<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected function weight_data() {
        return $this->hasMany('App\Models\WeightData');
    }

    protected function fitbit_auth() {
        return $this->hasOne('App\Models\FitbitAuth');
    }

    public function getFitbitAuth() {
        return $this->fitbit_auth;
    }

    public function withFitbitAuth() {
        $this->getFitbitAuth();
        return $this;
    }

    public function getWeightData($orderBy = '') {
        return $this->weight_data()->select('weight_data.*',\DB::raw('date(datetime) as date'))->orderByRaw($orderBy)->get();
    }

    public function getTrackingSettings() {
        return $this->weight_tracking_settings;
    }

    /*
    * Relations
    */

    protected function weight_tracking_settings() {
        return $this->hasOne('App\models\UserWeightTrackingSettings');
    }
}
