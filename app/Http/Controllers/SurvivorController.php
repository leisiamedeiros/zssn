<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Survivor;
use App\Infected;
use Validator;
use App\Rules\Items;
use App\Item;
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

    public function tradeItems(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'owner_name' => 'required|string',
            'items_wanted' => ['required', new Items],
            'items_paid' => ['required', new Items]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $interessed = Survivor::with(['inventory.item', 'infected'])
                     ->whereId($id)->first();

        $owner = Survivor::with(['inventory.item', 'infected'])
                     ->whereName($request->owner_name)->first();

        if ( empty($interessed) || empty($owner) ) {
            return response()->json(['message' => 'Ops... survivor not found'], 404);
        }
        if ($interessed->id === $owner->id) {
            return response()->json(['message' => 'Ops... you cant trade items with yourself'], 403);
        }

        if (is_null($owner->infected) && is_null($interessed->infected)) {

            $resultI = $this->verifyItemsInventory($request->items_paid, $interessed->id);
            $resultO = $this->verifyItemsInventory($request->items_wanted, $owner->id);

            if ($resultI == false || $resultO == false) {
              return response()->json(['message' => 'Oops... you dont have one or more items'], 404);
            }

            $cPinteressed = $this->countPoints($request->items_paid);
            $cPowner = $this->countPoints($request->items_wanted);

            if ( array_sum($cPinteressed) ===  array_sum($cPowner) ) {
              $this->replaceItems($request->items_paid, $interessed->id, $request->items_wanted, $owner->id);
              return response()->json(['message' => 'OK'], 200);

            } else {
              return response()->json([
                'message' => 'Oops... Both sides of the trade should offer the same amount of points.'
              ], 403);
            }

        } else if ( !is_null($owner->infected) && !is_null($interessed->infected) ) {
            if ( $owner->infected->status === true || $interessed->infected->status === true ) {
                return response()->json(['message' => 'Ops... kinda dead cant trade items'], 403);
            } else {

              $resultI = $this->verifyItemsInventory($request->items_paid, $interessed->id);
              $resultO = $this->verifyItemsInventory($request->items_wanted, $owner->id);

              if ($resultI == false || $resultO == false) {
                return response()->json(['message' => 'Oops... you dont have one or more items'], 404);
              }

              $cPinteressed = $this->countPoints($request->items_paid);
              $cPowner = $this->countPoints($request->items_wanted);

              if ( array_sum($cPinteressed) ===  array_sum($cPowner) ) {
                $this->replaceItems($request->items_paid, $interessed->id, $request->items_wanted, $owner->id);
                return response()->json(['message' => 'OK'], 200);

              } else {
                return response()->json([
                  'message' => 'Oops... Both sides of the trade should offer the same amount of points.'
                ], 403);
              }

            }

        } else if ( is_null($owner->infected) ) {
            if ( $interessed->infected->status === false) {

              $resultI = $this->verifyItemsInventory($request->items_paid, $interessed->id);
              $resultO = $this->verifyItemsInventory($request->items_wanted, $owner->id);

              if ($resultI == false || $resultO == false) {
                return response()->json(['message' => 'Oops... you dont have one or more items'], 404);
              }

              $cPinteressed = $this->countPoints($request->items_paid);
              $cPowner = $this->countPoints($request->items_wanted);

              if ( array_sum($cPinteressed) ===  array_sum($cPowner) ) {
                $this->replaceItems($request->items_paid, $interessed->id, $request->items_wanted, $owner->id);
                return response()->json(['message' => 'OK'], 200);

              } else {
                return response()->json([
                  'message' => 'Oops... Both sides of the trade should offer the same amount of points.'
                ], 403);
              }

            } else {
              return response()->json(['message' => 'Ops... kinda dead cant trade items'], 403);
            }

        } else {
            if ( $owner->infected->status === false) {

              $resultI = $this->verifyItemsInventory($request->items_paid, $interessed->id);
              $resultO = $this->verifyItemsInventory($request->items_wanted, $owner->id);

              if ($resultI == false || $resultO == false) {
                return response()->json(['message' => 'Oops... you dont have one or more items'], 404);
              }

              $cPinteressed = $this->countPoints($request->items_paid);
              $cPowner = $this->countPoints($request->items_wanted);

              if ( array_sum($cPinteressed) ===  array_sum($cPowner) ) {
                $this->replaceItems($request->items_paid, $interessed->id, $request->items_wanted, $owner->id);
                return response()->json(['message' => 'OK'], 200);

              } else {
                return response()->json([
                  'message' => 'Oops... Both sides of the trade should offer the same amount of points.'
                ], 403);
              }

            } else {
              return response()->json(['message' => 'Ops... kinda dead cant trade items'], 403);
            }
        }
        return response()->json(['message' => 'Ops... something is wrong'], 403);
    }

    public function verifyItemsInventory($items, $id)
    {
        $itemsS = explode(",", $items);
        foreach ($itemsS as $i) {
            list($IdItem, $Qtty) = explode(":", $i);
            $count = Inventory::verifyItem($id, $IdItem, $Qtty);
            if ($count == 0) {
              return false;
            }
        }
        return true;
    }

    public function countPoints($items)
    {
        $itemsS = explode(",", $items);
        foreach ($itemsS as $i) {
            list($Id, $Qtty) = explode(":", $i);
            $IPoint = Item::countPoints($Id);
            $points[] = $IPoint * $Qtty;
        }
        return $points;
    }

    public function replaceItems($items_paid, $interessed_id, $items_wanted, $owner_id)
    {
        $itemsPayment = explode(",", $items_paid);
        foreach ($itemsPayment as $i) {

            list($Id_Item, $Qtty_I) = explode(":", $i);
            $itemChangeIn = Inventory::getInventory($interessed_id, $Id_Item, $Qtty_I);

            if ( $itemChangeIn->qtd > $Qtty_I) {
              $itemChangeIn->qtd -= $Qtty_I;
              $itemChangeIn->save();

              $ownerInventory = Inventory::whereSurvivorId($owner_id)->whereItemId($itemChangeIn->item_id)->first();
              if (empty($ownerInventory)) {
                Inventory::create([
                  'survivor_id' => $owner_id,
                  'item_id' => $itemChangeIn->item_id,
                  'qtd' => $Qtty_I
                ]);
              } else {
                $ownerInventory->qtd += $Qtty_I;
                $ownerInventory->save();
              }

            } else {
              $ownerInventory = Inventory::whereSurvivorId($owner_id)->whereItemId($itemChangeIn->item_id)->first();
              if (!empty($ownerInventory)) {
                $ownerInventory->qtd += $itemChangeIn->qtd;
                $ownerInventory->save();
                $itemChangeIn->delete();
              } else {
                $itemChangeIn->survivor_id = $owner_id;
                $itemChangeIn->save();
              }
            }
        }

        $itemsWanted = explode(",", $items_wanted);
        foreach ($itemsWanted as $i) {

            list($id_item_wanted, $qtty_wanted) = explode(":", $i);
            $itemChangeOW = Inventory::getInventory($owner_id, $id_item_wanted, $qtty_wanted);

            if ( $itemChangeOW->qtd > $qtty_wanted) {
              $itemChangeOW->qtd -= $qtty_wanted;
              $itemChangeOW->save();

              $InteressedInventory = Inventory::whereSurvivorId($interessed_id)->whereItemId($itemChangeOW->item_id)->first();
              if (empty($InteressedInventory)) {
                Inventory::create([
                  'survivor_id' => $interessed_id,
                  'item_id' => $itemChangeOW->item_id,
                  'qtd' => $qtty_wanted
                ]);
              } else {
                $InteressedInventory->qtd += $qtty_wanted;
                $InteressedInventory->save();
              }

            } else {
              $InteressedInventory = Inventory::whereSurvivorId($interessed_id)->whereItemId($itemChangeOW->item_id)->first();
              if (!empty($InteressedInventory)) {
                  $InteressedInventory->qtd += $itemChangeOW->qtd;
                  $InteressedInventory->save();
                  $itemChangeOW->delete();
              } else {
                  $itemChangeOW->survivor_id = $interessed_id;
                  $itemChangeOW->save();
              }
            }
        }

    }

}
