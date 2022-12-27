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
      error_log($response->body());
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
      ->get(env("AUTH0_DOMAIN") . "/tokeninfo", [
        "id_token" => $request->input("id_token"),
      ]);


    /**
     * @todo Throw error if failed call to external server.
     */
    if ($response->status() != 200) {
      error_log($response->body());
      return response()->json([
        'data' => json_decode($response->body(), true),
      ], $response->status(), [], JSON_PRETTY_PRINT);
    }

    /**
     * @todo Must slice sub id to get only id without `auth0`.
     * @return Callback
     */
    return $next(
      substr(json_decode($response->body())->user_id, 6),
    );
  }

  /**
   * @todo The function to condition which user having admin permission or not.
   * @var Request $request  Network request.
   * @var callable $next    The callback function to return.
   */
  public function gaurd(Request $request, string $target, callable $next)
  {
    return $this->idpIntrospect(
      $request,
      function ($request, $token, $userId) use ($next, $target) {
        /**
         * @todo Call external server to update password.
         */
        $response = Http::withToken($token)->get(env("AUTH0_AUDIENCE") . "users/auth0|" . $userId . "/roles");

        /**
         * @todo Throw error when request failed.
         */
        if ($response->status() != 200) {
          return response()->json(
            [],
            $response->status(),
            [],
            JSON_PRETTY_PRINT
          );
        }

        /**
         * @todo Get user roles.
         */
        $roles = json_decode($response->body(), true);

        /**
         * @todo Define condition.
         */
        $isValid = false;

        /**
         * @todo Check if target role is exists in user's roles.
         */
        foreach ($roles as $role) {
          if ($role["name"] == $target) {
            $isValid = true;
          }
        }

        /**
         * @todo Throw 403 error if user dont have permissions.
         */
        if (!$isValid) {
          return response()->json([
            "data" => "Need admin permission to excute function"
          ], 403, [], JSON_PRETTY_PRINT);
        }

        return $next($roles);
      }
    );
  }
}
