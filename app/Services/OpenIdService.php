<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenIdService
{
  /**
   * @todo The function to instrospect token to gant write permission with credential.
   * @var Request $request  Network request.
   * @var callable $next    The callback function to return.
   */
  public function idpIntrospect(Request $request, callable $next)
  {
    /**
     * @todo Instropect permission
     * @var Request
     * */
    $credential = array(
      "audience" => env("AUTH0_AUDIENCE"),
      "client_id" => env("AUTH0_CLIENT_ID"),
      "client_secret" => env("AUTH0_SECRET"),
      "grant_type" => "client_credentials",
    );

    /**
     * @todo Introspect with external api by request credentials.
     */
    $response = Http::post(env("AUTH0_DOMAIN") . "/oauth/token", $credential);

    /**
     * @todo Throw error if failed call to external server.
     */
    if ($response->status() != 200) {
      return response()->json([
        'data' => json_decode($response->body(), true),
      ], $response->status(), [], JSON_PRETTY_PRINT);
    }

    /**
     * @todo Valid user profile with access token.
     */
    $userResponse = Http::withToken($request->bearerToken())
      ->withHeaders(["Content-Type" => "application/json'"])
      ->get(env("AUTH0_DOMAIN") . "/userinfo");

    /**
     * @todo Throw error if failed call to external server.
     */
    if ($userResponse->status() != 200) {
      return response()->json([
        'data' => json_decode($userResponse->body(), true),
      ], $userResponse->status(), [], JSON_PRETTY_PRINT);
    }

    error_log($userResponse->body());

    return $next(
      $request,
      json_decode($response->body())->access_token,
      substr(json_decode($userResponse->body())->sub, 6),
    );
  }

  /**
   * @todo The function to instrospect token to gant read permission with credential.
   * @var Request $request  Network request.
   * @var callable $next    The callback function to return.
   */
  public function openIdIntrospect(Request $request, callable $next)
  {
    /**
     * @todo Valid user profile with access token.
     */
    $response = Http::withToken($request->bearerToken())
      ->withHeaders(["Content-Type" => "application/json'"])
      ->get(env("AUTH0_DOMAIN") . "/userinfo");

    /**
     * @todo Throw error if failed call to external server.
     */
    if ($response->status() != 200) {
      return response()->json([
        'data' => json_decode($response->body(), true),
      ], $response->status(), [], JSON_PRETTY_PRINT);
    }

    /**
     * @todo Must slice sub id to get only id without `auth0`.
     * @return Callback
     */
    return $next(
      substr(json_decode($response->body())->sub, 6),
    );
  }
}
