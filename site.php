<?php 
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User; 
use \Hcode\Model\Category; 
use \Hcode\Model\Products; 

$app->get('/', function() {

	$products = Products::listAll();
    
	$page = new Page();

	$page->setTpl("index", [

		'products'=> Products::checkList($products)

	]);

});

$app->get('/categories/:idcategory', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	//tudo q vem na url é convertido p texto, ai precisa fazer o cast pra inteiro aqui no codigo

	$category->get((int)$idcategory);

	$page = new Page();
	
	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>Products::checkList($category->getProducts())

	]);

});


?>