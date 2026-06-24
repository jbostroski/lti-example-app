<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Most URLs will be handled by the '/lti` URL.  The LMS will ask for several URLs when you install
// the tool there.  All URLs will be `/lti`, except for the 'JWKS URL' or 'Public Keyset URL', which will be `/lti/jwks`.
Route::post('/lti', [App\Http\Controllers\LtiController::class, 'ltiMessage']);
Route::get('/lti/jwks', [App\Http\Controllers\LtiController::class, 'getJWKS']);

// These are just demo URLs, to show how to use the library to read/write from the LMS once the LMS launches your tool.
Route::get('/my_app_home', [App\Http\Controllers\LtiController::class, 'myAppHome']);
Route::get('/testRoster', [App\Http\Controllers\LtiController::class, 'testRoster']);
Route::get('/testLineItem', [App\Http\Controllers\LtiController::class, 'testLineItem']);
Route::get('/testLineItemSet', [App\Http\Controllers\LtiController::class, 'testLineItemSet']);
Route::get('/testLineItemUpdateScore', [App\Http\Controllers\LtiController::class, 'testLineItemUpdateScore']);
Route::get('/test1', [App\Http\Controllers\LtiController::class, 'test1']);
Route::get('/test2', [App\Http\Controllers\LtiController::class, 'test2']);
Route::get('/test3', [App\Http\Controllers\LtiController::class, 'test3']);
Route::get('/test4', [App\Http\Controllers\LtiController::class, 'test4']);
