<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenIdService;
use App\Models\Project;

class JournalController extends Controller
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
   * @todo The function to create project.
   */
  public function createProject(Request $request)
  {
    /** @var Project $project */
    global $dto;
    $dto = array(
      "name" => $request->input("name"),
      "image" => $request->input("image"),
      "description" => $request->input("description"),
    );

    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) use ($dto) {
        /**
         * @todo Assign userId for dto to create project.
         */
        $dto["userId"] = $userId;
        error_log(json_encode($dto));

        /**
         * @todo Check if the project is already exists with this name.
         */
        $exist = Project::where("userId", "=", $userId)
          ->where("name", "=", $dto["name"])->exists();
        if ($exist) {
          return response()->json([
            "data" => "The project with name is already created."
          ], 409, [], JSON_PRETTY_PRINT);
        }

        /**
         * @todo Now create new project with dto.
         */
        $project = new Project;
        $project->name = $dto["name"];
        $project->image = $dto["image"];
        $project->description = $dto["description"];
        $project->userId = $dto["userId"];
        $project->save();

        return response()->json([
          "data" => $project,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function to get detail of project following id.
   */
  public function getProject(Request $request, $id)
  {
    /**
     * @todo Find in db following $id
     */
    $project = Project::find($id);

    /**
     * @todo Return error when not found with the id.
     */
    if ($project->exists() == false) {
      return response()->json([
        "data" => "Not Found"
      ], 401, [], JSON_PRETTY_PRINT);
    }

    return response()->json([
      "data" => $project,
    ], 200, [], JSON_PRETTY_PRINT);
  }

  /**
   * @todo The function to delete project with the id.
   */
  public function deleteProject(Request $request, $id)
  {
    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) use ($id) {
        /**
         * @todo Find in db following $id
         */
        $project =
          Project::where('_id', "=" . $id)
          ->where("userId", "=" . $userId);

        /**
         * @todo Return error when not found with the id.
         */
        if ($project->exists() == false) {
          return response()->json([
            "data" => "Not Found"
          ], 404, [], JSON_PRETTY_PRINT);
        }

        /**
         * @todo Delete document.
         */
        $project->delete();
        return response()->json([], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function to get project list of a user
   */
  public function getProjects(Request $request)
  {
    /**
     * @todo Loop in @var $request and assign into query parameters to execute the document query.
     */
    $query = array();
    foreach ($request->except('_token') as $key => $value) {
      $query[$key] = $value;
    }

    /**
     * @todo Find in db following $id
     */
    $projects = Project::where($query)->get();
    return response()->json([
      "data" => $projects,
    ], 200, [], JSON_PRETTY_PRINT);
  }

  /**
   * @todo The function to edit project of user
   */
  public function editProjecct(Request $request, $id)
  {
    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) use ($request, $id) {
        /**
         * @todo Find in db following $id
         */
        $project = Project::where("userId", "=", $userId)
          ->where("_id", "=", $id);
        
        /**
         * @todo 
         */
        if (!$project->exists()) {
          return response()->json([
            "data" => "The project with name is already created."
          ], 404, [], JSON_PRETTY_PRINT);
        }

        /**
         * @todo Get document.
         */
        $project = $project->first();

        /**
         * @todo Loop in @var $request and assign into query parameters to execute the document query.
         */
        foreach ($request->except('_token') as $key => $value) {
          if (in_array($key, Project::$columns)) {
            $project->$key = $value;
          }
        }

        /**
         * @todo Update document.
         */
        $project->save();
        return response()->json([
          "data" => $project,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }
}
