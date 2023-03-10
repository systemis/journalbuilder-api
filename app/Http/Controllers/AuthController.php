<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Services\OpenIdService;
use Error;

class AuthController extends Controller
{
  /**
   * @todo Declare openId service to use.
   */
  private readonly OpenIdService $openIdService;

  /**
   * @todo Initialize controller.
   */
  function __construct()
  {
    $this->openIdService = new OpenIdService();
  }

  /**
   * @todo The function to login external api.
   */
  public function subExternal($username, $password, $scope)
  {
    /**
     * @todo Initialize form body to request to  server.
     */
    $credential = array(
      "username" => $username,
      "password" => $password,
      "audience" => env("AUTH0_AUDIENCE"),
      "client_id" => env("AUTH0_CLIENT_ID"),
      "client_secret" => env("AUTH0_SECRET"),
      "grant_type" => "password",
    );

    /**
     * @todo Condition to gant scope for token.
     */
    if ($scope == "write") {
      $credential["scopes"] = "update:client_grants";
    } else {
      $credential["scope"] = "openid";
    }

    /**
     * @todo Login with external api by request credentials.
     */
    $response = Http::post(env("AUTH0_DOMAIN") . "/oauth/token", $credential);
    return $response;
  }

  public function gantAccessToken($username, $password, $scope)
  {
    /**
     * @todo Login with external api by request credentials.
     */
    $response = $this->subExternal($username, $password, $scope);

    return response()->json([
      'data' => json_decode($response->body(), true),
    ], $response->status(), [], JSON_PRETTY_PRINT);
  }

  /**
   * @todo The function to login with external server to gant authenticated session.
   */
  public function login(Request $request)
  {
    /**
     * @todo Initialize form body to request to  server.
     */
    return $this->gantAccessToken(
      $request->input("username"),
      $request->input("password"),
      "read"
    );
  }

  /**
   * @todo The function to gant permission to update user profile.
   */
  public function requestPermission(Request $request)
  {
    /**
     * @todo Initialize form body to request to  server.
     */
    return $this->gantAccessToken(
      $request->input("username"),
      $request->input("password"),
      "write"
    );
  }

  /**
   * @todo The function to register with external server to create a new user.
   */
  public function register(Request $request)
  {
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
      "picture" => "https://source.boringavatars.com/beam/120/" . rand(),
      "user_metadata" => array(
        "plan" => "silver",
        "team_id" => "a111",
      ),
      "private" => false,
      "membership" => "",
      "client_id" => env("AUTH0_CLIENT_ID"),
      "connection" => env("AUTH0_CONNECTION"),
    );

    /**
     * @todo Signup with external api by request credentials.
     */
    $response = Http::post(env("AUTH0_DOMAIN") . "/dbconnections/signup", $credential);

    /**
     * @todo If signup was successfull, now create new user following above credential.
     */
    if ($response->status() == 200) {
      $user = new User;
      $user->username = $credential["username"];
      $user->email = $credential["email"];
      $user->given_name = $credential["given_name"];
      $user->family_name = $credential["family_name"];
      $user->name = $credential["name"];
      $user->picture = $credential["picture"];
      $user->sub = json_decode($response->body())->_id;
      $user->save();
    }

    return response()->json([
      'data' => json_decode($response->body(), true),
    ], $response->status(), [], JSON_PRETTY_PRINT);
  }

  /**
   * @todo The function to update user's password.
   */
  public function changePassword(Request $request)
  {
    return $this->openIdService->idpIntrospect(
      $request,
      function ($request, $token, $userId) {
        /**
         * @todo Initialize credential for calling to external server.
         */
        $credential = array(
          "password" => $request->input('password'),
          "connection" => env("AUTH0_CONNECTION"),
        );

        /**
         * @todo Call external server to update password.
         */
        $response = Http::withToken($token)->patch(env("AUTH0_AUDIENCE") . "users/auth0|" . $userId, $credential);

        return response()->json([
          'data' => json_decode($response->body(), true),
        ], $response->status(), [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function get user's roles.
   */
  public function getRoles(Request $request)
  {
    return $this->openIdService->gaurd(
      $request,
      "admin",
      function ($data) {
        return response()->json([
          "data" => $data,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function to login with admin permission.
   */
  public function loginAdmin(Request $request)
  {
    /**
     * @todo Initialize form body to request to  server.
     * @todo Login with external api by request credentials.
     */
    $authResponse = $this->subExternal(
      $request->input("username"),
      $request->input("password"),
      "read"
    );

    if ($authResponse->status() != 200) {
      return response()->json([
        "data" => json_decode($authResponse->body())
      ], $authResponse->status(), [], JSON_PRETTY_PRINT);
    }

    $token = json_decode($authResponse->body(), true)["access_token"];

    /**
     * @todo Valid user profile with access token.
     */
    $userResponse = Http::withToken($token)
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

    return $this->openIdService->idpPublic(
      function ($token) use ($authResponse, $userResponse) {
        error_log(json_encode($userResponse));
        /**
         * @todo Call external server to update password.
         */
        $response = Http::withToken($token)->get(env("AUTH0_AUDIENCE") . "users/" . $userResponse["sub"] . "/roles");

        /**
         * @todo Get user roles.
         */
        $roles = json_decode($response->body(), true);
        error_log(json_encode($roles));

        /**
         * @todo Define condition.
         */
        $isValid = false;

        /**
         * @todo Check if target role is exists in user's roles.
         */
        foreach ($roles as $role) {
          if ($role["name"] == "admin") {
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

        return response()->json([
          'data' => json_decode($authResponse->body(), true),
        ], $authResponse->status(), [], JSON_PRETTY_PRINT);
      }
    );
  }
}
