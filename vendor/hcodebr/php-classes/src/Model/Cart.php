<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Mailer;
use \Hcode\Model;
use \Hcode\Model\User;


class Cart extends Model {

	const SESSION = "Cart";


	public static function getFromSession(){

		$cart = new Cart();

		// verifica se a sessao cart exist e se tem o id > 0
		if(isset($_SESSION[Cart::SESSION]['idcart']) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

			//se for verdade, carrega o carrinho 

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
		}else{
			$cart->getFromSessionID();


			//CRIA UM CARRINHO NOVO
			if(!(int)$cart->getidcart() > 0){
				$data = [
					'dessessionid'=>session_id()
				];

				if(User::checkLogin(false)){ //se estiver logado, traz o usuario e o id dele

					$user = User::getFromSession();
					$data['iduser'] = $user->getiduser();
				}

				$cart->setData($data);
				$cart->save();
				$cart->setToSession();


			}



		}

	return $cart;

	}


	public function setToSession(){

		$_SESSION[Cart::SESSION] = $this->getValues();

	}



	//a primeira vez que o usuário cria um produto no carrinho é um insert
	//depois disso é só update
	//pra fazer um update em um insert que ja foi feito é necessaŕio saber qual é o id do carrinho e onde será guardado isso/ numa sessao

	public function save(){


		$sql = new Sql();
		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight,:nrdays)",
		[
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);
	}


	public function get (int $idcart){

		$sql = new Sql();

		 $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart ", array(
 			":idcart"=>$idcart
 		));

		 if(count($results) > 0 ){
 
 		$this->setData($results[0]);

 		}
	}

	public function getFromSessionID (){

		$sql = new Sql();

		 $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid ", array(
 			":dessessionid"=>session_id()
 		));
 
 		 if(count($results) > 0 ){
 
 		$this->setData($results[0]);

 		}

	}


	public function addProduct(Products $product){

		$sql = new Sql();

		$sql->select("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()

		]);


	}

	public function remove(){
		
	}



	}



?>