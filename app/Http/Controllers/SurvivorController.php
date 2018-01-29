<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Survivor;
use Validator;
use App\Rules\Items;
use App\Inventory;

class SurvivorController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string|max:10',
            'lat' => 'required|string',
            'long' => 'required|string',
            'items' => ['required', new Items]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
          $sur = Survivor::create($request->all());
        } catch (\Exception $e) {
          return response()->json(['message' => $e], 403);
        }

        $items = explode(",", $request->items);

        $itemsInventory = array();
        foreach ($items as $i) {
            list($id, $qtty) = explode(":", $i);

            try {
              $itemsInventory[] = Inventory::create([
                'survivor_id' => $sur->id,
                'item_id' => trim($id),
                'qtd' => trim($qtty)
              ]);

            } catch (\Exception $e) {
                return response()->json(['message' => $e], 404);
            }
        }
        return response()->json(['status' => 'OK', 'message' => $itemsInventory], 200);

    }


}
