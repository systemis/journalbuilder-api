<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @todo The function to login with external server to gant authenticated session.
     */
    public function login(Request $request) {
      /**
       * @todo Initialize form body to request to  server.
       */
      $credential = array(
        "username" => $request->input("username"),
        "password" => $request->input("password"),
        "audience" => env("AUTH0_AUDIENCE"),
        "client_id" => env("AUTH0_CLIENT_ID"),
        "client_secret" => env("AUTH0_SECRET"),
        "scope" => "openid",
        "grant_type" => "password",
      );

      error_log(json_encode($credential));

      /**
       * @todo Login with external api by request credentials.
       */
      $response = Http::post(env("AUTH0_DOMAIN")."/oauth/token", $credential);

      return response()->json([
          'data' => json_decode($response->body(), true),
        ], $response->status(), [], JSON_PRETTY_PRINT);
    }

    /**
     * @todo The function to register with external server to create a new user.
     */
    public function register(Request $request) {
      /**
       * @todo Initialize form body to request to  server.
       */
      $credential = array(
        "username" => $request->input("username"),
        "email" => $request->input("email"),
        "password" => $request->input("password"),
        "given_name" => $request->input("given_name"),
        "family_name" => $request->input("family_name"),
        "name" => $request->input("name"),
        "picture" => "http://example.org/jdoe.png",
        "user_metadata" => array(
          "plan" => "silver",
          "team_id" => "a111",
        ),
        "client_id" => env("AUTH0_CLIENT_ID"),
        "connection" => env("AUTH0_CONNECTION"),
      );

      /**
       * @todo Signup with external api by request credentials.
       */
      $response = Http::post(env("AUTH0_DOMAIN")."/dbconnections/signup", $credential);

      /**
       * @todo If signup was successfull, now create new user following above credential.
       */
      error_log($credential["username"]);
      if ($response->status() == 200) {
        $user = new User;
        $user->username = $credential["username"];
        $user->email = $credential["email"];
        $user->given_name = $credential["given_name"];
        $user->family_name = $credential["family_name"];
        $user->name = $credential["name"];
        $user->save();
      }

      return response()->json([
          'data' => json_decode($response->body(), true),
        ], $response->status(), [], JSON_PRETTY_PRINT);
    }
}
