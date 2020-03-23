<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) { //localhost/investing/public/
    return $router->app->version();
});


$router->group(['prefix' => 'api'], function($router){

    /* ---Api to Products--- */
    /*Get Methods*/     
    $router->get('products', 'ProductController@getAllProducts');
    $router->get('products/{id}', 'ProductController@getProduct');
    $router->get('products/{field}/{id}', 'ProductController@getValue');
    $router->get('products/catalogs/list/{id}', 'ProductController@getCatalogs'); 

    /*Post Method*/
    $router->post('products', 'ProductController@newProduct');
    
    /*Put Methods*/
    $router->put('products/{id}', 'ProductController@updateProduct');
    $router->put('products/{field}/{id}/{value}', 'ProductController@updateField');
    
    /*Delete Method*/
    $router->delete('products/{id}', 'ProductController@deleteProduct');


    /* ---Api to Caltalog--- */
    /*Get Methods*/
    $router->get('catalogs', 'CatalogController@getAllCatalogs');
    $router->get('catalogs/{id}', 'CatalogController@getCatalog');
    $router->get('catalogs/{field}/{id}', 'CatalogController@getValue');
    $router->get('catalogs/products/list/{id}', 'CatalogController@getProducts'); 

    /*Post Method*/
    $router->post('catalogs', 'CatalogController@newCatalog');
        
    /*Put Methods*/
    $router->put('catalogs/{id}', 'CatalogController@updateCatalog');
    $router->put('catalogs/{field}/{id}/{value}', 'CatalogController@updateField');
    $router->put('catalogs/{id}/{productId}', 'CatalogController@updateProdtucsList');
    
    /*Delete Method*/
    $router->delete('catalogs/{id}', 'CatalogController@deleteCatalog');


    /* ---Api to Shopping Cart--- */
    $router->post('shoppingcart', 'ShoppingCart@get');
    $router->post('shoppingcart/delete', 'ShoppingCart@delete'); 
    $router->post('shoppingcart/newItem', 'ShoppingCart@add');
    $router->post('shoppingcart/updateItem', 'ShoppingCart@update');
    $router->post('shoppingcart/removeItem', 'ShoppingCart@remove');
    $router->post('shoppingcart/totalPrice/{currency}', 'ShoppingCart@getTotalPrice'); 
    $router->post('shoppingcart/sortBy/{type}', 'ShoppingCart@sortBy'); 


    /* ---Api to Shopping Cart - DB--- */
    /*Get Methods*/
    $router->get('shoppingcart/{cartId}', 'ShoppingcartsController@get');
    $router->get('shoppingcart/{cartId}/totalPrice/{currency}', 'ShoppingcartsController@getTotalPrice'); 
    $router->get('shoppingcart/{cartId}/sortBy/{type}', 'ShoppingcartsController@sortBy'); 

    /*Delete Method*/
    $router->delete('shoppingcart/{cartId}', 'ShoppingcartsController@delete'); 
    $router->delete('shoppingcart/{cartId}/removeItem/{productId}', 'ShoppingcartsController@remove');
    
    /*Post Method*/
    $router->post('shoppingcart/{cartId}', 'ShoppingcartsController@add');
    
    /*Put Methods*/ 
    $router->put('shoppingcart/{cartId}', 'ShoppingcartsController@update'); 

});

