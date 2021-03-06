<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
USE \Hcode\Mailer;



class Category extends Model {

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");


	}

	public function save(){
        $sql = new Sql();
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
        ));
        $this->setData($results[0]);
        Category::updateFile();
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


		//método que traz todos os produtos 
		public function getProducts($related = true){

			$sql = new Sql();

			if($related === true){

				return	$sql->select("

					SELECT * FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
					);

					", [

						":idcategory"=>$this->getidcategory()

					]);
			}else{

				return $sql->select("

					SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
					);

					", [

						":idcategory"=>$this->getidcategory()

					]);



			}

		}

		public function addProduct(Products $product){

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", [
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);
	}



	public function removeProduct(Products $product){

		$sql = new Sql();
		
		$sql->query("DELETE FROM tb_productscategories WHERE :idproduct = idproduct AND :idcategory = idcategory", [
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);
	}


	public function getProductsPage($page = 1, $itemsPerPage = 2){

		$start = ($page-1) * $itemsPerPage;
		$sql= new Sql();
		
		$results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * from tb_products a 
					Inner join tb_productscategories b
					on a.idproduct = b.idproduct
					inner join tb_categories c on c.idcategory = b.idcategory
					where c.idcategory = :idcategory
					limit $start ,$itemsPerPage;", 
					[
						":idcategory"=>$this->getidcategory()
					]);
		$resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal");
		return  [
			'data'=>Products::checkList($results),
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];	


	}

	
     public static function getPage($page=1, $itemsPerPage = 10){
        $start = ($page-1) * $itemsPerPage; 
        $sql = new Sql();
        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_categories 
            ORDER BY descategory
            LIMIT $start, $itemsPerPage;");
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        return [
            'data'=>$results,
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"]/$itemsPerPage)  //ceil- função que coverte arredondando para cima (inteiro)
        ];
        
    }
    
    public static function getPageSearch($search, $page=1, $itemsPerPage = 10){
        $start = ($page-1) * $itemsPerPage; 
        $sql = new Sql();
        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_categories  
            WHERE descategory LIKE :search 
            ORDER BY descategory
            LIMIT $start, $itemsPerPage;",
            [
                ':search'=>'%'.$search.'%'
            ]);
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        return [
            'data'=>$results,
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"]/$itemsPerPage)  //ceil- função que coverte arredondando para cima (inteiro)
        ];  
    }


	}



?>