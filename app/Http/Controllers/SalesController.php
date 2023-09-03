<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Sales;
use App\Models\Shop;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SalesController extends Controller
{
    private function isValidApiKey($apiKey)
    {
        $key = Shop::where('key', $apiKey)->first();
        if($key != null){
            return $apiKey === $key->key;
        }
    }

    public function index(Request $request)
    {
        // input key
        $apiKey = $request->input('key');

        if (!$this->isValidApiKey($apiKey)) {
            return response()->json(['code' => 400,'error' => 'Unauthorized Access'], 400);
        }

        $data = Sales::with(['carts','carts.product','carts.product.variants'])->get();

        return response()->json(['code' => 200,'message' => 'success get data','data'=> $data],200 );

    }
    public function store(Request $request)
    {
        // input key
        $apiKey = $request->input('key');

        if (!$this->isValidApiKey($apiKey)) {
            return response()->json(['code' => 400,'error' => 'Unauthorized Access'], 400);
        }

        // validation
        $rules = [
            'total' => 'required|numeric',
            'created' => 'required',
            'cart' => 'required',
            'payment_method' => 'required',
        ];
        $text = [
            'total.required' => 'Total Tidak Boleh Kosong',
            'total.numeric' => 'Total Harus Angka',
            'created.required' => 'Dibuat Tidak Boleh Kosong',
            'cart.required' => 'Keranjang Tidak Boleh Kosong',
            'payment_method.required' => 'Metode Pembayaran Tidak Boleh Kosong',
        ];
        
        $validasi = Validator::make($request->all(), $rules, $text);

        if($validasi->fails()){
            return response()->json(['code' => 422,'message' => $validasi->errors()->first()],422);
        }

        // generate id
        $id = "S" . '-' . rand(1, 1000000) . '-' . rand(1, 1000000);

        // date format
        $dateTime = Carbon::parse($request->created); // Gantilah ini dengan tanggal dan waktu yang sesuai
        $formattedDate = $dateTime->format('j F Y H:i:s');

        $sales = new Sales([
            'id' => $id,
            'total' => $request->total,
            'created' => $formattedDate,
            'payment_method' => $request->payment_method,
        ]);

        $sales->save();
        
        $cart=[];

        foreach ($request->cart as $cartData) {
            // cek product
            $product = Product::find($cartData['product_id']);
            if(!$product){
                return response()->json(['code' => 400,'error' => 'Not Found Data Product'], 400);
            }
            $carts = new Cart([
                'product_id' => $cartData['product_id'],
                'sales_id' => $id,
            ]);
            $carts->save();


            if (isset($cartData['variants'])) {
                $req = [
                    "product_id" => $carts->product_id,
                    "variants" => $cartData['variants'],
                ];
                array_push($cart, $req);
            }else{
                $req = [
                    "product_id" => $carts->product_id,
                ];
                array_push($cart, $req);

            }
            if (isset($cartData['variants'])) {
                foreach ($cartData['variants'] as $variantData) {
                    $variant = new Variant([
                        'name' => $variantData['variants_name'],
                        'additional_price' => $variantData['price'],
                        'product_id' => $cartData['product_id'],
                    ]);
                    $variant->save();
                }
                
            }
        }

        $res = [
            "id" => $id,
            "cart" => $cart,
            "total"=> $sales->total,
            "created"=> $sales->created,
            "payment_method"=> $sales->payment_method,
        ];
        return response()->json(['code' => 201,'message' => 'Success Created','data'=>$res], 201);
    }

}
