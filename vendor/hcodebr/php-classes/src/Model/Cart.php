<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;
use \Hcode\Model\Products;


class Cart extends Model {

	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";


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

				if(User::checkLogin(false)=== true){ //se estiver logado, traz o usuario e o id dele

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
		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=> $idcart
		]);
		if(count($results) > 0){
		$this->setData($results[0]);
		}

	}

	public function getFromSessionID (){

		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'=>session_id()
		]);
		if(count($results) > 0){
		$this->setData($results[0]);
		}

	}


	public function addProduct(Products $product){

		$sql = new Sql();
		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
		]);

		$this->getCalculateTotal();


	}

	public function removeProduct(Products $product, $all = false){

		$sql = new Sql();
		if($all){
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);
		} else {
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);
		}

		$this->getCalculateTotal();
	
	}


	public function getProducts(){
	
		$sql = new Sql();
		
		return Products::checkList($sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl,
							COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
							FROM tb_cartsproducts a 
							INNER JOIN tb_products b 
							ON a.idproduct = b.idproduct 
							WHERE a.idcart = :idcart
							AND a.dtremoved IS NULL 
							GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth , b.vlheight, b.vllength, b.vlweight, b.desurl
							ORDER BY b.desproduct;", [
			':idcart'=> $this->getidcart()
		]));

		return Product::checkList($rows);

	}


	public function getProductsTotals(){
		$sql = new Sql();

		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) as vlwidth, SUM(vlheight) as vlheight, SUM(vllength) as vllength, SUM(vlweight) as vlweight, COUNT(*) AS nrqtd
			FROM tb_products a
			INNER JOIN tb_cartsproducts b on a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL;
			", [
				':idcart'=>$this->getidcart()
			]);

		if(count($results) > 0 ){
			return $results[0];
		}else{
			return [];
		}

	}


	public function setFreight($nrzipcode){

		$nrzipcode = str_replace('-', '', $nrzipcode);

		$totals = $this->getProductsTotals();

		if($totals['nrqtd'] > 0 ){
			//funcao para ler xml
			//url do caminho e o metodo q vai se usado
			if($totals['vlwidth'] < 11 ) $totals['vlwidth'] = 11;
			if($totals['vlheight'] < 2) $totals['vlheight'] = 2;
			if($totals['vllength'] < 16) $totals['vllength'] = 16;


		$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'40010',
				'sCepOrigem'=>'09853120',
				'sCepDestino'=>$nrzipcode,
				'sVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1',
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=> $totals['vlprice'],
				'sCdAvisoRecebimento'=>'S'
			]);


		$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

     	$result = $xml->Servicos->cServico;
     	
		if($result->MsgError != ''){

			Cart::setMsgError($result->MsgErro);


		}else{
			Cart::clearMsgErro();
		}

			$this->setntdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);

			$this->save();

			return $result;

		} else{

		}
	}

	public static function formatValueToDecimal($value):float{

		$value = str_replace('.', '', $value);
		return str_replace(',', '.',  $value);

	}


	public function updateFreight(){
		if($this->getdeszipcode() != ''){

			$this->setFreight($this->getdeszipcode());
		}
	}

	public function getValues(){

		$this->getCalculateTotal();

		return parent::getValues();
	}

	public function getCalculateTotal(){

		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());
	}

	}



?>