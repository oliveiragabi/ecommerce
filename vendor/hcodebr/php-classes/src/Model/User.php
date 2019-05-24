<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;


class User extends Model {

	const SESSION = "User";

  // esse método verifica se o login que  foi digitado está no banco
  // se o login for valido, irá trazer o hash e validar se esse hash é compativel com o passado pelo usuario
	public static function login($login, $password) {

		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		)); //o ;login é igual ao login recebido no parametro

		if(count($results) === 0)
		{
			// tem que colocar a barra pra achar a exception principal
			throw new \Exception("Usuário inexistente ou senha invalida");
			
		}

		//dados do usuario em hash
		$data = $results[0];

		if(password_verify($password, $data["despassword"]) === true)
		{
			$user = new User();

			//o método set data pega os valores  que tem no banco e coloca em uma variavel 
			$user->setData($data);

			$_SESSION[User::SESSION]= $user->getValues();

			//usando métodos mágicos para nao ficar chamando get e set td hora
			return $user;

		} else
		{
			throw new \Exception("Usuário inexistente ou senha invalida");
		}

	}

	public static function verifyLogin($inadmin = true)
	{
		if(
			!isset($_SESSION[User::SESSION]) 
			|| 
			!$_SESSION[User::SESSION] 
			|| 
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 
			|| 
			(bool)$_SESSION(User::SESSION)["inadmin"] !== $inadmin

		) {
			header("Location: /ecommerce/index.php/admin/login");
			exit();

		}
		
	}


}



?>