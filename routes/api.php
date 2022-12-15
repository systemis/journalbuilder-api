<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::get('/public', function () {
  return response()->json([
    'message' => 'Hello from a public endpoint! You don\'t need to be authenticated to see this.',
    'authorized' => Auth::check(),
    'user' => Auth::check() ? json_decode(json_encode((array) Auth::user(), JSON_THROW_ON_ERROR), true) : null,
  ], 200, [], JSON_PRETTY_PRINT);
})->middleware(['auth0.authorize.optional']);

/**
 * @todo Group all api related user information.
 */
Route::controller(UserController::class)->prefix(("user"))->group(function() {
  /**
   * @todo Get user profile
   * @var User $user
   * */
  Route::get("/profile", "getProfile");
})->middleware(['auth0.authorize']);

/**
 * @todo Group all api realeated to authentication.
 */
Route::controller(AuthController::class)->prefix(("auth"))->group(function () {
  /**
   * @todo Login by email & password
   * @var String $accessToken
   * */
  Route::post("/login","login");
  Route::post("/register","register");
})->middleware(['auth0.authorize.optional']);