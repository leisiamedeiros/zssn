<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReporterColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('infected', function (Blueprint $table) {
            $table->dropUnique('infected_survivor_id_unique');
            $table->dropColumn('related');
            $table->integer('reporter_id');
            $table->foreign('reporter_id')->references('id')->on('survivors')
            ->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['survivor_id', 'reporter_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('infected', function (Blueprint $table) {
            $table->dropColumn('reporter_id');
            $table->integer('related');
        });
    }
}
