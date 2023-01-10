<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Models\Feedback;
use App\Models\User;
use App\Services\OpenIdService;

class FeedbackController extends Controller
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

  public function getProductFeedbacks(Request $request, $productId)
  {
    error_log("productId" . $productId);
    $feedback = Feedback::where("productId", "=", $productId)->orderBy('updated_at', 'desc')->get();

    /**
     * @todo Assign user info to product
     */
    foreach ($feedback as $f) {
      $user = User::where("sub", "=", $f["userId"])->first();
      if ($user) {
        $f["owner"] = array(
          "name" => $user["name"],
          "picture" => $user["picture"],
          "username" => $user["username"],
          "sub" => $user["sub"],
        );
      }
    }

    return response([
      "data" => $feedback
    ], 200, [], JSON_PRETTY_PRINT);
  }

  /**
   * @todo The function to send feedback about the product
   */
  public function sendFeedback(Request $request)
  {
    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) use ($request) {
        /**
         * @todo Now create new feedback with dto.
         */
        $feedback = new Feedback;
        $feedback->userId = $userId;
        $feedback->productId = $request->input("productId");
        $feedback->message = $request->input("message");

        $feedback->save();

        return response()->json([
          "data" => $feedback,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function to remove feedback
   */
  public function deleteFeedback(Request $request)
  {
    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) use ($request) {
        /** @todo Find feedback with id */
        $data = Feedback::where("_id", "=", $request->input("productId"));
        if (!$data->exists()) {
          return response([
            "data" => "Not found"
          ], 200, [], JSON_PRETTY_PRINT);
        }

        /** @todo Check owner of feedback is user. */
        $feedback = $data->first();
        if ($feedback->userId !== $userId) {
          return response([], 403, [], JSON_PRETTY_PRINT);
        }

        $feedback->delete();
        return response([], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }
}
