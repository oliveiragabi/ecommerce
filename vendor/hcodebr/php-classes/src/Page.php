<?php 

namespace Hcode;

use Rain\Tpl;

class Page{
	private $tpl;
	private $options =[];
	private $defaults = [
		"header" => true,
		"footer" => true,
		"data" => []
	];


//criando metodos magicos
	public function __construct($opts = array(), $tpl_dir ="/ecommerce/views/"){
		
		//quero que o que a pessoa informou como parametro no array opts da classe construtura sobrescreva no array defaults 
		//se eu passar um parametro no opts e der conflito com o defaults, vale o opts
		//o merge mescla as informações e guarda dentro de options
		
		$this->defaults["data"]["session"] = $_SESSION;
		$this->options = array_merge($this->defaults, $opts);

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]. $tpl_dir,
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/ecommerce/views-cache/",
			"debug"         => false // set to false to improve the speed
				   );

		Tpl::configure($config);

		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);
	
		/*
		//os dados estao na chave data
		foreach ($this->options["data"] as $key => $value) {
		
		// o método assign espera uma chave e um valor
		$this->tpl->assign($key, $value);
		}
		*/

		//validando e desenhando o header
		if($this->options["header"] === true) $this->tpl->draw("header");
} 


		private function setData($data = array()){

			foreach ($data as $key => $value) {
				// o método assign espera uma chave e um valor
				$this->tpl->assign($key, $value);
	}

}

		//body html
		public function setTpl($name, $data = array(), $returnHTML = false){
			$this->setData($data);
			//desenhando um template na tela 
			return $this->tpl->draw($name, $returnHTML);
		}


		//criando o footer
		public function __destruct(){
			if($this->options["footer"] === true) $this->tpl->draw("footer");
		}


}




?> 