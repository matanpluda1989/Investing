<?php

namespace App\Http\Controllers;

use App\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    
    /*Response json of all the Catalogs*/
    public function getAllCatalogs() {
        return response()->json(Catalog::all());
    }

    /*Response json with full details of Catalog by Catalog Id*/
    public function getCatalog($catalogId) { 
        
        //Validation for existing catalog id
        $catalog = $this->catalogIsExist($catalogId);
        if($catalog == null)
            return "Catalog ID " . $catalogId . " doesn't exist.";

        return response()->json($catalog);
    }
    
    /**
     * The function output specific field in DB by id.
     * The url contains apart the id also the field name.
     */
    public function getValue($field, $catalogId) { 

        //Validation for existing catalog id
        $catalog = $this->catalogIsExist($catalogId);
        if($catalog == null)
            return "Catalog ID " . $catalogId . " doesn't exist.";

        $queryRes = Catalog::select('CatalogName', 'CatalogDescription')->where('CatalogId', $catalogId)->get();    
        switch($field) {
            case "name":
                return $queryRes[0]['CatalogName'];   
            case "description":
                return $queryRes[0]['CatalogDescription']; 
            default:
                return response("error in url", 404);
        }
    }

    /*Response list of all Products belong given Catalog*/
    public function getProducts($catalogId) {  

        //Validation for existing catalog id
        $catalog = $this->catalogIsExist($catalogId);
        if($catalog == null)
            return "Catalog ID " . $catalogId . " doesn't exist.";

        $productsId = DB::select('select ProductId from productscatalogsrelation where CatalogID = ?', [$catalogId]);
        if($productsId == null)
            return "The catalogId " . $catalogId . " doesn't contain any product."; 
        return $productsId;
    }
    
    /*Create new Catalog in catalogs table*/
    public function newCatalog(Request $request) { 

        $this->validation($request, "post");

        $catalog = Catalog::create([ 
                                    'CatalogID' => $request->CatalogID,
                                    'CatalogName' => $request->CatalogName,
                                    'CatalogDescription' => $request->CatalogDescription
                                ]); 

        return response()->json($catalog, 201);
    }

    /*Updating Data Catalog by json of whole Catalog*/ 
    public function updateCatalog(Request $request, $catalogId) { 
        
        //Validation for existing catalog id
        $catalog = $this->catalogIsExist($catalogId);
        if($catalog == null)
            return "Catalog ID " . $catalogId . " doesn't exist.";

        $this->validation($request); 
        
        $catalog = Catalog::findOrFail($catalogId);
        $catalog->update([
            'CatalogName' => $request->CatalogName,
            'CatalogDescription' => $request->CatalogDescription
        ]);
       
        return response()->json($catalog, 200);
    } 


    /**
     * Updating specific field in DB.
     * The url contains apart the id also the field to change and the new value.
     */
    public function updateField($field, $catalogId, $newValue) { 
        
        //Validation for existing catalog id
        $catalog = $this->catalogIsExist($catalogId);
        if($catalog == null)
            return "Catalog ID " . $catalogId . " doesn't exist.";

        $catalog = Catalog::findOrFail($catalogId);
        switch($field) {
            case "name":
                $catalog->update(['CatalogName' => $newValue]);
                break;
            case "description":
                $catalog->update(['CatalogDescription' => $newValue]);
                break;
            default:
                return response("error in url", 404);
        }
        return $this->getCatalog($catalogId);
    } 

    /**
     * Insert new record to relation table -
     * Additional of product to specific catalog
     */
    public function updateProdtucsList($productId, $catalogId) { 
        
        //Validation for existing catalog id
        $catalog = $this->catalogIsExist($catalogId);
        if($catalog == null)
            return "Catalog ID " . $catalogId . " doesn't exist.";

        //Validation for existing product id
        $product = (new ProductController())->productIsExist($productId);
        if($product == null)
            return "Product ID " . $productId . " doesn't exist.";

        DB::insert('INSERT INTO productscatalogsrelation VALUES (?,?)', [$productId, $catalogId]);
        return response('Product ' . $productId . ' was added to catalog ' . $catalogId);
    } 

    /*Delete the Catalog from the db (deleting from the shopping cart)*/
    public function deleteCatalog($catalogId) { 
        
        //Validation for existing catalog id
        $catalog = $this->catalogIsExist($catalogId);
        if($catalog == null)
            return "Catalog ID " . $catalogId . " doesn't exist.";

        //First needed delete all the products from catalog
        DB::delete('DELETE FROM productscatalogsrelation WHERE CatalogId = (?)', [$catalogId]);
        
        Catalog::findOrFail($catalogId)->delete();
        return response('Catalog ' . $catalogId . ' was deletetd from the Shopping Cart.');
    }

    /**
     * Validation to invalid inserting or updating data.
     * The validaions is changed by the http request.
     */
    private function validation(Request $request, $httpReq = "put") {    
        if($httpReq == "post") {
            $this->validate($request, [   
                'CatalogID' => 'required|unique:catalogs',
                'CatalogName' => 'required|unique:catalogs', 
                'CatalogDescription' => 'required|unique:catalogs'
            ]);
        }
        else {
            $this->validate($request, [
                'CatalogName' => 'required', 
                'CatalogDescription' => 'required', 
            ]);
        }     
    }

    /**
     * The function checks if the given catalog id exist in DB,
     * and return the catalog data or null if it doesn't exist.
     */
    private function catalogIsExist($catalogId) {
        $catalog = Catalog::find($catalogId);
        if ($catalog == null)
            return null; 
        return $catalog;
    }

}


    


    