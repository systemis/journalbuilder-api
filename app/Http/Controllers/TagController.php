<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenIdService;
use App\Models\Project;
use App\Models\Tag;

class TagController extends Controller
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
   * @todo The function to create tag.
   */
  public function createTag(Request $request)
  {
    return $this->openIdService->gaurd($request, "admin", function () use ($request) {
      /** @var Project $project */
      $dto = array(
        "name" => $request->input("name"),
      );

      /**
       * @todo Check if the project is already exists with this name.
       */
      $exist = Tag::where("name", "=", $dto["name"])->exists();
      if ($exist) {
        return response()->json([
          "data" => "The tag with name is already created."
        ], 409, [], JSON_PRETTY_PRINT);
      }

      /**
       * @todo Now create new project with dto.
       */
      $tag = new Tag;
      $tag->name = $dto["name"];
      $tag->save();

      return response()->json([
        "data" => $tag,
      ], 200, [], JSON_PRETTY_PRINT);
    });
  }

  /**
   * @todo The function to get tag list.
   */
  public function getTags()
  {
    /**
     * @todo Find in db following $id
     */
    $tags = Tag::all();


    return response()->json([
      "data" => $tags,
    ], 200, [], JSON_PRETTY_PRINT);
  }
}
