<?php 

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User; 
use \Hcode\Model\Category; 
use \Hcode\Model\Products; 


$app->get('/admin', function() {
    User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("index");

});

$app->get('/admin/login', function() {
    
    //desabilitando o header e o footer
	$page = new PageAdmin([
		"header" => false,
		"footer" => false

	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function() {
    User::login($_POST["login"], $_POST["password"]);
    //desabilitando o header e o footer

    header("Location: /ecommerce/index.php/admin");

	exit();

});

$app->get('/admin/logout', function() {
    
    User::logout();

    header("Location: /ecommerce/index.php/admin/login");

	exit();


});


$app->get('/admin/forgot', function() {

    //desabilitando o header e o footer
	$page = new PageAdmin([
		"header" => false,
		"footer" => false

	]);

	$page->setTpl("forgot");
    
});

$app->post('/admin/forgot', function() {

    $user = User::getForgot($_POST["email"]);

    header("Location: /ecommerce/index.php/admin/forgot/sent");
    exit;
    
});

$app->get('/admin/forgot/sent', function() {

    $page = new PageAdmin([
		"header" => false,
		"footer" => false

	]);

    $page->setTpl("forgot-sent");
    
});

$app->get('/admin/forgot/reset', function() {

	$user = User::validForgotDescrypt($_GET["code"]);

    $page = new PageAdmin([
		"header" => false,
		"footer" => false

	]);

    $page->setTpl("forgot-reset", array(
    	"name"=>$user["desperson"],
    	"code" => $_GET["code"]
    ));
    
});



?>