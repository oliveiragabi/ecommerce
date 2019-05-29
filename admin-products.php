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

$app->get('/admin/products/:idproduct', function($idproduct){

	//vai pegar o id do produto e criar uma foto com o próprio id
	//nome do arq da imagem vai ser o id do produto 
	User::verifyLogin();

	//carregando os dados do produto

	$product = new Products();

	$product->get((int)$idproduct);


	$page = new PageAdmin();

	$page->setTpl("products-update", [

		"product"=>$product->getValues()

	]);
});


$app->post('/admin/products/:idproduct', function($idproduct){

	User::verifyLogin();

	$product = new Products();

	$product->get((int)$idproduct);


	//o que é campo é recebido com $_post
	$product->setData($_POST);
 	
 	$product->save();


 	//o que é arquivo é recebido com $files
 	$product->setPhoto($_FILES["file"]);

 	header("Location: /ecommerce/index.php/admin/products");
 	exit;
});

$app->get('/admin/products/:idproduct/delete', function($idproduct){

	//vai pegar o id do produto e criar uma foto com o próprio id
	//nome do arq da imagem vai ser o id do produto 
	User::verifyLogin();

	//carregando os dados do produto

	$product = new Products();

	$product->get((int)$idproduct);

	$product->delete();
	
	header("Location: /ecommerce/index.php/admin/products");
 	exit;


});
?>
