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

    public function item()
    {
        return $this->hasOne('App\Item', 'id', 'item_id');
    }

    public function changeInventory($survivor)
    {
        $this->survivor_id = $survivor;
        $this->save();
    }

    // public function scopeGetInventory($query, $id, $item_id, $qtty)
    // {
    //     return $query->where([
    //         ['survivor_id', '=', $id],
    //         ['item_id', '=', $item_id],
    //         ['qtd', '=', $qtty],
    //       ])->first();
    // }

    public function scopeVerifyItem($query, $id, $item_id, $qtty)
    {
        return $query->where([
            ['survivor_id', '=', $id],
            ['item_id', '=', $item_id],
            ['qtd', '>=', $qtty],
          ])->count();
    }

}
