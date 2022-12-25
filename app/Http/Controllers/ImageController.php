<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
  public function uploadImage(Request $request)
  {
    $this->validate($request, [
      'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
    ]);


    $file= $request->file('image');
    $filename= date('YmdHi').$file->getClientOriginalName();
    $file-> move(public_path('images'), $filename);

    return response()->json([
      "data" => "https://afternoon-gorge-11599.herokuapp.com/images/".$filename,
    ], 200, [], JSON_PRETTY_PRINT);
  }
}
