<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Infected extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'survivor_id';

    protected $table = 'infected';

    protected $fillable = ['survivor_id', 'reporter_id'];

    public function isInfected()
    {
        $this->status = true;
        $this->save();
    }

    public function scopeQttyReported($query, $id)
    {
        return $query->whereSurvivorId($id)->count();
    }

    public function scopeInfected($query, $id)
    {
        return $query->select('status')->where('survivor_id', $id)->groupBy('status');
    }

    public function survivor()
    {
       return $this->belongsTo('App\Survivor');
    }
}
