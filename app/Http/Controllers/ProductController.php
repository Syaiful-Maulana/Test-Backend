<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    private function isValidApiKey($apiKey)
    {
        return $apiKey === 'client-adfasdf123sdfbadsftwkljlkjl';
    }

    public function index(Request $request)
    {
        // input key
        $apiKey = $request->input('key');

        if (!$this->isValidApiKey($apiKey)) {
            return response()->json(['code' => 400,'error' => 'Unauthorized Access'], 400);
        }

        $data = Product::with(['variants'])->get();
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
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'variants' => 'required',
        ];
        $text = [
            'name.required' => 'Nama Tidak Boleh Kosong',
            'description.required' => 'Deskripsi Tidak Boleh Kosong',
            'price.required' => 'Harga Tidak Boleh Kosong',
            'price.numeric' => 'Harga Harus Angka',
            'variants.required' => 'Varian Tidak Boleh Kosong',
        ];
        
        $validasi = Validator::make($request->all(), $rules, $text);

        if($validasi->fails()){
            return response()->json(['code' => 422,'message' => $validasi->errors()->first()],422);

        }

        $product = new Product([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);

        $product->save();
        $variants = [];
        foreach ($request->variants as $variantData) {
            $variant = new Variant([
                'name' => $variantData['name'],
                'additional_price' => $variantData['additional_price'],
            ]);
            $req = [
                "name" => $variant->name,
                "additional_price" => $variant->additional_price,
            ];

            $product->variants()->save($variant);
            array_push($variants, $req);
        }

        $response = [
            "name" => $product->name,
            "description" => $product->description,
            "price" => $product->price,
            "variants" => $variants,
        ];
        return response()->json(['code' => 201,'message' => 'success created','data'=> $response],201 );
    }

    public function show(Request $request, Product $product)
    {
        // input key
        $apiKey = $request->input('key');

        if (!$this->isValidApiKey($apiKey)) {
            return response()->json(['code' => 400,'error' => 'Unauthorized Access'], 400);
        }

        return $product->load('variants');
    }

    public function update(Request $request, Product $product)
    {
        // input key
        $apiKey = $request->input('key');

        if (!$this->isValidApiKey($apiKey)) {
            return response()->json(['code' => 400,'error' => 'Unauthorized Access'], 400);
        }
        $products = Product::find($product->id);
        if (!$products) {
            return response()->json(['code' => 400,'error' => 'Not Found Data'], 400);
        }

        $products->name = $request->name;
        $products->description = $request->description;
        $products->price = $request->price;

        $products->save();

        // Update variants

        foreach ($request->variants as $variantData) {
            $variant = $product->variants->firstWhere('id', $variantData['id']);

            if (!$variant) {
                continue;
            }

            $variant->update([
                'name' => $variantData['name'],
                'additional_price' => $variantData['additional_price'],
            ]);
        }
        return response()->json(['code' => 200,'message' => 'success updated','data'=> $product],200 );
    }

    public function destroy(Product $product, Request $request)
    {
        // input key
        $apiKey = $request->input('key');

        if (!$this->isValidApiKey($apiKey)) {
            return response()->json(['code' => 400,'error' => 'Unauthorized Access'], 400);
        }

        $products = Product::find($product->id);
        if (!$products) {
            return response()->json(['code' => 400,'error' => 'Not Found Data'], 400);
        }

        $product->delete();
        $variant = Variant::where('product_id', $product->id)->get();
        foreach ($variant as $variantData) {
            $variantData->delete();
        }

        return response()->json(['code' => 200,'message' => 'success deleted'],200 );
    }
}
