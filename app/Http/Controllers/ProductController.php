<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenIdService;
use App\Models\Product;
use App\Models\User;
use Throwable;

class ProductController extends Controller
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
   * @todo The function to create product.
   */
  public function createProduct(Request $request)
  {
    /** @var Product $product */
    $dto = array(
      "name" => $request->input("name"),
      "gallery" => $request->input("gallery"),
      "description" => $request->input("description"),
    );

    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) use ($dto, $request) {
        /**
         * @todo Assign userId for dto to create product.
         */
        $dto["userId"] = $userId;

        /**
         * @todo Check if the product is already exists with this name.
         */
        $exist = Product::where("userId", "=", $userId)
          ->where("name", "=", $dto["name"])->exists();
        if ($exist) {
          return response()->json([
            "data" => "The product with name is already created."
          ], 409, [], JSON_PRETTY_PRINT);
        }

        /**
         * @todo Now create new project with dto.
         */
        $product = new Product;
        $product->name = $dto["name"];
        $product->gallery = $dto["gallery"];
        $product->description = $dto["description"];
        $product->userId = $dto["userId"];
        $product->view = 0;
        $product->reactions = [];

        /**
         * @todo Assign tags to product if exist tags in request
         */
        if ($request->input("tags")) {
          $product->tags = $request->input("tags");
        }

        /**
         * @todo Assign projectId to product if exist projectId in request
         */
        if ($request->input("projectId")) {
          $product->projectId = $request->input("projectId");
        }

        $product->save();

        return response()->json([
          "data" => $product,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function to get detail of product following id.
   */
  public function getProduct(Request $request, $id)
  {
    try {
      /**
       * @todo Find in db following $id
       */
      $product = Product::where("_id", "=", $id);
      
      /**
       * @todo Return error when not found with the id.
       */
      if ($product->exists() == false) {
        return response()->json([
          "data" => "Not Found"
        ], 401, [], JSON_PRETTY_PRINT);
      }

      $product = $product->first();

      /**
       * @todo Assign user info to product
       */
      $user = User::where("sub", "=", $product["userId"])->first();
      if ($user) {
        $product["owner"] = array(
          "name" => $user["name"],
          "picture" => $user["picture"],
          "username" => $user["username"],
        );
      }

      return response()->json([
        "data" => $product,
      ], 200, [], JSON_PRETTY_PRINT);
    } catch (Throwable $e) {
      return response()->json([
        "data" => "Bad request"
      ], 400, [], JSON_PRETTY_PRINT);
    }
  }

  /**
   * @todo The function to get detail of product following id.
   */
  public function getProductByOwner(Request $request, $id)
  {
    try {
      return $this->openIdService->openIdIntrospect(
        $request,
        function ($userId) use ($request, $id) {
          /**
           * @todo Find in db following $id
           */
          $product = Product::where("userId", "=", $userId)
            ->where("_id", "=", $id);

          /**
           * @todo Throw exception when dont found any projects with the Id.
           */
          if (!$product->exists()) {
            return response()->json([
              "data" => "The product with name is already created."
            ], 404, [], JSON_PRETTY_PRINT);
          }

          /**
           * @todo Get document.
           */
          $product = $product->first();

          /**
           * @todo Update document.
           */
          $product->save();
          return response()->json([
            "data" => $product,
          ], 200, [], JSON_PRETTY_PRINT);
        }
      );
    } catch (Throwable $e) {
      return response()->json([
        "data" => "Bad request"
      ], 400, [], JSON_PRETTY_PRINT);
    }
  }

  /**
   * @todo The function to delete product with the id.
   */
  public function deleteProduct(Request $request, $id)
  {
    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) use ($id) {
        /**
         * @todo Find in db following $id
         */
        $product = Product::where("userId", "=", $userId)
          ->where("_id", "=", $id);

        /**
         * @todo Throw exception when dont found any projects with the Id.
         */
        if (!$product->exists()) {
          return response()->json([
            "data" => "Not found product"
          ], 404, [], JSON_PRETTY_PRINT);
        }

        /**
         * @todo Delete document.
         */
        $product->delete();
        return response()->json([], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /**
   * @todo The function to get product list of a user
   */
  public function getProducts(Request $request)
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
    $products = Product::where($query)->get();

    /**
     * @todo Assign user info to product
     */
    foreach ($products as $product) {
      $user = User::where("sub", "=", $product["userId"])->first();
      if ($user) {
        $product["owner"] = array(
          "name" => $user["name"],
          "picture" => $user["picture"],
          "username" => $user["username"],
        );
      }
    }

    return response()->json([
      "data" => $products,
    ], 200, [], JSON_PRETTY_PRINT);
  }

 /**
   * @todo The function to get product list of a user
   */
  public function getLikedProductsByUser(Request $request)
  {
    
    $products = Product::where("reactions", "all", [$request->input("userId")])->get();
    /**
     * @todo Assign user info to product
     */
    foreach ($products as $product) {
      $user = User::where("sub", "=", $product["userId"])->first();
      if ($user) {
        $product["owner"] = array(
          "name" => $user["name"],
          "picture" => $user["picture"],
          "username" => $user["username"],
        );
      }
    }

    return response()->json([
      "data" => $products,
    ], 200, [], JSON_PRETTY_PRINT);
  }


  /**
   * @todo The function to edit product of user
   */
  public function editProduct(Request $request, $id)
  {
    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) use ($request, $id) {
        /**
         * @todo Find in db following $id
         */
        $product = Product::where("userId", "=", $userId)
          ->where("_id", "=", $id);

        /**
         * @todo Throw exception when dont found any projects with the Id.
         */
        if (!$product->exists()) {
          return response()->json([
            "data" => "Not found product with following name"
          ], 404, [], JSON_PRETTY_PRINT);
        }

        /**
         * @todo Get document.
         */
        $product = $product->first();

        /**
         * @todo Loop in @var $request and assign into query parameters to execute the document query.
         */
        foreach ($request->except('_token') as $key => $value) {
          if (in_array($key, Product::$columns)) {
            $product->$key = $value;
          }
        }

        /**
         * @todo Update document.
         */
        $product->save();
        return response()->json([
          "data" => $product,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }

  /** @todo The function to react to the product. */
  public function react(Request $request, $id)
  {
    return $this->openIdService->openIdIntrospect(
      $request,
      function ($userId) use ($id) {
        /**
         * @todo Find in db following $id
         */
        $product = Product::where("_id", "=", $id);

        /**
         * @todo Throw exception when dont found any projects with the Id.
         */
        if (!$product->exists()) {
          return response()->json([
            "data" => "Not found product"
          ], 404, [], JSON_PRETTY_PRINT);
        }

        /** @todo Get entity. */
        $product = $product->first();

        /**
         * @todo Handle to like or unline product.
         */
        $reactions = $product->reactions;
        $index = array_search($userId, $reactions);
        if (count($reactions) && ($index || $reactions[0] == $userId)) {
          array_splice($reactions, $index, 1);
        } else {
          array_push($reactions, $userId);
        }

        /**
         * @todo Update schema.
         */
        $product->reactions = $reactions;

        /**
         * @todo Update document.
         */
        $product->save();
        return response()->json([
          "data" => $product,
        ], 200, [], JSON_PRETTY_PRINT);
      }
    );
  }
}
