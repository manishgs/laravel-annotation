<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::get('/home',function(){
    return redirect('/annotation/1');
});

Route::get('/annotation', 'AnnotationController@list');
Route::get('/annotation/{id}/search', 'AnnotationController@search');
Route::delete('/annotation/{id}/deleteAll', 'AnnotationController@deleteAll');

Route::get('/annotation/{id}', 'AnnotationController@index');
Route::post('/annotation', 'AnnotationController@store');
Route::put('/annotation/{id}', 'AnnotationController@update');
Route::delete('/annotation/{id}', 'AnnotationController@delete');

Route::get('/stamp/{pdf}/{page}', 'StampController@index');
Route::post('/stamp', 'StampController@store');
Route::put('/stamp/{id}', 'StampController@update');
Route::delete('/stamp/{id}', 'StampController@delete');
