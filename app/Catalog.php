<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Catalog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'CatalogID', 'CatalogName', 'CatalogDescription'
    ];

    protected $primaryKey = 'CatalogID';              

    public $timestamps = false; //Cancel the default using in 'created_at' and 'updated_at' fields. 

    public $incrementing = false; //Cancel the default id incrementing when new record insert to db.


}
