<?php

namespace App\Models;

use \App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserWeightTrackingSettings extends Model
{
    protected $database = 'hackd';
    protected $table = 'user_weight_tracking_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tracking_start_date',
        'weightunit',
        'user_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'tracking_start_date'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tracking_start_date' => 'datetime',
    ];

    /*
    * Accessors/Mutators
    */

    public function getUser() : User {
        return $this->user;
    }

    /*
    * RELATIONS
    */

    public function user() {
        return $this->belongsTo('App/Models/User');
    }
}