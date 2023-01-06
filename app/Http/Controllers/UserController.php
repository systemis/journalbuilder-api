<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Services\OpenIdService;

class UserController extends Controller
{

  /**
   * @todo Declare openId service to use.
   */
  private readonly OpenIdService $openIdService;

  /**
   * @todo Declare auth provider to use.
   */
  private readonly AuthController $authProvider;

  /**
   * @todo Initialize controller.
   */
  function __construct()
  {
    /**
     * @todo Initialize openId service.
     */
    $this->openIdService = new OpenIdService();

    /**
     * @todo Initialize auth provider.
     */
    $this->authProvider = new AuthController();
  }

  /**
   * @todo The function to get user profile.
   */
  public function getProfile(Request $request)
  {
    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) {
        /**
         * @todo Find user in database with sub id.
         */
        $user = User::where("sub", $userId)->first();

        /**
         * @return Response.
         */
        return response()->json([
          "data" => $user,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }


  /**
   * @todo The function to update profile internal & external.
   */
  public function updateProfile(Request $request)
  {
    return $this->openIdService->idpIntrospect(
      $request,
      function ($request, $token, $userId) {
        /**
         * @todo Find in db following $id
         */
        $user = User::where("sub", "=", $userId);

        /**
         * @todo Throw exception when dont found any projects with the Id.
         */
        if (!$user->exists()) {
          return response()->json([
            "data" => "Not found"
          ], 404, [], JSON_PRETTY_PRINT);
        }

        /**
         * @todo Get document.
         */
        $user = $user->first();
        $external_payload = array();

        /**
         * @todo Loop in @var $request and assign into query parameters to execute the document query.
         */
        foreach ($request->except('_token') as $key => $value) {
          if (in_array($key, User::$editable_columns)) {
            $user->$key = $value;
          }

          if (in_array($key, User::$editable_external_columns)) {
            $external_payload[$key] = $value;
          }
        }
        /**
         * @todo Call external server to update external.
         */
        if (!empty($external_payload)) {
          $response = Http::withToken($token)
            ->patch(
              env("AUTH0_AUDIENCE") . "users/auth0|" . $userId,
              $external_payload
            );

          /**
           * @todo Throw exception when calling external api failed.
           */
          if ($response->status() != 200) {
            return response()->json([
              "data" => $response->body()
            ], $response->status(), [], JSON_PRETTY_PRINT);
          }
        }

        /**
         * @todo Restrict updating document in database.
         */
        $user->save();

        return response()->json([
          'data' => $user,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function to get public user by id.
   */
  public function getPublicUser(Request $request, string $id)
  {
    return $this->openIdService->idpPublic(
      function ($token) use ($id) {
        Http::withToken($token)
          ->get(
            env("AUTH0_AUDIENCE") . "users",
            array(
              "q" => "auth0|" . $id,
              "auth0|" => "v3",
            )
          );

        /**
         * @todo Find user in database with sub id.
         */
        $user = User::where("sub", $id)->first();

        /**
         * @return Response.
         */
        return response()->json([
          "data" => $user,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function to update user password.
   */
  public function updatePassword(Request $request)
  {

    return $this->openIdService->idpIntrospect(
      $request,
      function ($request, $token, $userId) {
        /**
         * @todo Find in db following $id
         */
        $user = User::where("sub", "=", $userId);

        /**
         * @todo Throw exception when dont found any projects with the Id.
         */
        if (!$user->exists()) {
          return response()->json([
            "data" => "Not found"
          ], 404, [], JSON_PRETTY_PRINT);
        }

        /**
         * @todo Get document.
         */
        $user = $user->first();

        /**
         * @todo Validate password before adjusting.
         */
        $authValidated = $this->authProvider->subExternal(
          $user->username,
          $request->input("oldPassword"),
          "read"
        );

        /**
         * @todo Throw error if old paassword does not match.
         */
        if ($authValidated->status() != 200) {
          return response()->json([
            "data" => "Old password does not match"
          ], 401, [], JSON_PRETTY_PRINT);
        }

        /**
         * @todo Initialize payload to change in external api.
         */
        $external_payload = array();
        
        /**
         * @todo Assign payload to change
         */
        $external_payload["password"] = $request->input("newPassword");

        /**
         * @todo Call external server to update external.
         */
        if (!empty($external_payload)) {
          $response = Http::withToken($token)
            ->patch(
              env("AUTH0_AUDIENCE") . "users/auth0|" . $userId,
              $external_payload
            );

          /**
           * @todo Throw exception when calling external api failed.
           */
          if ($response->status() != 200) {
            return response()->json([
              "data" => $response->body()
            ], $response->status(), [], JSON_PRETTY_PRINT);
          }
        }

        /**
         * @todo Restrict updating document in database.
         */
        $user->save();

        return response()->json([
          'data' => $user,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function to get list of user by admin permission.
   */
  public function getUsersAdmin(Request $request)
  {
    return $this->openIdService->gaurd($request, "admin", function () use ($request) {
      /** @todo Find users in db. */
      $users = User::all();

      return response()->json([
        "data" => $users,
      ], 200, [], JSON_PRETTY_PRINT);
    });
  }
}
