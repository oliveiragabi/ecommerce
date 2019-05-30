<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Mailer;
use \Hcode\Model;


class Products extends Model {

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");


	}

	//fazendo checagem na lista de produtos e passando o desphoto pq senao a foto n carrega na home, 

	public static function checkList($list){

		//usando a mesma variavel
		foreach ($list as &$row) {
			$p = new Products();
			$p->setData($row);
			//passou p getvalues e esta verificando se existe a foto ou n
			$row = $p->getValues();
		}

		return $list;

	}


	public function save(){
		$sql = new Sql();
		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
        	":idproduct" =>$this->getidproduct(),
	        ":desproduct" =>$this->getdesproduct(),
	        ":vlprice" =>$this->getvlprice(),
	        ":vlwidth" =>$this->getvlwidth(),
	        ":vlheight" =>$this->getvlheight(),
	        ":vllength" =>$this->getvllength(),
	        ":vlweight" =>$this->getvlweight(),
	        ":desurl" =>$this->getdesurl()
    ));
		$this->setData($results[0]);
	}


	public function get ($idproduct){

		$sql = new Sql();

		 $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct ", array(
 			":idproduct"=>$idproduct
 		));
 
 		$this->setData($results[0]);

	}

	
	public function delete(){
		$sql = new Sql();
		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$this->getidproduct()
		));
	}


	public function checkPhoto(){
		if(file_exists($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . 
						"ecommerce" .  DIRECTORY_SEPARATOR .
						"res". DIRECTORY_SEPARATOR . 
						"site" . DIRECTORY_SEPARATOR . 
						"img" . DIRECTORY_SEPARATOR . 
						"products" . DIRECTORY_SEPARATOR . 
						$this->getidproduct() . ".jpg")){
			$url = "/ecommerce/res/site/img/products/" . $this->getidproduct() . ".jpg";
		}else{
			$url = "/ecommerce/res/site/img/product.jpg";
		}
		return $this->setdesphoto($url);
	}



	public function getValues(){
		$this->checkPhoto();
		$value = parent::getValues();
		return $value;
	}



	public function setPhoto($file){

		//convertendo arq que nao sao jpg para jpg

		$extension = explode( '.', $file['name']);

		//pegando a extensão do arquivo
		$extension = end($extension);


		switch ($extension) {
			case 'jpg':
			case 'jpeg':
			$image = imagecreatefromjpeg($file['tmp_name']);
				# code...
				break;


			case 'gif':
			$image = imagecreatefromgif($file['tmp_name']);
				break;


			case 'png':
			$image = imagecreatefrompng($file['tmp_name']);
				break;
		}


		$dest = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . 
						"ecommerce" .  DIRECTORY_SEPARATOR .
						"res". DIRECTORY_SEPARATOR . 
						"site" . DIRECTORY_SEPARATOR . 
						"img" . DIRECTORY_SEPARATOR . 
						"products" . DIRECTORY_SEPARATOR . 
						$this->getidproduct() . ".jpg";
		

		imagejpeg($image, $dest);
		imagedestroy($image);

		$this->checkPhoto();


	}


	public function getFromURL($desurl){

		$sql = new Sql();
		$rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
			':desurl'=>$desurl
		]);

		$this->setData($rows[0]);
	}


	public function getCategories(){

		$sql = new Sql();

		return $results = $sql->select("
			SELECT * FROM tb_categories a 
			INNER JOIN tb_productscategories b 
			ON a.idcategory = b.idcategory
			WHERE b.idproduct = :idproduct 
			", [

				':idproduct'=>$this->getidproduct()

			]);

		
	}


}



?>