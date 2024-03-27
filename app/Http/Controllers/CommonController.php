<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Validator;
use Hash;
use Storage;
use App\Models\Cloth;
use App\Models\Bank;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\Rule;

class CommonController extends Controller
{
    //
    public function register(Request $request)
    {
      $validator = validator::make($request->all(),[
        'name'=>'required|string|between:2,100',
        'email'=>'required|string|max:100|email|unique:users',
        'password'=>'required|string|confirmed|min:6',
        'card_number'=>'required|integer|exists:banks,card_number|min:6',
        'account_name'=>'required|string|exists:banks,account_name|min:6',
        'expired_date'=>'required|date',
        'cvv'=>'required|integer|min:6',

      ]);
      if($validator->fails())
      {
        return response()->json([
            'message'=>$validator->errors()->first()
        ],401);
      }
      $bank_id=  $this->checkForBank($validator->validated());
      if($bank_id){
          User::create(['name'=>$validator->validated()['name']
          ,'email'=>$validator->validated()['email']
          ,'bank_id'=>$bank_id,
          'password'=>Hash::make($request->password)]);
          return response()->json([
            'message'=>'Registation complete'
        ],200);
      }   
      return response()->json([
        'message'=>'unknown bank account'
    ],500);
    }

    public function editProfile(Request $request)
    {
      $validator = validator::make($request->all(),[
        'name'=>'required|string|between:2,100',
        'email'=>'required|string|max:100|email|unique:users',
        'password'=>'required|string|confirmed|min:6',
        'card_number'=>'required|integer|exists:banks,card_number|min:6',
        'account_name'=>'required|string|exists:banks,account_name|min:6',
        'expired_date'=>'required|date',
        'cvv'=>'required|integer|min:6',

      ]);
      if($validator->fails())
      {
        return response()->json([
            'message'=>$validator->errors()->first()
        ],401);
      }
      $bank_id=  $this->checkForBank($validator->validated());
       if($bank_id){
          auth()->user()->update(['name'=>$validator->validated()['name']
          ,'email'=>$validator->validated()['email']
          ,'bank_id'=>$bank_id,
          'password'=>Hash::make($request->password)]);
          return response()->json([
            'message'=>'Registation complete'
        ],200);
       }   
        return response()->json([
        'message'=>'unknown bank account'
       ],500);
    }

    public function checkForBank(array $data)
    {
       return Bank::where([['account_name',$data['account_name']],['card_number',$data['card_number']],['expired_date',$data['expired_date']],['cvv',$data['cvv']]])->first()?->id;
    }

    public function newCloth(Request $request)
    {
      if(auth()->user()->admin)
      {
        $validator = validator::make($request->all(),[
          'name'=>'required|string|between:2,100',
          'color'=>'required|string|between:2,100',
          'size'=>'required|string|between:2,100',
          'price'=>'required|numeric',
          'description'=>'required|string|max:400',
          'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if($validator->fails())
        {
          return response()->json([
              'message'=>$validator->errors()->first()
          ],401);
        }
        $images= $validator->validated()['images'];
        $validatedData=$validator->validated();
         unset($validatedData['images']);
        $cloth = Cloth::create($validatedData);
        foreach($images as $image )
        {
          $imageName = time() . '.' . $image->extension(); 
          Storage::disk('public')->putFileAs('photos',$image,$imageName);
          $cloth->clothImages()->create(['image'=>$imageName]);
        }
        return response()->json([
          'message'=>'New cloth added!'
      ],200);
      } 
      return response()->json([
        'message'=>'not authorized'
    ],500);    }

    public function allCloths()
    {
     return Cloth::paginate(1);
    }

    public function searchCloth($text)
    {
     return Cloth::where('name', 'LIKE', '%' .$text. '%')->paginate(1);
    }

    public function getCloth(int $id)
    {
      return response()->json(['data'=>Cloth::find($id), 'image'=>Cloth::find($id)->clothImages()->paginate(1)],200);
    }

    public function editCloth(int $id,Request $request)
    {
      $validator = validator::make($request->all(),[
        'name'=>'required|string|between:2,100',
        'color'=>'required|string|between:2,100',
        'size'=>'required|string|between:2,100',
        'price'=>'required|decimal',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      ]);
      if($validator->fails())
      {
        return response()->json([
            'message'=>$validator->errors()->first()
        ],401);
      }
       $images= $validator->validated()['images'];
       unset($validator->validated()['images']);
       $cloth = Cloth::find($id)->create($validator->validated());
       foreach($cloth->clothImages() as $img)
       {
        unlink(public_path('phohos/'.$img));
       }
       $cloth->clothImages()->delete();
      foreach($images as $image )
      {
        $imageName = time() . '.' . $image->extension(); 
        Storage::disk('public')->putFileAs('photos',$image,$imageName);
        $cloth->clothImages()->create(['image'=>$imageName]);
      }

      return response()->json([
        'message'=>'cloth updated'
    ],200);
    }

   public function newOrder(Request $request)
   {

    $validator = validator::make($request->all(),[
      'row.*.cloth_id'=>'required|integer|exists:cloths,id',
      'row.*.quantity'=>'required|integer',
      'row.*.total'=>'required|numeric',
    ]);
    if($validator->fails())
    {
      return response()->json([
          'message'=>$validator->errors()->first()
      ],401);
    }

      $orderKey=isset(Order::max('order_key')->order_key) ? Order::max('order_key')->order_key : 1;
      foreach($validator->validated()['row'] as $row)
      {
       Order::create(array_merge($row,['user_id'=>auth()->user()->id,'order_key'=>$orderKey]));
      }
      return response()->json(['message'=> 'Order placed'],200);

   }

   public function editOrderStatus(Request $request)
   {
    if(auth()->user()->admin)
    {
      $validator = validator::make($request->all(),[
        'order_key'=>'required|integer|exists:orders,order_key',
        'status'=>['required','max:50',Rule::in([1,2,3])]
      ]);
      if($validator->fails())
      {
        return response()->json([
            'message'=>$validator->errors()->first()
        ],401);
      }
      Order::where('order_key',$validator->validated()['order_key'])->update(['status'=>$validator->validated()['status']]);
        return response()->json([
          'message'=>'Status updated!'
      ],200);
    } 
      return response()->json([
        'message'=>'Unautharized'
    ],402);
   }

   public function adminOrders()
   {
    if(auth()->user()->admin)
    {
      $orderKeys=[];
       $orders= Order::paginate(2);
      foreach($orders as $order)
      {
       if(in_array($order['order_key'],$orderKeys))
       {
        unset($orders[$order]);
       } 
        $orderKeys[]=$order['order_key'];
        $order['total']=Order::where('order_key',$order->order_key)->get()->reduce(function($init,$order){return $init+$order->total;},0);
        $order['items']=Order::where('order_key',$order->order_key)->get()->reduce(function($init,$order){return $init+$order->quantity;},0);
        unset($order['user_id']);
        unset($order['cloth_id']);
        unset($order['created_at']);
        unset($order['updated_at']);
        unset($order['quantity']);
  
      }
      return $orders;
    }
    return response()->json([
      'message'=>'Unautharized'
  ],402);
   }
  public function userOrders()
  {
    $orderKeys=[];
    $orders = auth()->user()->orders()->paginate(2);
    foreach($orders as $order)
    {
     if(in_array($order['order_key'],$orderKeys))
     {
      unset($orders[$order]);
     } 
      $orderKeys[]=$order['order_key'];
      $order['total']=Order::where('order_key',$order->order_key)->get()->reduce(function($init,$order){return $init+$order->total;},0);
      $order['items']=Order::where('order_key',$order->order_key)->get()->reduce(function($init,$order){return $init+$order->quantity;},0);
      unset($order['user_id']);
      unset($order['cloth_id']);
      unset($order['created_at']);
      unset($order['updated_at']);
      unset($order['quantity']);

    }
    return $orders;
  }

  public function singleOrder(int $orderKey)
  {
   return Order::where('order_key',$orderKey)->paginate(2);
  }

  }
