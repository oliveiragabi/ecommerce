<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User; 
use \Hcode\Model\Category; 
use \Hcode\Model\Products; 



$app->get('/admin/products', function(){

	User::verifyLogin();

	$products = Products::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$products
	]);
});


$app->get('/admin/products/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");
});

$app->post('/admin/products/create', function(){

	User::verifyLogin();

	$product = new Products();

	$product->setData($_POST);
 	
 	$product->save();

 	header("Location: /ecommerce/index.php/admin/products");
 	exit;
});

?>
