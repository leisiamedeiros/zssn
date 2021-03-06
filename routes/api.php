<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('survivor')->group(function () {

    Route::post('/new', 'SurvivorController@store');
    Route::put('/{id}/location/update', 'SurvivorController@updateLocation');
    Route::post('/{id}/report', 'SurvivorController@flagSurvivorInfected');
    Route::post('/{id}/trade', 'SurvivorController@tradeItems');

});

Route::get('report/infected', 'ReportsController@survivorsInfecteds');
Route::get('report/non-infected', 'ReportsController@survivorsNonInfected');
