<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    public $timestamps = false;

    protected $table = 'inventory';

    protected $fillable = ['survivor_id', 'item_id', 'qtd'];

    public function survivor()
    {
        return $this->belongsTo('App\Survivor');
    }

    public function items()
    {
        return $this->hasMany('App\Item');
    }

}
