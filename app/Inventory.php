<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';

    public function survivor()
    {
        return $this->belongsTo('App\Survivor');
    }

    public function items()
    {
        return $this->hasMany('App\Item');
    }
    
}
