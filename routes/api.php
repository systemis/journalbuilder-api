<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::get('/public', function () {
  return response()->json([
    'message' => 'Hello from a public endpoint! You don\'t need to be authenticated to see this.',
    'authorized' => Auth::check(),
    'user' => Auth::check() ? json_decode(json_encode((array) Auth::user(), JSON_THROW_ON_ERROR), true) : null,
  ], 200, [], JSON_PRETTY_PRINT);
})->middleware(['auth0.authorize.optional']);

/**
 * @todo Group all api related user information.
 */
Route::controller(UserController::class)
  ->prefix(("user"))
  ->middleware(['auth0.authorize.optional'])
  ->group(function () {
    /**
     * @todo Get user profile
     * @var User $user
     * */
    Route::get("/profile", "getProfile");

    /**
     * @todo Update user profile
     * @var User $user
     */
    Route::patch("/profile", "updateProfile");
  });

/**
 * @todo Group all api realeated to authentication.
 */
Route::controller(AuthController::class)
  ->prefix(("auth"))
  ->middleware(['auth0.authorize.optional'])
  ->group(function () {
    /**
     * @todo Login by email & password
     * @var String $accessToken
     * */
    Route::post("/login", "login");

    /**
     * @todo Register new user with credential.
     */
    Route::post("/register", "register");

    /**
     * @todo The endpoint to request permission for update apis.
     */
    Route::post("/request-permission", "requestPermission");

    /**
     * @todo The endpoint to update user password.
     */
    Route::patch("/password", "changePassword");

    /**
     * @todo The endpoint to get role of user.
     */
    Route::get("/roles", "getRoles");
  });

/**
 * @todo Group all api related project.
 */
Route::controller(ProjectController::class)
  ->prefix(("project"))
  ->middleware(['auth0.authorize.optional'])
  ->group(function () {
    /**
     * @todo Create new project
     * @var Project $project
     * */
    Route::post("/", "createProject");

    /**
     * @todo Edit user project
     * @var EditDTO
     */
    Route::patch("/{id}", "editProject");

    /**
     * @todo Delete user project
     * @var String $id
     */
    Route::delete("/{id}", "deleteProject");
  });

/**
 * @todo Group all api related project.
 */
Route::controller(ProductController::class)
  ->prefix(("product"))
  ->middleware(['auth0.authorize.optional'])
  ->group(function () {
    /**
     * @todo Create new product
     * @var Product $product
     * */
    Route::post("/", "createProduct");

    /**
     * @todo Edit user product
     * @var EditDto
     */
    Route::patch("/{id}", "editProduct");

    /**
     * @todo Get product detail
     * @var String id
     */
    Route::get("/detail", "getProduct");

    /**
     * @todo Get list product
     * @var Params query
     */
    Route::get("/", "getProducts");

    /**
     * @todo Delete user product
     * @var String $id
     */
    Route::delete("/{id}", "deleteProduct");
  });

/**
 * @todo Group all api related admin tags.
 */
Route::controller(TagController::class)
  ->prefix(("admin"))
  ->middleware(['auth0.authorize.optional'])
  ->group(function () {
    /**
     * @todo Create new tag
     * @var Tag $tag
     * */
    Route::post("/tag", "createTag");

    /**
     * @todo Edit tag.
     * @var EditTagDto
     */
    Route::patch("/tags/{id}", "editTag");

    /**
     * @todo Delete tag
     * @var String $id
     */
    Route::delete("/{id}", "deleteTag");
  });

/**
 * @todo Get tags
 */
Route::get("/tags", [TagController::class, "getTags"]);


/**
 * @todo Delete user project
 * @var String $id
 */
Route::get("/project/{id}", [ProjectController::class, "getProject"]);

/**
 * @todo Find projects
 * @var Request request
 */
Route::get("/projects", [ProjectController::class, "getProjects"]);


/**
 * @todo Upload a image
 * @var File file
 */
Route::post("/image", [ImageController::class, "uploadImage"]);