<?php

namespace Hcode;

class Model{

	//dados do usuario
	private $values = [];


	public function __call($name, $args)
	{
		//traz 0 1 e 2
		$method = substr($name, 0, 3);

		$fieldName = substr($name, 3, strlen($name));

		switch ($method) {
			case 'get':
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
			break;

			case "set":
				$this->values[$fieldName] = $args[0];
			break;
			
		}
	}


	public function setData($data = array())
	{
		foreach ($data as $key => $value) {
			//como esta sendo criando dinamicamente o php permite 
			//tem que juntar o set e o nome do campo que esta vindo na variavel key
			//td que vc for criar dinamico no php tem que ser entre chaves
			// a string set sera executada como se fosse um método mesmo
			//nome do método			//aqui sao os parametros
			$this->{"set".$key}($value);	//chama cada um dos metodos automaticamente
		}
	}

	public function getValues(){
		return $this->values;
	}



}



?>