<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Survivor;
use App\Infected;
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

    public function updateLocation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|string',
            'long' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
          $updated = Survivor::findOrFail($id)->update([
            'lat' => $request->lat,
            'long' => $request->long
          ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ops.. survivor not found'], 404);
        }
        return response()->json(['message' => $updated], 200);
    }

    public function flagSurvivorInfected(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'survivor_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $reporter = Survivor::find($id);
        $survivor = Survivor::find($request->survivor_id);

        if ( empty($reporter) ) {
          return response()->json(['message' => 'Ops... reporter not found'], 404);
        }
        if ( empty($survivor) ) {
          return response()->json(['message' => 'Ops... survivor not found'], 404);
        }
        if ($survivor->id == $reporter->id) {
          return response()->json(['message' => 'Ops... you cant report yousef'], 403);
        }

        $qtty = Infected::qttyReported($survivor->id);
        if ( $qtty <= 2 ) {
          try {
              $reported = Infected::create([
                'survivor_id' => $survivor->id,
                'reporter_id' => $reporter->id
              ]);

              if ( $qtty == 2) {
                $reported->isInfected();
              }
          } catch (\Exception $e) {
              return response()->json(['message' => 'You already reported this infection'], 403);
          }
        } else {
            return response()->json(['message' => 'Survivor already is kinda dead'], 200);
        }
        return response()->json(['message' => $reported], 200);
    }

}
