<?php

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    return view('welcome');
});

Route::get('/blogs/{id}', function($id){
    $cachedBlog = Redis::get('blog_' . $id);


  if(isset($cachedBlog)) {
      $blog = json_decode($cachedBlog, FALSE);

      return response()->json([
          'status_code' => 201,
          'message' => 'Fetched from redis',
          'data' => $blog,
      ]);
  }else {
      $blog = Blog::find($id);
      Redis::set('blog_' . $id, $blog);

      return response()->json([
          'status_code' => 201,
          'message' => 'Fetched from database',
          'data' => $blog,
      ]);
  }
});
// ### Update ####
Route::post('/blogs/update/{id}', function(Request $request,$id){

    $update = Blog::findOrFail($id)->update($request->all());

  if($update) {

      // Delete blog_$id from Redis
      Redis::del('blog_' . $id);

      $blog = Blog::find($id);
      // Set a new key with the blog id
      Redis::set('blog_' . $id, $blog);

      return response()->json([
          'status_code' => 201,
          'message' => 'User updated',
          'data' => $blog,
      ]);
  }
});

// #### Delete #####

Route::delete('/blogs/delete/{id}', function($id){
    Blog::findOrFail($id)->delete();
    Redis::del('blog_' . $id);

    return response()->json([
        'status_code' => 201,
        'message' => 'Blog deleted'
    ]);
});
