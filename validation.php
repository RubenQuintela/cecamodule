<?php  
include(dirname(__FILE__).'/../../config/config.inc.php');

if (isset(Context::getContext()->controller)) {
    $controller = Context::getContext()->controller;
} else {
    $controller = new FrontController();
    $controller->init();
    $controller->setMedia();
}
Tools::displayFileAsDeprecated();
$controller->displayHeader();

include(dirname(__FILE__).'/cecamodule.php');

if(!function_exists("writeLog")||!function_exists("generateIdLogCECA")) {
	require_once('apiCeca/cecaLib.php');
}

$idLog = generateIdLogCECA();
writeLog($idLog.' - Conexión  - ',true);

$cecamodule = new cecamodule();

if (!empty($_POST)){
	$MerchantID     = $_POST["MerchantID"];
	$AcquirerBIN    = $_POST["AcquirerBIN"];
	$TerminalID     = $_POST["TerminalID"];
	$Num_operacion  = $_POST["Num_operacion"];
	$Importe        = $_POST["Importe"];
	$Tipo_moneda    = $_POST["TipoMoneda"];
	$Exponente      = $_POST["Exponente"];
	$Referencia     = $_POST["Referencia"];
	$signature      = $_POST["Firma"];
	$Num_aut        = $_POST["Num_aut"];
	$moneda_tienda = 1; // Euros

	$ImporteEUR = round(floatval($Importe/100), 2);

	$key = Configuration::get('CECA_ClaveCifrado');
	$local_signature = sha1($key.$MerchantID.$AcquirerBIN.$TerminalID.$Num_operacion.$Importe.$Tipo_moneda.$Exponente.$Referencia);
	
	writeLog($idLog.' - IP: '.$_SERVER['REMOTE_ADDR'].' - ',true);
	writeLog($idLog.' - Número de operación: '.$Num_operacion.' - ',true);
	writeLog($idLog.' - Firma calculada: '.$local_signature.' - ',true);
	writeLog($idLog.' - Firma CECA: '.$signature.' - ',true);
	writeLog($idLog.' - Referencia: '.$Referencia.' Número de Autorización: '.$Num_aut.' - ',true);
	writeLog($idLog.' - ------------ - ',true);

	if ($signature != $local_signature){
		writeLog($idLog.' - Error: Las firmas no coinciden - ',true);
		writeLog($idLog.' - Devolviendo error desde plataforma - ',true);
		echo "Error: Las firmas no coinciden";
	}else{
		/** Objetos para confirmar el pedido **/
		$pedidoSecuencial = $Num_operacion;
		$pedido = intval(substr($pedidoSecuencial, 0, 11));
		$cart = new Cart($pedido);

		/** Validación cliente **/
		$customer = new Customer((int)$cart->id_customer);


		/** VALIDACIONES DE DATOS y LIBRERÍA **/
		//Total
		writeLog($idLog.' -- Total (' . $Num_operacion . '): '.$ImporteEUR.' €',true);
		writeLog($idLog.' - ------------ - ',true);
		writeLog($idLog.' - Success: Las firmas coinciden - ',true);
		writeLog($idLog.' - Devolviendo estado PAGADO desde plataforma - ',true);
		$cecamodule->validateOrder($cart->id, _PS_OS_PAYMENT_, $ImporteEUR, $cecamodule->displayName, null, array(), (int)$cart->id_currency, $cecamodule->l('Pago recibido. Número de autorización: ').$Num_aut, $customer->secure_key);
		echo '$*$OKY$*$';
	}
}else{
	writeLog($idLog.' - Acceso no autorizado, conexión no realizada por POST  - ',true);
	writeLog($idLog.' - Devolviendo error desde plataforma - ',true);
	echo "Error: Acceso no autorizado.";
}

?>