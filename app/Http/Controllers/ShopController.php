<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function randomAbjad($length)
    {
        $randomAlphabet =strtolower('client'.'-'.Str::random($length));

        $cekKey = Shop::where('key', $randomAlphabet)->first();
        if($cekKey == null){
            return $randomAlphabet;
        }else{
            $this->randomAbjad(21);
        }
    }
    public function index(Request $request)
    {
        $data = Shop::all();
        return response()->json(['code' => 200,'message' => 'success get data','data'=> $data],200 );

    }

    public function show($id)
    {
        return Shop::findOrFail($id);
    }

    public function store(Request $request)
    {
        // validation
        $rules = [
            'name' => 'required',
        ];
        $text = [
            'name.required' => 'Nama Tidak Boleh Kosong',
        ];
        
        $validasi = Validator::make($request->all(), $rules, $text);

        if($validasi->fails()){
            return response()->json(['code' => 422,'message' => $validasi->errors()->first()],422);

        }

        // generate key
        $key = $this->randomAbjad(21);
        $shops = Shop::create([
            "name" => $request->name,
            "key"=>$key,
        ]);
        return response()->json(['code' => 201,'message' => 'success created','data'=> $shops],201 );
    }

    public function update(Request $request, $id)
    {
        // input key
        $apiKey = $request->input('key');

        if (!$this->isValidApiKey($apiKey)) {
            return response()->json(['code' => 400,'error' => 'Unauthorized Access'], 400);
        }
        // validation
        $rules = [
            'name' => 'required',
        ];
        $text = [
            'name.required' => 'Nama Tidak Boleh Kosong',
        ];
        
        $validasi = Validator::make($request->all(), $rules, $text);

        if($validasi->fails()){
            return response()->json(['code' => 422,'message' => $validasi->errors()->first()],422);

        }

        $shop = Shop::find($id);
        
        if (!$shop) {
            return response()->json(['code' => 400,'error' => 'Not Found Data'], 400);
        }

        $shop->name = $request->name;
        $shop->save();

        return response()->json(['code' => 200,'message' => 'success updated','data'=> $shop],200 );
    }

    public function destroy(Request $request, $id)
    {

        $shop = Shop::find($id);
        if (!$shop) {
            return response()->json(['code' => 400,'error' => 'Not Found Data'], 400);
        }
        $shop->delete();

        return response()->json(['code' => 200,'message' => 'success deleted'],200 );
    }
}
