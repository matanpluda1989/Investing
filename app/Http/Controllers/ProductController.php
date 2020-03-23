<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /*Response json of all the Products*/
    public function getAllProducts() {
        return response()->json(Product::all());
    }

    /*Response json with full details of product by Product Id*/
    public function getProduct($productId) {
        
        //Validation for existing product id
        $product = $this->productIsExist($productId);
        if($product == null)
            return "Product ID " . $productId . " doesn't exist.";

        return response()->json($product);
    }
    
    /**
     * The function output specific field in DB by id.
     * The url contains apart the id also the field name.
     */
    public function getValue($field, $productId) {  
        
        //Validation for existing product id
        $product = $this->productIsExist($productId);
        if($product == null)
            return "Product ID " . $productId . " doesn't exist.";

        $queryRes = Product::select('ProductName', 'ProductDescription', 'ProductPrice', 'Stock')->where('ProductID', $productId)->get();
        switch($field) {
            case "name":   
                return $queryRes[0]['ProductName'];
            case "description":
                return $queryRes[0]['ProductDescription']; 
            case "price":
                return $queryRes[0]['ProductPrice'];
            case "stock":
                return $queryRes[0]['Stock'];
            default:
                return response("error in url", 404);
        }
    }

    
    /*Response list of all catalogs contain specific Product*/
    public function getCatalogs($productId) {
        
        //Validation for existing product id
        $product = $this->productIsExist($productId);
        if($product == null)
            return "Product ID " . $productId . " doesn't exist.";

        $catalogsId = DB::select('select CatalogID from productscatalogsrelation where ProductId = ?', [$productId]);
        if($catalogsId == null)
            return "The productId " . $productId . " doesn't exist in any catalog."; 
        return $catalogsId;
    }

    /*Create new Product in products table*/
    public function newProduct(Request $request) {
        $this->validation($request, "post");

        $product = Product::create([ 
            'ProductID' => $request->ProductID,
            'ProductName' => $request->ProductName,
            'ProductDescription' => $request->ProductDescription,
            'ProductPrice' => $request->ProductPrice,
            'Stock' => $request->Stock
        ]);

        return response()->json($product, 200);
    }


    /*Updating Data product by json of whole product*/ 
    public function updateProduct(Request $request, $productId) {
        
        //Validation for existing product id
        $product = $this->productIsExist($productId);
        if($product == null)
            return "Product ID " . $productId . " doesn't exist.";
            
        $this->validation($request); 
        
        $product->update([
            'ProductName' => $request->ProductName,
            'ProductDescription' => $request->ProductDescription,
            'ProductPrice' => $request->ProductPrice,
            'Stock' => $request->Stock
        ]);
        
        return response()->json($product, 200);

    } 

    /**
     * Updating specific field in DB.
     * The url contains apart the id also the field to change and the new value.
     */
    public function updateField($field, $productId, $newValue) { 
        
        //Validation for existing product id
        $product = $this->productIsExist($productId);
        if($product == null)
            return "Product ID " . $productId . " doesn't exist.";

        switch($field) {
            case "name":
                $product->update(['ProductName' => $newValue]);
                break;
            case "description":
                $product->update(['ProductDescription' => $newValue]);
                break;
            case "price":
                $product->update(['ProductPrice' => $newValue]);
                break;
            case "stock":
                $product->update(['Stock' => $newValue]);
                break;
            default:
                return response("error in url", 404);
        }
        return $this->getProduct($productId);
    } 

    /*Delete the product from the db (deleting from the shopping cart)*/
    public function deleteProduct($productId) {
        
        //Validation for existing product id
        $product = $this->productIsExist($productId);
        if($product == null)
            return "Product ID " . $productId . " doesn't exist.";

        Product::findOrFail($productId)->delete();
        return response('Product ' . $productId . ' was deletetd from the stock.');
    }

    /**
     * Validation to invalid inserting or updating data.
     * The validaions is changed by the http request.
     */
    private function validation(Request $request, $httpReq = "put") {    
        if($httpReq == "post") {
            $this->validate($request, [
                'ProductID' => 'required|unique:products',
                'ProductName' => 'required|unique:products', 
                'ProductDescription' => 'required|unique:products',
                'ProductPrice' => 'required', 
                'Stock' =>'required'
            ]);
        }
        else {
            $this->validate($request, [
                'ProductName' => 'required', 
                'ProductDescription' => 'required', 
                'ProductPrice' => 'required', 
                'Stock' =>'required'
            ]);
        }
        
    }

    /**
     * The function checks if the given product id exist in DB,
     * and return the product data or null if it doesn't exist.
     */
    public function productIsExist($productId) {
        $product = Product::find($productId);
        if ($product == null)
            return null; 
        return $product;
    }
}


