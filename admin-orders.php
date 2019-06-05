 <?php 

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User; 
use \Hcode\Model\Category; 
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus; 

$app->get('/admin/orders', function() {

    User::verifyLogin();


	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;

	if($search != ''){

		$pagination = Order::getPageSearch($search,$page, 5);

	}else{

		$pagination = Order::getPage($page, 5);

	}


	$pages = [];

	for($x = 0; $x < $pagination['pages']; $x++){
		array_push($pages, [
			'href'=>'/ecommerce/index.php/admin/orders?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),

			'text'=>$x+1

		]);
	}

	$page = new PageAdmin();

	$page->setTpl("orders",[
		"orders"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);

});

$app->get('/admin/orders/:idorder/delete', function($idorder) {

    User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$order->delete();

	header("Location: /ecommerce/index.php/admin/orders");
	exit;

});

 $app->get('/admin/orders:idorder', function($idorder) {

    User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl("order",[
		"order"=>$order->getValues(),
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts()
	]);

});

  $app->get('/admin/orders:idorder/status', function($idorder) {

    User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);


	$page = new PageAdmin();

	$page->setTpl("order-status",[
		"order"=>$order->getValues(),
		"status"=>OrderStatus::listAll(),
		'msgSuccess'=>Order::getSuccess(),
		'msgError'=>Order::getError()
		
	]);

});

$app->post('/admin/orders:idorder/status', function($idorder) {

    User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setSuccess("Status atualizado.");

	header("Location: /ecommerce/index.php/admin/orders" .$idorder. "/status");
	exit;

});


?>