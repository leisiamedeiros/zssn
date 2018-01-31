<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';

    public function scopeCountPoints($query, $id)
    {
        return $query->select('points')->whereId($id)->first()->points;
    }
}
