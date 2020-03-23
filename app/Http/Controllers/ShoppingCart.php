<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ShoppingCart
{

    
    /**
     * The $items varaibale contains array of arrays.
     * The inner arrays contains name, quantity, price per unit and id of the product in db.
     * Inner array - array("ProductName" , "quantity", "price", "ProductId").
     * The current num of items restore in $numItems.  
     */
    private $items = array();

    private $numItems;


    public function __construct(Request $request)
    {   
        //Getting the data from session. If there is no data the default is null and 0 items.
        $this->items = $request->session()->get('items');
        $this->numItems = $request->session()->get('numItems');

        if($this->numItems == null) {
            $this->items = array();
            $this->numItems = 0;
        }

    }


    /**
     * The function output all products in the cart. 
     */
    public function get() {
        if($this->numItems == 0)
            return "The cart is empty";

        $products = null;
        foreach($this->items as $item) {
            $products = $products . $item[1] . " " . $item[0] . ", ";
        }
        $products = substr($products, 0, strlen($products) - 2);
        return "The items in the cart: " . $products;
    }

    /**
     * The function delete all products from the cart. 
     */
    public function delete(Request $request) { 
        
        //Updating the amount in db
        /*$productController = new ProductController();
        foreach($this->items as $item)
        {
            $qtyDb = Product::select('Stock')->where('ProductID', $item[3])->get();
            $newQty = $qtyDb[0]['Stock'] + $item[1];
            $productController->updateField("stock", $item[3], $newQty);
        }*/

        //Delete the data from sessions
        $request->session()->flush();
        
        return "The cart is empty";
    }

    /**
     * The function add new product and quantity to cart. 
     * input given as json - 
     *  {
     *      "name": x,
     *      "qty": y
     *  }
     * url structure - ../public/api/shoppingcart/newItem?name=x&qty=y
     */ 
    public function add(Request $item) {
        $productName = $item['name'];
        $productQty = $item['qty'];

        $queryRes = Product::select('ProductID', 'ProductPrice', 'Stock')->where('ProductName', $productName)->get();
        
        
        if(count($queryRes) == 0) //The product doesn't exist in db
            return $productName . " doesn't exist in the stock.";

        $productID = $queryRes[0]['ProductID'];
        $priceUnit = $queryRes[0]['ProductPrice'];
        $qtyInStock = $queryRes[0]['Stock'];

        if($productQty > $qtyInStock) { //Case the user wants to add more than the amount in the stock
            $productQty = $qtyInStock;
            echo "Only " . $productQty . " remains in the stock. <br>";
        }
        
        //Checking if the given item already exists in the cart
        $pos = $this->existsInItems($productName);
        if($pos == -1) { //new item in the cart
            $newItem = array($productName, $productQty, $priceUnit, $productID);
            array_push($this->items, $newItem);
            $this->numItems++;
        }
        else { //The item exists in the cart. we only increase the quantity.
            $this->items[$pos][1] += $productQty;
        }

        //Updateing the amount in db
        //(new ProductController())->updateField("stock", $productID, $qtyInStock - $productQty);

        //Saving the data in sessions to the next using.
        $item->session()->put('items', $this->items);
        $item->session()->put('numItems', $this->numItems);

        return $productQty . " of " . $productName . " was added to the cart.";     
    }

    /**
     * The function input item and quantity for updating
     * and update the quantity of product in the cart. 
     * input given as json of name and qty - 
     *  {
     *      "name: x,
     *      "qty": y
     *  }
     * url structure - ../public/api/shoppingcart/updateItem?name=x&qty=y
     */
    public function update(Request $item) { 
        $productName = $item['name'];
        $productQty = $item['qty']; 
        
        $pos = $this->existsInItems($productName);
        if($pos == -1) 
            return "The item doesn't exist in the cart.";

        $queryRes = Product::select('ProductID', 'Stock')->where('ProductName', $productName)->get();  
        $qtyInStock = $queryRes[0]['Stock'];
        $productID = $queryRes[0]['ProductID'];

        $prevQty = $this->items[$pos][1];
        if($productQty > ($qtyInStock + $prevQty)) { //Case the user wants to add more than the amount in the stock
            $productQty = ($qtyInStock + $prevQty);
            echo "You can change to only " . $productQty . ".<br>";
        }

        $this->items[$pos][1] = $productQty;

        //Updateing the amount in db
        //(new ProductController())->updateField("stock", $productID, $prevQty + $qtyInStock - $productQty);

        //Saving the data in sessions to the next using.
        $item->session()->put('items', $this->items);

        return "The quantity of " . $productName . " was changed to " . $productQty;       
    }   

    /**
     * The function input the item name and delete the item from the cart.
     * input given as json of name - 
     *  {
     *      name: x
     *  }
     * url structure - ../public/api/shoppingcart/removeItem?name=x
     */
    public function remove(Request $itemId) {
        $productName = $itemId['name'];
        $pos = $this->existsInItems($productName);
        if($pos == -1) 
            return "The item doesn't exist in the cart.";

        $productQty = $this->items[$pos][1];
        $productID = $this->items[$pos][3];
        unset($this->items[$pos]);
        $this->numItems--;

        //Saving the data in sessions to the next using.
        $itemId->session()->put('items', $this->items);
        $itemId->session()->put('numItems', $this->numItems);

        //Updateing the amount in db
        /*$qtyDb = Product::select('Stock')->where('ProductID', $productID)->get();
        $qtyDb = $qtyDb[0]['stock'];
        (new ProductController())->updateField("stock", $productID, $qtyDb + $productQty);*/

        return $productName . " was removed from the cart.";
    } 

    /**
     * The function output the total payment to the cart by the given currency - EUR/USD
     * url structure - ../public/api/shoppingcart/getTotalPrice/{currency} 
     */
    public function getTotalPrice($currency) {
        if($currency != "EUR" && $currency != "USD")
            return "No possible to pay with " . $currency;
        $tot = 0;
        foreach($this->items as $item) {
            $tot += ($item[1] * $item[2]);
        }

        //try-catch ??
        //The base of the price is in USD
        if($currency == "EUR") {
            $url = 'https://api.exchangeratesapi.io/latest';
            $response_json = file_get_contents($url);
            $rate = $this->getInnerStr($response_json, 'USD":', ',"'); 
            $tot = $tot / $rate;
            $tot = round($tot, 1);
        }

        return "Total price - " . $tot . " " . $currency . ".";
    }  

    /**
     * The function output all the products in the cart by either name or price or quantity.
     * url structure - ../public/api/shoppingcart/sortBy/{type}
     */
    public function sortBy($type) {
        if($type != 'name' && $type != 'price' && $type != 'quantity')
            return response("error url", 404);
        
        $newArr = $this->items;
        if($type == 'quantity') {
            $newArr = $this->changeArr($newArr, 0, 1);
            sort($newArr);
            $newArr = $this->changeArr($newArr, 0, 1);
        }
        else if($type == 'price') {
            $newArr = $this->changeArr($newArr, 0, 2);
            sort($newArr);
            $newArr = $this->changeArr($newArr, 0, 2);
        }
        else
            sort($newArr);

        print_r($newArr);
    }

    /**
     * The function checks if the product exists in $items
     * and return the position in $items or -1 if the product doesn't exist.
     */
    private function existsInItems($productName) {
        $pos = -1;
        if($this->numItems == 0)
            return $pos;
        $arr = $this->items;
        for($i = 0; $i < count($arr); $i++) {
            if($arr[$i][0] == $productName) { 
                $pos = $i;
                break;
            }
        }
        return $pos;
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


    


    