<?php 

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User; 
use \Hcode\Model\Category; 
use \Hcode\Model\Products; 


$app->get('/admin/users', function() {
	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;

	if($search != ''){

		$pagination = User::getPageSearch($search,$page, 5);

	}else{

		$pagination = User::getPage($page, 5);

	}


	$pages = [];

	for($x = 0; $x < $pagination['pages']; $x++){
		array_push($pages, [
			'href'=>'/ecommerce/index.php/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),

			'text'=>$x+1

		]);
	}
    
    $page = new PageAdmin();

	$page->setTpl("users", array(
		"users" => $pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	));


});

//acessando via get e tendo como resposta um html
$app->get('/admin/users/create', function() {
	User::verifyLogin();
    
    $page = new PageAdmin();

	$page->setTpl("users-create");


});

$app->get('/admin/users/:iduser/delete', function($iduser) {
	
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /ecommerce/index.php/admin/users");
 	exit;
    
});


$app->get('/admin/users/:iduser', function($iduser) {
	//só o fato de estar como um parâmetro obrigatório de rota, ele ja entende que a funçao consegue enchergar o parametro passado
	User::verifyLogin();

	$users = new User;

	$users->get((int)$iduser);


    $page = new PageAdmin();

	$page->setTpl("users-update", array(
		"users" => $users->getValues()

	));


});

//acessando via post e fazendo o insert dos dados
$app->post("/admin/users/create", function () {

 	User::verifyLogin();

	$users = new User();

 	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

 		"cost"=>12

 	]);

 	$users->setData($_POST);

	$users->save();

	header("Location: /ecommerce/index.php/admin/users");
 	exit;

});

$app->post('/admin/users/:iduser', function($iduser) {
	
	User::verifyLogin();

	$users = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$users->get((int)$iduser);

	$users-> setData($_POST);

	$users->update();

	header("Location: /ecommerce/index.php/admin/users");
 	exit;
    
});



?>