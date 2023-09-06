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

    public function getCodeVerifier() : string {
        return $this->code_verifier;
    }

    /*
    * RELATIONS
    */

    public function user() {
        return $this->belongsTo('App/Models/User');
    }
}