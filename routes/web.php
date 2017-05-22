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

use App\User;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('room/showallposts/{postType}', 'RoomController@showAllPosts')->name('room.showallposts');

Route::get('profile/image/update/{userId}', ['as' => 'room.updateProfileImage', function ($userId) {
    if ($userId != Auth::id()) {
        return view('unauthorizedAccess');
    }

    return View::make('profileImageUpdateForm')
        ->with('userId', $userId);
}]);

Route::post('profile/image/update/{userId}', 'RoomController@updateProfileImage');

Route::get('profile/info/update/{userId}', ['as' => 'room.updateProfileInfo', function ($userId) {
    if ($userId != Auth::id()) {
        return view('unauthorizedAccess');
    }

    return View::make('profileInfoUpdateForm')
        ->with('user', User::find($userId));
}]);

Route::post('profile/info/update/{userId}', 'RoomController@updateProfileInfo');

Route::get('room/profile/{userId}', 'RoomController@viewProfile')->name('room.profile');

Route::get('recoverPasswordInitiate', ['as' => 'forgotPasswordInitiate', function () {
    return View::make('forgotPasswordForm');
}]);

Route::post('forgotPasswordCheckStatus', 'UserController@forgotPassword')->name('forgotPasswordCheckStatus');

Route::get('user/{userId}/post/{postId}/destroy', ['as' => 'room.post.destroy', function ($userId, $postId) {
    if ($userId != Auth::id()) {
        return view('unauthorizedAccess');
    }
    app()
        ->make('App\Http\Controllers\RoomController')
        ->callAction('destroyPost', $parameters = ['postId' => $postId]);
}]);

Route::resource('/room', 'RoomController', ['except' => 'index']);


