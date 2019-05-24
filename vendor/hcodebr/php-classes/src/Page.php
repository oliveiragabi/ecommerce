<?php 

namespace Hcode;

use Rain\Tpl;

class Page{
	private $tpl;
	private $options =[];
	private $defaults = [
		"data" => []
	];


//criando metodos magicos
	public function __construct($opts = array()){
		
		//quero que o que a pessoa informou como parametro no array opts da classe construtura sobrescreva no array defaults 
		//se eu passar um parametro no opts e der conflito com o defaults, vale o opts
		//o merge mescla as informações e guarda dentro de options
		$this->options = array_merge($this->defaults, $opts);

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]. "/ecommerce/views/",
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

		//desenhando o header
		$this->tpl->draw("header");
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
			return $this->tpl->draw($name, $data, $returnHTML);
		}


//criando o footer
		public function __destruct(){
			$this->tpl->draw("footer");
		}


}




?> 
