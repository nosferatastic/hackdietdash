<?php

namespace App\Models;

use \App\Models\User;

use Illuminate\Database\Eloquent\Model;

class FitbitAuth extends Model
{
    protected $database = 'hackd';
    protected $table = 'fitbit_auth';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'code_verifier'
    ];

    /*
    * Accessors/Mutators
    */

    public function getUser() : User {
        return $this->user;
    }

    public function getCodeVerifier() : null|string {
        return $this->code_verifier;
    }

    public function getRefreshToken() : null|string {
        return $this->refresh_token;
    }

    public function setTokens($server_response) : FitbitAuth {
        if(!isset($server_response->access_token)) {
            return $this;
        }
        $this->access_token = $server_response->access_token;
        $this->refresh_token = $server_response->refresh_token;
        $this->expires_at = new \DateTime('+'.$server_response->expires_in." seconds");
        $this->save();
        return $this;
    }

    /*
    * RELATIONS
    */

    public function user() {
        return $this->belongsTo('App/Models/User');
    }
}