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

Route::get('/home', 'HomeController@index');
Route::get('/annotation', 'AnnotationController@search');
Route::post('/annotation', 'AnnotationController@store');
Route::put('/annotation/{id}', 'AnnotationController@update');
Route::get('/annotation/{id}', 'AnnotationController@index');
Route::delete('/annotation/{id}', 'AnnotationController@delete');

Route::get('/stamp/{pdf}/{page}', 'StampController@index');
Route::post('/stamp', 'StampController@store');
Route::put('/stamp/{id}', 'StampController@update');
Route::delete('/stamp/{id}', 'StampController@delete');
