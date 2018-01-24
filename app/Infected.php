<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Infected extends Model
{
    protected $table = 'infected';

    protected $fillable = ['survivor_id', 'related'];

    public function isInfected()
    {
        $this->status = true;
        $this->save();
    }

    public function survivor()
    {
       return $this->belongsTo('App\Survivor');
    }
}
