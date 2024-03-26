<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\CommonController; 
Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);
 
    $user = User::where('email', $request->email)->first();
    if (! $user || ! Hash::check($request->password, $user->password)) {
        // throw ValidationException::withMessages([
        //     'email' => ['The provided credentials are incorrect.'],
        // ]);
       return response()->json(['message'=>'credintials mismatch'],500);
    }
     $user->admin ? $role='admin' : $role= 'user';
    return response()->json(['role'=>$role,'token'=>$user->createToken($request->device_name)->plainTextToken]);
});

Route::middleware('auth:sanctum')->group(function () {
  Route::post('newCloth',[CommonController::class,'newCloth']); 
  Route::get('searchCloth/{text}',[CommonController::class,'searchCloth']);
  Route::post('logout',function(Request $request){
    auth()->user()->tokens()->delete();
    return response()->json(['you logged out'],200);
  }); 
  Route::get('adminOrders',[CommonController::class,'adminOrders']);
  Route::post('newOrder',[CommonController::class,'newOrder']);
  Route::post('editOrderStatus',[CommonController::class,'editOrderStatus']);
  Route::get('userOrders',[CommonController::class,'userOrders']);

});

Route::post('register',[CommonController::class,'register']);
Route::post('register',[CommonController::class,'register']);
Route::get('login',function(){
    return response()->json(['message'=>'unauthorized'],403);
})->name('login');