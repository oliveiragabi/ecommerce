<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\Cart;



class User extends Model {

	const SESSION = "User";
	const SECRET = "Hcodephp7_Secret";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";


	public static function getFromSession(){

		$user = new User(); 

		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

			$user->setData($_SESSION[User::SESSION]);
		}

		return $user;
	}

	public static function checkLogin($inadmin = true){
		
		if(
			!isset($_SESSION[User::SESSION]) 
			|| 
			!$_SESSION[User::SESSION] 
			|| 
			!(int)$_SESSION[User::SESSION]["iduser"] > 0){

			return false;

		}else{
			if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){
				return true;
			}else if($inadmin === false){
				
				return true;

			}else{
				
				return false;
			}
		}

	}
	

  // esse método verifica se o login que  foi digitado está no banco
  // se o login for valido, irá trazer o hash e validar se esse hash é compativel com o passado pelo usuario
	public static function login($login, $password) {

		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users a
			INNER JOIN tb_persons b
			ON a.idperson = b.idperson
			WHERE a.deslogin = :LOGIN", array(
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
		if(!User::checkLogin($inadmin)){
			if($inadmin){
				
				header("Location: /ecommerce/index.php/admin/login");
			
			} else {
				header("Location: /ecommerce/index.php/login");
			}
			exit;
		}
		
	}


	public static function logout(){

		$_SESSION[User::SESSION] = NULL;
	}


	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT *FROM tb_users a 
			INNER JOIN tb_persons b 
			USING(idperson) 
			ORDER BY b.desperson");


	}


	public function get ($iduser){

		$sql = new Sql();

		 $results = $sql->select("SELECT * FROM tb_users a 
		 	INNER JOIN tb_persons b 
		 	USING(idperson) 
		 	WHERE a.iduser = :iduser;", array(
 			":iduser"=>$iduser
 		));
 
 		$data = $results[0];

 		$data['desperson'] = utf8_encode($data['desperson']);
 
 		$this->setData($data);

	}


	public function save(){

		$sql = new Sql();

		//criando procedure
		$results = $sql->select("CALL sp_users_save( :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin )", array(
			":desperson" => utf8_decode($this->getdesperson()),
			":deslogin" => $this->getdeslogin(),
			":despassword" => User::getPasswordHash($this->getdespassword()),
			":desemail" => $this-> getdesemail(),
			":nrphone" =>$this->getnrphone(),
			":inadmin" => $this->getinadmin()
		));

		$this->setData($results[0]);
	}


	public function update(){
		$sql = new Sql();

		//criando procedure
		$results = $sql->select("CALL sp_usersupdate_save( :iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin )", array(
			":iduser" => $this->getiduser(),
			":desperson" => utf8_decode($this->getdesperson()),
			":deslogin" => $this->getdeslogin(),
			":despassword" => User::getPasswordHash($this->getdespassword()),
			":desemail" => $this-> getdesemail(),
			":nrphone" =>$this->getnrphone(),
			":inadmin" => $this->getinadmin()
		));

		$this->setData($results[0]);
	}


	public function delete(){

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}


	public static function getForgot($email)
	{
		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_persons a 
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :email;
			", array(
				":email" => $email 
			));

		if(count($results) === 0){

			//namespace principal
			throw new \Exception("Não foi possível recuperar a senha ");
			
		}else{

			$data = $results[0];
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=> $_SERVER["REMOTE_ADDR"]
			));


			if(count($results2) === 0 ){
				throw new \Exception("Não foi possível recuperar a senha" );
				
			}else{
				$dataRecovery = $results2[0];

				//fazendo criptografia
				//transformando em base64

				$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
				$code = openssl_encrypt($dataRecovery["idrecovery"], "aes-256-cbc", User::SECRET, 0, $iv);
				$result = base64_encode($iv . $code);

				$link = "http://127.0.0.1/ecommerce/index.php/admin/forgot/reset?code=$code";

				$mailer =  new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha do Curso PHP", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link

				) );

				$mailer->send();

				return $data;

			}
		}


	}


	public static function validForgotDescrypt($aux)
	{
		$aux = base64_decode($aux);

     	$code = mb_substr($aux, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');

     	$iv = mb_substr($aux, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');

     	$idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
     	
     	$sql = new Sql();
     	
     	$results = $sql->select("
     		SELECT * FROM tb_userspasswordsrecoveries a 
     		INNER JOIN tb_users b USING(iduser) 
     		INNER JOIN tb_persons c USING(idperson) 
     		WHERE 
     		a.idrecovery = :idrecovery 
     		AND 
     		a.dtrecovery IS NULL 
     		AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();",
     		 array(
         		":idrecovery"=>$idrecovery
         		));
     	
     	if (count($results) === 0)
     	{
        	throw new \Exception("Não foi possível recuperar a senha.");
     	}
     	else
     	{
         	return $results[0];
     	}

	}

	public static function  setError($msg){

		$_SESSION[User::ERROR] = $msg;
	}

	public static function getError(){

		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();
		return $msg;
	}

	public static function clearError(){

		$_SESSION[User::ERROR] = NULL;
	}

	public static function setErrorRegister($msg)
	{
		$_SESSION[User::ERROR_REGISTER] = $msg;
	}

	 public static function getErrorRegister(){
        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
        User::clearErrorRegister();
        return $msg;
     }

     public static function clearErrorRegister(){
        $_SESSION[User::ERROR_REGISTER] = NULL;
     }

     public static function getPasswordHash($password){
     	return password_hash($password, PASSWORD_DEFAULT, [
     		'cost'=>12
     	]);
     }


     public static function checkLoginExist($login){
     	$sql = new Sql();

     	$results = $sql->select("SELECT *FROM tb_users
     							WHERE deslogin = :deslogin", [
     			':deslogin'=>$login
     		]);

     	return (count($results) > 0);
     }





}



?>