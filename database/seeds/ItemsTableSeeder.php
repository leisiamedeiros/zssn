<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('items')->insert([
          ['id' => 1, 'item' => 'Water', 'points' => 4],
          ['id' => 2, 'item' => 'Food', 'points' => 3],
          ['id' => 3, 'item' => 'Medication', 'points' => 2],
          ['id' => 4, 'item' => 'Ammunition', 'points' => 1],
        ]);
    }
}
