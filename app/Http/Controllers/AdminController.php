<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\OpenIdService;
use App\Models\Project;
use App\Models\User;

class AdminController extends Controller
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
   * @todo The function to get info of app.
   */
  public function getAppOverview(Request $request)
  {
    return $this->openIdService->gaurd($request, "admin", function () {
      $projects = Project::all();
      $products = Product::all();
      $users = User::all();

      return response()->json([
        "data" => array(
          "totalProjects" => count($projects),
          "totalProducts" => count($products),
          "totalUsers" => count($users),
        ),
      ], 200, [], JSON_PRETTY_PRINT);
    });
  }

  /**
   * @todo The function to force reset users password.
   */
  public function resetUserPassword(Request $request, string $userId)
  {
    return $this->openIdService->gaurd($request, "admin", function () use ($request, $userId) {
      $data = '1234567890ABCDEFGHIJ1234857923KLMNOPQRSTUVWXYZabcefghijkl!#$mnopqrst@uvwxyz';
      $chars = 10;
      $password = substr(str_shuffle($data), 0, $chars);


      return $this->openIdService->idpPublic(function ($token) use ($userId, $password) {
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
        $external_payload["password"] = $password;

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
          'newPassword' => $password,
        ], 200, [], JSON_PRETTY_PRINT);
      });
    });
  }
}
