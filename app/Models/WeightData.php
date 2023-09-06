<?php

namespace App\Models;

use \App\Models\User;

use Illuminate\Database\Eloquent\Model;

class WeightData extends Model
{
    protected $database = 'hackd';
    protected $table = 'weight_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'datetime',
        'weightlbs'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'datetime'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'datetime' => 'datetime',
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