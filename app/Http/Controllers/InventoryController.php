<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
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
        $data = Inventory::all();
        return response()->json(['code' => 200,'message' => 'success get data','data'=> $data],200 );

    }

    public function show($id)
    {
        return Inventory::findOrFail($id);
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
            'name' => 'required',
            'unit' => 'required',
            'amount' => 'required|numeric',
            'price' => 'required|numeric',
        ];
        $text = [
            'name.required' => 'Nama Tidak Boleh Kosong',
            'unit.required' => 'Unit Tidak Boleh Kosong',
            'amount.required' => 'Jumlah Tidak Boleh Kosong',
            'amount.numeric' => 'Jumlah Harus Angka',
            'price.required' => 'Harga Tidak Boleh Kosong',
            'price.numeric' => 'Harga Harus Angka',
        ];
        
        $validasi = Validator::make($request->all(), $rules, $text);

        if($validasi->fails()){
            return response()->json(['code' => 422,'message' => $validasi->errors()->first()],422);

        }

        $inventory = Inventory::create([
            "name" => $request->name,
            "amount"=>$request->amount,
            "price" => $request->price,
            "unit" => $request->unit,
        ]);
        return response()->json(['code' => 201,'message' => 'success created','data'=> $inventory],201 );
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
            'unit' => 'required',
            'amount' => 'required|numeric',
            'price' => 'required|numeric',
        ];
        $text = [
            'name.required' => 'Nama Tidak Boleh Kosong',
            'unit.required' => 'Unit Tidak Boleh Kosong',
            'amount.required' => 'Jumlah Tidak Boleh Kosong',
            'amount.numeric' => 'Jumlah Harus Angka',
            'price.required' => 'Harga Tidak Boleh Kosong',
            'price.numeric' => 'Harga Harus Angka',
        ];
        
        $validasi = Validator::make($request->all(), $rules, $text);

        if($validasi->fails()){
            return response()->json(['code' => 422,'message' => $validasi->errors()->first()],422);

        }

        $inventory = Inventory::find($id);
        
        if (!$inventory) {
            return response()->json(['code' => 400,'error' => 'Not Found Data'], 400);
        }

        $inventory->name = $request->name;
        $inventory->unit = $request->unit;
        $inventory->amount = $request->amount;
        $inventory->price = $request->price;
        $inventory->save();

        return response()->json(['code' => 200,'message' => 'success updated','data'=> $inventory],200 );
    }

    public function destroy(Request $request, $id)
    {
        // input key
        $apiKey = $request->input('key');

        if (!$this->isValidApiKey($apiKey)) {
            return response()->json(['code' => 400,'error' => 'Unauthorized Access'], 400);
        }

        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['code' => 400,'error' => 'Not Found Data'], 400);
        }
        $inventory->delete();

        return response()->json(['code' => 200,'message' => 'success deleted'],200 );
    }
}
