<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // load all product from  api
    public function syncProduct(Request $request)
    {
        // guzzle http client post request
        $client = new Client();

        try {
            $response = $client->request('POST', env('DIGIFLAZZ_URL') . '/price-list', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    "cmd" => $request->type,
                    "username" => env('DIGIFLAZZ_USERNAME'),
                    "sign" => getSignature('pricelist')
                ])
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            if (is_array($responseJSON['data']) && count($responseJSON['data']) > 0) {
                // insert or update product
                foreach ($responseJSON['data'] as $key => $items) {
                    Product::updateOrCreate(['product_sku' => $items['buyer_sku_code']], [
                        'product_original_name' => $items['product_name'],
                        'product_name' => $request->type == 'prepaid' ? extractNumberFromString($items['product_name']) : $items['product_name'],
                        'product_slug' => str_replace(' ', '-', $items['product_name']),
                        'product_description' => $items['desc'],
                        'product_original_price' => isset($items['price']) ? $items['price'] : 0,
                        'product_image' => null,
                        'product_status' => $items['seller_product_status'],
                        'product_stock' => isset($items['stock']) ? $items['stock'] : 0,
                        'product_category' => $items['category'],
                        'product_brand' => $items['brand'],
                        'product_sku' => $items['buyer_sku_code'],
                        'vendor_admin_fee' => isset($items['admin']) ? $items['admin'] : 0,
                        'admin_fee' => 0,
                        'commission' => isset($items['commission']) ? $items['commission'] : 0,
                        'product_type' => $request->type,
                    ]);
                }

                // insert or update category
                foreach ($responseJSON['data'] as $key => $items) {
                    ProductCategory::updateOrCreate(['category_name' => $items['category']], [
                        'category_name' => $items['category'],
                        'category_slug' => str_replace(' ', '-', $items['category']),
                        'category_image' => null,
                    ]);
                }

                return response()->json([
                    'message' => 'Sync Product Success',
                    'data' => [],
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error',
                'data' => $th->getMessage(),
            ], 400);
        }
    }

    // get list product
    public function getListProduct(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $category = $request->category;
        $product_type = $request->product_type;
        $product =  Product::query();
        if ($search) {
            $product->where(function ($query) use ($search) {
                $query->where('product_original_name', 'like', "%$search%");
                $query->orWhere('product_name', 'like', "%$search%");
                $query->orWhere('product_slug', 'like', "%$search%");
                $query->orWhere('product_description', 'like', "%$search%");
                $query->orWhere('product_category', 'like', "%$search%");
                $query->orWhere('product_brand', 'like', "%$search%");
                $query->orWhere('product_sku', 'like', "%$search%");
            });
        }

        if ($status) {
            $product->where('product_status', $status);
        }


        if ($category) {
            $product->where('product_category', $category);
        }

        if ($product_type) {
            $product->where('product_type', $product_type);
        }


        $products = $product->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $products,
            'message' => 'List Product'
        ]);
    }

    // load detail product
    public function getProductDetail($product_id)
    {
        $product = Product::find($product_id);

        return response()->json([
            'status' => 'success',
            'data' => $product,
            'message' => 'Detail Product'
        ]);
    }

    // update product
    public function updateProduct(Request $request, $product_id)
    {
        $product = Product::find($product_id);

        $product->update([
            'product_name' => $request->product_name,
            'product_price' => $request->product_price,
            'admin_fee' => $request->admin_fee,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $product,
            'message' => 'Success Update Product'
        ]);
    }

    // update product
    public function updateStatusProduct(Request $request, $product_id)
    {
        $product = Product::find($product_id);

        $product->update([
            'product_status' => $request->product_status,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $product,
            'message' => 'Success Update Status Product'
        ]);
    }
}
