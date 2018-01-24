<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Survivor extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'age', 'gender', 'lat', 'long'];

    public function updateLocation($lat, $long)
    {
        $this->lat = $lat;
        $this->long = $long;
        $this->save();
    }

    public function inventory()
    {
        return $this->hasOne('App\Inventory');
    }

    public function infected()
    {
        return $this->hasOne('App\Infected');
    }
}
