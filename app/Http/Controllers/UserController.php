<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function getProfile(Request $request) {
      $response = Http::withToken($request->bearerToken())
        ->withHeaders(["Content-Type" => "application/json'"])
        ->get(env("AUTH0_DOMAIN")."/userinfo");

      return response()->json([
        'user' => json_decode($response->body(), true),
      ], 200, [], JSON_PRETTY_PRINT);
    }
}
