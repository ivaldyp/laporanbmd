<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('index');
// });

// Route::get('/home', function () {
//     return view('index');
// });

Route::get('/', 'LandingController@index');
Route::get('/home', 'HomeController@index');
Route::POST('/mail', 'HomeController@feedback');

Route::get('/profil', function () {
    return view('pages.profil');
});

// Route::get('/home', function () {
//     return view('layouts.masterhome');
// });

Route::group(['prefix' => 'content'], function () {
	Route::get('/berita', 'ContentController@berita_all');
	Route::get('/berita/{id}', 'ContentController@berita_read');
	Route::get('/foto', 'ContentController@foto_all');
	Route::get('/foto/{id}', 'ContentController@foto_open');
	Route::get('/video', 'ContentController@video_all');
	Route::get('/video/{id}', 'ContentController@video_open');
});

Route::group(['prefix' => 'security'], function () {
	Route::get('/groupuser', 'SecurityController@grupall');
	Route::get('/groupuser/ubah', 'SecurityController@grupubah');
});
Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');
