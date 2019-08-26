<?php  
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION); //5.2.7 ->  50207       5.5.28 -> 50528
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
class CECA{

	/******  Datos de Entrada ******/
    	var $vars_pay = array();
	
	/******  Set parameter ******/
		function setParameter($key,$value){
			$this->vars_pay[$key]=$value;
		}

	/******  Get parameter ******/
		function getParameter($key){
			return $this->vars_pay[$key];
		}

	/********************** Funciones formulario de pago **********************/
	
		/******  Número de pedido ******/
			function getOrder(){
				$numPedido = "";
				if(empty($this->vars_pay['Num_operacion'])){
					$numPedido = $this->vars_pay['Num_operacion'];
				} else {
					$numPedido = $this->vars_pay['Num_operacion'];
				}
				return $numPedido;
			}
			function createMerchantSignature($key){
				$MerchantID = $this->vars_pay['MerchantID'];
				$AcquirerBIN = $this->vars_pay['AcquirerBIN'];
				$TerminalID = $this->vars_pay['TerminalID'];
				$Num_operacion = $this->vars_pay['Num_operacion'];
				$Importe = $this->vars_pay['Importe'];
				$tipomoneda = $this->vars_pay['TipoMoneda'];
				$Exponente = '2';
				$cifrado = 'SHA2';
				$URL_OK = $this->vars_pay['URL_OK'];
				$URL_NOK = $this->vars_pay['URL_NOK'];
				$string = "$key $MerchantID $AcquirerBIN $TerminalID $Num_operacion $Importe $tipomoneda $Exponente \"\"  "; 
                $signature = hash('sha256', $key.$MerchantID.$AcquirerBIN.$TerminalID.$Num_operacion.$Importe.$tipomoneda.$Exponente.$cifrado.$URL_OK.$URL_NOK);								
				return $signature;
			}


}

?>
