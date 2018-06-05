<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Survivor;
use App\Infected;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    // Percentage of infected survivors.
    public function survivorsInfecteds()
    {
        $survivors = Survivor::all()->count();

        $infected = Infected::select(DB::raw('count(distinct survivor_id)'))
                     ->whereStatus(true)->first()->count;

        $percentage = ($infected * 100) / $survivors;
        return response()->json(
          ['message' => 'Percentage of infected survivors', 'value' => $percentage], 200);
    }

    // Percentage of non-infected survivors.
    public function survivorsNonInfected()
    {
      $survivors = Survivor::all()->count();

      $infected = Infected::select(DB::raw('count(distinct survivor_id)'))
                   ->whereStatus(true)->first()->count;

      $nonInfected = $survivors - $infected;
      $percentage = ($nonInfected * 100) / $survivors;

      return response()->json(
        ['message' => 'Percentage of non-infected survivors.', 'value' => $percentage], 200);

    }


}
