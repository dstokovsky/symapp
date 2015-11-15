<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::post('oauth/access_token', 'OAuthController@postAccessToken');
Route::post('/api/users', 'UserController@create');
Route::group(array('before' => 'oauth'), function(){
    //-----------------------Blacklists's actions--------------------------------------
    Route::post('/api/blacklist/{user_id}/{banned_user_id}', 'BlacklistController@add')
        ->where(['user_id' => '[0-9]+', 'banned_user_id' => '[0-9]+']);
    Route::get('/api/blacklist/{user_id}', 'BlacklistController@show')
        ->where('user_id', '[0-9]+');
    Route::delete('/api/blacklist/{user_id}/{banned_user_id}', 'BlacklistController@delete')
        ->where(['user_id' => '[0-9]+', 'banned_user_id' => '[0-9]+']);
    Route::resource('blacklist', 'BlacklistController');
    //---------------------------------------------------------------------------------
    
    //-----------------------Friend's actions--------------------------------------
    Route::post('/api/friends/{user_id}/{friend_id}', 'FriendController@add')
        ->where(['user_id' => '[0-9]+', 'friend_id' => '[0-9]+']);
    Route::get('/api/friends/{id}/follows', 'FriendController@follows')
        ->where('id', '[0-9]+');
    Route::get('/api/friends/{id}/followers', 'FriendController@followers')
        ->where('id', '[0-9]+');
    Route::delete('/api/friends/{user_id}/{friend_id}', 'FriendController@delete')
        ->where(['user_id' => '[0-9]+', 'friend_id' => '[0-9]+']);
    Route::resource('friend', 'FriendController');
    //---------------------------------------------------------------------------------
    
    //-----------------------Message's actions--------------------------------------
    Route::post('/api/messages/{author_id}/{recipient_id}', 'MessageController@post')
        ->where(['author_id' => '[0-9]+', 'recipient_id' => '[0-9]+']);
    Route::get('/api/messages/{user_id}/history', 'MessageController@history')
        ->where('user_id', '[0-9]+');
    Route::get('/api/messages/{author_id}/{recipient_id}', 'MessageController@chat')
        ->where(['author_id' => '[0-9]+', 'recipient_id' => '[0-9]+']);
    Route::delete('/api/messages/{author_id}/{recipient_id}', 'MessageController@delete')
        ->where(['author_id' => '[0-9]+', 'recipient_id' => '[0-9]+']);
    Route::resource('message', 'MessageController');
    //---------------------------------------------------------------------------------
    
    //-----------------------User's actions--------------------------------------
    Route::get('/api/users/{id}', 'UserController@show')
        ->where('id', '[0-9]+');
    Route::put('/api/users/{id}', 'UserController@edit')
        ->where('id', '[0-9]+');
    Route::delete('/api/users/{id}', 'UserController@delete')
        ->where('id', '[0-9]+');
    Route::resource('user', 'UserController');
    //---------------------------------------------------------------------------------
});