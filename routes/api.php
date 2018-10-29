<?php

Route::group(['namespace' => 'API\Auth'], function () {
    Route::post('sign-up', 'SignUpController@signUp');
    Route::post('sign-in', 'SignInController@signIn');
});
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('sign-out', 'API\Auth\AccountController@signOut');
});
Route::group(['namespace' => 'Auth', 'middleware' => 'api', 'prefix' => 'password'], function () {
    Route::post('recovery',    'PasswordResetController@recovery');
    Route::get('find/{token}', 'PasswordResetController@find');
    Route::post('reset',       'PasswordResetController@reset');
});

Route::group(['middleware' => ['cors', 'auth:api', 'license', 'namespace' => 'API']], function () {
    Route::get('account', 'UserController@account');
    Route::post('account', 'UserController@update');
});
