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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('user/create','UserController@store');

Route::get('/registration/{token}','UserController@activation');

Route::get('user/show/{userId}','UserController@show');

Route::post('/forgotPassword','UserController@forgotPassword');

Route::get('/showForgotPasswordForm/{email}/{forgotPasswordToken}','UserController@forgotPasswordForm');

Route::post('/savePassword','UserController@forgotPasswordStore');

Route::post('/logout','UserController@logout');

Route::post('/login','UserController@login');

Route::put('user/update','UserController@update');

Route::put('/changePassword','UserController@changePassword');

Route::get('/delete/{id}','UserController@delete');

Route::get('/getFile/{filename}', 'ImageController@getFile')
    ->name('file.get');

Route::post('post/create', 'PostController@savePost');

Route::get('/fetchAllPost', 'PostController@fetchAllPost');

Route::get('/fetchPostOfParticularArea', 'PostController@fetchPostOfParticularArea');

Route::get('/fetchPersonalPost/{apiToken}', 'PostController@fetchPersonalPost');

Route::get('/fetchPost/{postType}', 'PostController@fetchPost');