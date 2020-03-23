<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Shoppingcarts extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *      */
    protected $fillable = [
        'CartId', 'ProductID', 'Quantity'
    ];

    //protected $primaryKey = ['CartId', 'ProductID'];
    protected $primaryKey = 'CartId';

    public $timestamps = false; //Cancel the default using in 'created_at' and 'updated_at' fields. 

    public $incrementing = false; //Cancel the default id incrementing when new record insert to db.
}
