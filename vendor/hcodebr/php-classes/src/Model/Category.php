<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Mailer;
use \Hcode\Model;


class Category extends Model {

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");


	}

	public function save(){

		$sql = new Sql();

		//criando procedure
		$results = $sql->select("CALL sp_categories_save( :idcategory, :descategory )", array(
			":idcategory" => $this->getidcategory(),
			":descategory" => $this->getdescategory(),

		));

		$this->setData($results[0]);

		Category::updatefile();
	}


	public function get ($idcategory){

		$sql = new Sql();

		 $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory ", array(
 			":idcategory"=>$idcategory
 		));
 
 		$this->setData($results[0]);

	}

	public function delete(){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$this->getidcategory()
		));

		Category::updatefile();
	}


	public static function updatefile(){

		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			 array_push($html, '<li><a href="/ecommerce/index.php/categories/'. $row['idcategory'] .'">'. $row["descategory"] . '</a></li>');
		}

		//caminho físico do arquivo,  conteúdo
		//convertendo o array do html p string com implode 
		//explode: string-array
		file_put_contents($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . "ecommerce" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html" , implode('', $html));

		}


	}



?>