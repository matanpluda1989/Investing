<?php

namespace App\Http\Controllers;

use App\Product;
use App\Shoppingcarts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ShoppingcartsController extends Controller
{
 

    public function __construct(Request $request)
    {   
        
    }


    /**
     * The function output all products in cart $cartId. 
     */
    public function get($cartId) {

        //Validation that the cart is not empty
        $items = $this->cartAvail($cartId);
        if($items == null)
            return "Cart " . $cartId . " is empty";

        $items = Shoppingcarts::select('ProductID', 'Quantity')->where('CartId', $cartId)->get();
        //return $items;
        $res = null;
        foreach($items as $item) {
            $productName = Product::select('ProductName')->where('ProductID', $item['ProductID'])->get();
            $res = $res . $item['Quantity'] . " " . $productName[0]['ProductName'] . ", ";
        }
        $res = substr($res, 0, strlen($res) - 2);
        return "The items in cart " . $cartId .": " . $res;
    }

    /**
     * The function delete all products from cart $cartId. 
     */
    public function delete($cartId) { 
        
        //Validation that the cart is not empty
        $items = $this->cartAvail($cartId);
        if($items == null)
            return "Cart " . $cartId . " is empty";

        //Return the amounts from the cart to the stock in product table 
        $items = Shoppingcarts::select('ProductID', 'Quantity')->where('CartId', $cartId)->get();
        foreach($items as $item) {
            $productId = $item['ProductID'];
            $currentQty = Product::select('Stock')->where('ProductID', $productId)->get();
            $newQty = $currentQty[0]['Stock'] + $item['Quantity'];
            (new ProductController())->updateField("stock", $productId, $newQty);
        }

        //Delete the items from the cart
        $items = Shoppingcarts::where('CartId', $cartId)->delete();

        return "Cart " . $cartId . " is empty";
    }

    /**
     * The function add new product and quantity to cart {cartId}. 
     * input given as json - 
     *  {
     *      "name": x,
     *      "qty": y
     *  }
     * url structure - ../public/api/shoppingcart/{cartId}?name=x&qty=y
     */ 
    public function add(Request $item, $cartId) {
        $productName = $item['name'];
        $productQty = $item['qty'];

        $queryRes = Product::select('ProductID', 'ProductPrice', 'Stock')->where('ProductName', $productName)->get();
        
        if(count($queryRes) == 0) //The product doesn't exist in db
            return $productName . " doesn't exist in the stock.";

        $productID = $queryRes[0]['ProductID'];
        $priceUnit = $queryRes[0]['ProductPrice'];
        $qtyInStock = $queryRes[0]['Stock'];

        if($qtyInStock == 0)
            return "There are no quantity in stock.";

        if($productQty > $qtyInStock) { //Case the user wants to add more than the amount in the stock
            $productQty = $qtyInStock;
            echo "Only " . $productQty . " remains in the stock. <br>";
        }
        
        $query = Shoppingcarts::select('*')->
                                where('CartId', $cartId)->
                                get();

        if(count($query) == 0) { //Cart id doesn't exist
            Shoppingcarts::create(
                ['CartId' => $cartId, 'ProductID' => $productID, 'Quantity' => $productQty]
            );
        }
        else {
            $query = Shoppingcarts::select('*')->
                                where('ProductID', $productID)->
                                where('CartId', $cartId)->
                                get();

            if(count($query) == 0) { //Cart id exists, product doesn't exist      
                Shoppingcarts::create(
                    ['CartId' => $cartId, 'ProductID' => $productID, 'Quantity' => $productQty]
                );
            }
            else { //Cart id exists and product id doesn't exist
                $currentQty = $query[0]['Quantity'];
                $newQty = $currentQty + $productQty;
                Shoppingcarts::where('ProductID', $productID)->
                                where('CartId', $cartId)->
                                update(['Quantity' => $newQty]);

            } 
        }

        //Updateing the amount in product table
        (new ProductController())->updateField("stock", $productID, $qtyInStock - $productQty);

        return $productQty . " of " . $productName . " was added to cart " . $cartId . ".";     
    }

    /**
     * The function input cartId, name and quantity of item and updating
     * the quantity of product in cart {cartId}. 
     * input given as json of name and qty - 
     *  {
     *      "name: x,
     *      "qty": y
     *  }
     * url structure - ../public/api/shoppingcart/{cartId}?name=x&qty=y
     */
    public function update(Request $item, $cartId) { 

        //Validation that the cart is not empty
        $items = $this->cartAvail($cartId);
        if($items == null)
            return "Cart " . $cartId . " is empty";

        $productName = $item['name'];
        $productQty = $item['qty']; 
        
        $query = Product::select('ProductID', 'ProductPrice', 'Stock')->where('ProductName', $productName)->get();
        
        if(count($query) == 0) //The product doesn't exist in db
            return $productName . " doesn't exist in the stock.";
        
        $currentQtyProduct = $query[0]['Stock'];
        

        $query = Shoppingcarts::select('*')->
                                where('CartId', $cartId)->
                                where('ProductID', $query[0]['ProductID'])->get();   
        
        $productId = $query[0]['ProductId'];

        $currentQtyCart = $query[0]['Quantity'];

        $newQtyProduct = $currentQtyProduct + $currentQtyCart;

        if($productQty > $newQtyProduct) { //Case the user wants to add more than the amount in the stock
            $productQty = $newQtyProduct;
            echo "You can change to only " . $productQty . ".<br>";
        }    

        //Updateing the amount in product table
        (new ProductController())->updateField("stock", $productId, $newQtyProduct - $productQty);

        Shoppingcarts::where('ProductID', $productId)->
                        where('CartId', $cartId)->
                        update(['Quantity' => $productQty]);
        
        return "The quantity of " . $productName . " was changed to " . $productQty . " in cart " . $cartId; 
    }   


    /**
     * The function input the item name and delete the item from the cart.
     * input given as json of name - 
     *  {
     *      name: x
     *  }
     * url structure - ../public/api/shoppingcart/{cartId}/removeItem/{productId}
     */
    public function remove($cartId, $productId) {
        //Validation that the cart is not empty
        $items = $this->cartAvail($cartId);
        if($items == null)
            return "Cart " . $cartId . " is empty";

        //Validation that the product exists in the cart
        $query = Shoppingcarts::select('*')->
                            where('CartId', $cartId)->
                            where('ProductID', $productId)->get();    
        if(count($query) == 0)
            return "The product " . $productId . " doesn't exist in cart " . $cartId . ".";

        //Updateing the amount in product table
        $qtyCart = $query[0]["Quantity"];
        $query = Product::select('ProductName', 'Stock')->where('ProductID', $productId)->get();
        $qtyProduct = $query[0]['Stock'];
        (new ProductController())->updateField("stock", $productId, $qtyCart + $qtyProduct);  

        //Delete the item from the cart
        $items = Shoppingcarts::where('CartId', $cartId)->
                                where('ProductId', $productId)->delete();

        return $query[0]['ProductName'] . " was removed from cart " . $cartId . ".";
    } 


    /**
     * The function output the total payment to the cart by the given currency - EUR/USD
     * url structure - ../public/api/shoppingcart/{cartId}/totalPrice/{currency} 
     */
    public function getTotalPrice($cartId, $currency) {
        //Validation that the cart is not empty
        $items = $this->cartAvail($cartId);
        if($items == null)
            return "Cart " . $cartId . " is empty";

        if($currency != "EUR" && $currency != "USD")
            return "No possible to pay with " . $currency;
        
        $tot = 0;
        $items = Shoppingcarts::select('*')->where('CartId', $cartId)->get();
        foreach($items as $item) {
            $product = Product::select('ProductPrice')->where('ProductID', $item['ProductId'])->get();
            $priceProduct = $product[0]['ProductPrice'];
            $tot += $item['Quantity'] * $priceProduct;
        }

        //The base of the price is in USD
        if($currency == "EUR") {
            $url = 'https://api.exchangeratesapi.io/latest';
            $response_json = file_get_contents($url);
            $rate = $this->getInnerStr($response_json, 'USD":', ',"'); 
            $tot = $tot / $rate;
            $tot = round($tot, 1);
        }

        return "Total price to cart " . $cartId . " - " . $tot . " " . $currency . ".";
    }  

    /**
     * The function output all the products in the cart by either name or price or quantity.
     * url structure - ../public/api/shoppingcart/{carId}/sortBy/{type}
     */
    public function sortBy($cartId, $type) {
        
        if($type != 'name' && $type != 'price' && $type != 'quantity')
            return response("error url", 404);
        
        //Validation that the cart is not empty
        $items = $this->cartAvail($cartId);
        if($items == null)
            return "Cart " . $cartId . " is empty";

        $items = Shoppingcarts::select('*')->where('CartId', $cartId)->get();
        $arr = array();
        foreach($items as $item) {
            $product = Product::select('ProductName', 'ProductPrice')->where('ProductID', $item['ProductId'])->get();
            $innerArr = array();
            $innerArr[0] = $product[0]['ProductName'];
            $innerArr[1] = $product[0]['ProductPrice'];
            $innerArr[2] = $item['Quantity'];
            array_push($arr, $innerArr);
        }
        if($type == 'quantity') {
            $arr = $this->changeArr($arr, 0, 2);
            sort($arr);
            $arr = $this->changeArr($arr, 0, 2);
        }
        else if($type == 'price') {
            $arr = $this->changeArr($arr, 0, 1);
            sort($arr);
            $arr = $this->changeArr($arr, 0, 1);
        }
        else
            sort($arr);

        print_r($arr);
    }

    /**
     * The function checks if there are items in DB by the given cartID,
     * and return all the items or null if the cart is empty.
     */
    private function cartAvail($cartId) {
        $items = Shoppingcarts::find($cartId);
        if ($items == null)
            return null; 
        return $items;
    }

    /**
    * The function input string, start and end positions and return the 
    * inner string between the start and the end. 
    */
    private function getInnerStr($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) 
            return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

        /**
     * The function input array of arrays (items array) and 2 integers - $i1, $i2,
     * and swap the items in the inners arrays in positions $i1 and $i2 and
     * output the created new array.
     */
    private function changeArr($arr, $i1, $i2) {
        for($i = 0; $i < count($arr); $i++) {
            $temp = $arr[$i][$i1];
            $arr[$i][$i1] = $arr[$i][$i2];
            $arr[$i][$i2] = $temp;
        }
        return $arr;
    }

}


