<?php
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if(!function_exists("writeLog")) {
	require_once('apiCeca/cecaLib.php');
}
if(!class_exists("CECA")) {
	require_once('apiCeca/ceca.php');
}

if (!defined('_PS_VERSION_')) {
    exit;
}

class cecamodule extends PaymentModule{

	protected	$html = '';
	protected $_postErrors = array();

	public function __construct(){
		$this->name = 'cecamodule';
	    $this->tab = 'payments_gateways'; 
	    $this->version = '1.0.0'; 
	    $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
	    $this->author ='Rubén Quintela Cancelo: https://twitter.com/rubenquintela';
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		// Datos de configuración
		$config = Configuration::getMultiple(array('CECA_Nombre','CECA_URLTPV','CECA_MerchantID','CECA_AcquirerBIN','CECA_TerminalID','CECA_ClaveCifrado'));

		// Establecer propiedades mediante datos de configuración
		$this->env = $config['CECA_URLTPV'];
		switch ($this->env){
			case 0: //Producción
				$this->urltpv = 'https://pgw.ceca.es/cgi-bin/tpv';
				break;
			case 1: //Desarrollo-Pruebas
				$this->urltpv = 'http://tpv.ceca.es:8000/cgi-bin/tpv';
				break;
		}

		if (isset($config['CECA_Nombre'])){
			$this->nombre = $config['CECA_Nombre'];
		}
		if (isset($config['CECA_MerchantID'])){
			$this->merchant_id = $config['CECA_MerchantID'];
		}
		if (isset($config['CECA_AcquirerBIN'])){
			$this->acquirer_bin = $config['CECA_AcquirerBIN'];
		}
		if (isset($config['CECA_TerminalID'])){
			$this->terminal_id = $config['CECA_TerminalID'];
		}
		if (isset($config['CECA_ClaveCifrado'])){
			$this->clavecifrado = $config['CECA_ClaveCifrado'];
		}

	    parent::__construct();
	    
 		$this->page = basename(__FILE__, '.php');
	    $this->displayName = $this->l('TPV CECA para prestashop 1.7'); 
	    $this->description = $this->l('Pasarela de pagos CECA para prestashop 1.7.x.x.'); 
	 
	    if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No se ha establecido ninguna moneda para este módulo.');
        } 

	 	// Mostrar aviso si faltan datos de config.
		if (!isset($this->urltpv)
		|| !isset($this->nombre)
		|| !isset($this->merchant_id)
		|| !isset($this->acquirer_bin)
		|| !isset($this->terminal_id)
		|| !isset($this->clavecifrado)){
			$this->warning = $this->l('Datos pendientes de configurar en módulo de CECA.');			
		}	   
	}

	public function install(){
		if (!Configuration::updateValue('CECA_URLTPV', '0')
			|| !Configuration::updateValue('CECA_Nombre', $this->l('Escriba el nombre de su tienda'))
			|| !Configuration::updateValue('CECA_MerchantID', $this->l('Escriba su MerchantID'))
			|| !Configuration::updateValue('CECA_AcquirerBIN', $this->l('Escriba su AcquireID'))
			|| !Configuration::updateValue('CECA_TerminalID', $this->l('00000003'))
			|| !Configuration::updateValue('CECA_ClaveCifrado', $this->l('Escriba la clave de cifrado'))){
			return false;			
		}
		return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn')
        ;  
	}

	public function uninstall(){
		if (!Configuration::deleteByName('CECA_URLTPV')
			|| !Configuration::deleteByName('CECA_Nombre')
			|| !Configuration::deleteByName('CECA_MerchantID')
			|| !Configuration::deleteByName('CECA_AcquirerBIN')
			|| !Configuration::deleteByName('CECA_TerminalID')
			|| !Configuration::deleteByName('CECA_ClaveCifrado')
			|| !parent::uninstall()){
			return false;			
		}
		return true;
	}

	private function _postValidation(){
		// Comprobar que no existan datos vacíos en formulario de configuración
		if (Tools::isSubmit('btnSubmit')){	
			if (!Tools::getValue('nombre') || !checkNombreComecioCECA(Tools::getValue('nombre'))){
				$this->_postErrors[] = $this->l('Se requiere el nombre del comercio o el valor indicado para el nombre del comercio no es correcto (Alfanumérico sin espacios).');
			}			
			if (!Tools::getValue('merchant_id')){
				$this->_postErrors[] = $this->l('Debe proporcionar el Merchant ID.');					
			}
			if (!Tools::getValue('acquirer_bin')){
				$this->_postErrors[] = $this->l('Debe proporcionar el Acquirer BIN.');	
			}
			if (!Tools::getValue('terminal_id')){
				$this->_postErrors[] = $this->l('Debe proporcionar el Terminal ID.');				
			}
			if (!Tools::getValue('clavecifrado')){
				$this->_postErrors[] = $this->l('Debe proporcionar la clave  de cifrado proporcionada por CECA.');		
			}
		}
	}	

	private function _postProcess(){
		// Actualizar configuración en BBDD
		if (Tools::isSubmit('btnSubmit')){
			Configuration::updateValue('CECA_URLTPV', Tools::getValue('urltpv'));
			Configuration::updateValue('CECA_Nombre', Tools::getValue('nombre'));
			Configuration::updateValue('CECA_MerchantID', Tools::getValue('merchant_id'));
			Configuration::updateValue('CECA_AcquirerBIN', Tools::getValue('acquirer_bin'));
			Configuration::updateValue('CECA_TerminalID', Tools::getValue('terminal_id'));
			Configuration::updateValue('CECA_ClaveCifrado', Tools::getValue('clavecifrado'));
		}
		$this->html .= $this->displayConfirmation($this->l('Configuración actualizada'));
	}

	private function _displayCecamodule(){
		$this->html .= '<img src="../modules/cecamodule/views/img/ceca-logo.png" style="float:left; margin-right:15px;"><b><br />'
		.$this->l('Módulo para aceptar pagos con tarjeta en TPV CECA.').'</b><br />'
		.$this->l('Con este módulo, podrá pagar por la plataforma bancaria.').'<br /><br /><br />';
	}

	private function _displayForm(){

		// Opciones entorno
		if (!Tools::getValue('urltpv')){
			$entorno = Tools::getValue('env', $this->env);
		}else{
			$entorno = Tools::getValue('urltpv');
		}
		$entorno_real = ($entorno == 0) ? ' selected="selected" ' : '';
		$entorno_test = ($entorno == 1) ? ' selected="selected" ' : '';

		// Mostar formulario
		$this->html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<legend><img src="../img/admin/contact.gif" />'.$this->l('Configuración del TPV').'</legend>
					<table border="0" width="680" cellpadding="0" cellspacing="0" id="form-ceca-config">
						<tr><td colspan="2">'.$this->l('Por favor completa los datos de configuración del comercio').'.<br /><br /></td></tr>
						<tr><td width="255" style="height: 35px;">'.$this->l('Entorno CECA').'</td><td><select name="urltpv"><option value="0"'.$entorno_real.'>'.$this->l('Real (Producción)').'</option><option value="1"'.$entorno_test.'>'.$this->l('Pruebas (Desarrollo)').'</option></select></td></tr>
						<tr><td width="255" style="height: 35px;">'.$this->l('Nombre del comercio').'</td><td><input type="text" name="nombre" value="'.htmlentities(Tools::getValue('nombre', $this->nombre), ENT_COMPAT, 'UTF-8').'" style="width: 200px;" /></td></tr>
						<tr><td width="255" style="height: 35px;">'.$this->l('MerchantID').'</td><td><input type="text" name="merchant_id" value="'.Tools::getValue('merchant_id', $this->merchant_id).'" style="width: 200px;" /></td></tr>
						<tr><td width="255" style="height: 35px;">'.$this->l('AcquireBIN').'</td><td><input type="text" name="acquirer_bin" value="'.Tools::getValue('acquirer_bin', $this->acquirer_bin).'" style="width: 200px;" /></td></tr>
						<tr><td width="255" style="height: 35px;">'.$this->l('TerminalID').'</td><td><input type="text" name="terminal_id" value="'.Tools::getValue('terminal_id', $this->terminal_id).'" style="width: 80px;" /></td></tr>
						<tr><td width="255" style="height: 35px;">'.$this->l('Clave secreta de encriptación').'</td><td><input type="text" name="clavecifrado" value="'.Tools::getValue('clavecifrado', $this->clavecifrado).'" style="width: 200px;" /></td></tr>					
					</table>
				</fieldset>
			<br>
				<input class="button" name="btnSubmit" value="'.$this->l('Guardar configuración').'" type="submit" />
			</form>';
	}

	public function getContent(){
		if (Tools::isSubmit('btnSubmit')){
			$this->_postValidation();
			if (!count($this->_postErrors)){				
				$this->_postProcess();
			}else{
				foreach ($this->_postErrors as $err){
					$this->html .= $this->displayError($err);
				}
			}
		}else{
			$this->html .= '<br />';
		}
		$this->_displayCecamodule();
		$this->_displayForm();
		return $this->html;
	}

	public function hookPaymentOptions($params){
		if (!$this->active){
			return;
		}
		if (!$this->checkCurrency($params['cart'])){
			return;
		}

		// Coste de compra
			$currency = new Currency($params['cart']->id_currency);
			$cantidad = number_format($params['cart']->getOrderTotal(true, Cart::BOTH), 2, '', '');
			$cantidad = (int)$cantidad;

		// Identificador de compra (Número de pedido): id_Carrito + hora actual
			$orderId = $params['cart']->id;
			if(isset($_COOKIE["P".$orderId])){
				$sec_pedido = $_COOKIE["P".$orderId];
			}else{
				$sec_pedido = -1;
			}
			if($sec_pedido<9){
				setcookie("P".$orderId, ++$sec_pedido, time() + 86400); // 24 horas
			}
			$numpedido = str_pad($orderId.$sec_pedido, 12, "0", STR_PAD_LEFT); 

		// ISO Moneda
			$moneda = $currency->iso_code_num;

		// Descripciones de productos
			$products = $params['cart']->getProducts();
			$productos = '';
			foreach ($products as $product){
				$productos .= $product['quantity'].' '.Tools::truncate($product['name'], 50).' ';			
			}
			$productos = str_replace("%","&#37;",$productos);

		// Protocolo
			$protocolo = 'http://';


		// Variable cliente
			$customer = new Customer($params['cart']->id_customer);
			$id_cart = (int)$params['cart']->id;		
			$miObj = new CECA;
			$miObj->setParameter("MerchantID",$this->merchant_id);
			$miObj->setParameter("AcquirerBIN",$this->acquirer_bin);
			$miObj->setParameter("TerminalID",$this->terminal_id);
			$miObj->setParameter("Num_operacion",strval($numpedido));
			$miObj->setParameter("Importe",$cantidad);
			$miObj->setParameter("TipoMoneda",$moneda);
			$miObj->setParameter("URL_OK",$protocolo.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$id_cart.'&id_module='.$this->id.'&id_order='.$this->currentOrder.'&key='.$customer->secure_key);
			$miObj->setParameter("URL_NOK",$protocolo.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'pedido');
		
		// Se generan los parámetros de la petición
			$request = "";
			$signature = $miObj->createMerchantSignature($this->clavecifrado);

			$this->smarty->assign(array(
				'urltpv'          => $this->urltpv,
				'MerchantID'      => $this->merchant_id,
				'AcquirerBIN'     => $this->acquirer_bin,
				'TerminalID'      => $this->terminal_id,
				'exponente'       => '2',
				'TipoMoneda'      => $moneda,
				'URL_OK'          => $protocolo.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$id_cart.'&id_module='.$this->id.'&id_order='.$this->currentOrder.'&key='.$customer->secure_key,
				'URL_NOK'         => $protocolo.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'pedido',
				'Num_operacion'   => $numpedido,
				'Importe'         => $cantidad,
				'Descripcion'     => $productos,
				'Firma'           => $signature,
				'Cifrado'		  => 'SHA2',
				'Pago_soportado'  => 'SSL'
			));
			$payment_options = [];
			$new_option = new PaymentOption();
			$new_option->setCallToActionText($this->l('Paga con Tarjeta ',array(), 'Modules.Cecamodule.Admin'))
			->setInputs([
				'MerchantId'=>[
					'name'=>'MerchantID',
					'type'=>'hidden',
					'value'=>$this->merchant_id
				],
				'AcquirerBIN'=>[
					'name'=>'AcquirerBIN',
					'type'=>'hidden',
					'value'=>$this->acquirer_bin
				],				
				'TerminalID'=>[
					'name'=>'TerminalID',
					'type'=>'hidden',
					'value'=>$this->terminal_id
				],			
				'Exponente'=>[
					'name'=>'Exponente',
					'type'=>'hidden',
					'value'=>'2'
				],			
				'TipoMoneda'=>[
					'name'=>'TipoMoneda',
					'type'=>'hidden',
					'value'=>$moneda
				],			
				'URL_OK'=>[
					'name'=>'URL_OK',
					'type'=>'hidden',
					'value'=>$protocolo.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$id_cart.'&id_module='.$this->id.'&id_order='.$this->currentOrder.'&key='.$customer->secure_key
				],			
				'URL_NOK'=>[
					'name'=>'URL_NOK',
					'type'=>'hidden',
					'value'=>$protocolo.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'pedido'
				],		
				'Num_operacion'=>[
					'name'=>'Num_operacion',
					'type'=>'hidden',
					'value'=>$numpedido
				],		
				'Importe'=>[
					'name'=>'Importe',
					'type'=>'hidden',
					'value'=>$cantidad
				],		
				'Descripcion'=>[
					'name'=>'Descripcion',
					'type'=>'hidden',
					'value'=>$productos
				],	
				'Firma'=>[
					'name'=>'Firma',
					'type'=>'hidden',
					'value'=>$signature
				],
				'Cifrado'=>[
					'name'=>'Cifrado',
					'type'=>'hidden',
					'value'=>'SHA2'
				],
				'Pago_soportado'=>[
					'name'=>'Pago_soportado',
					'type'=>'hidden',
					'value'=>'SSL'
				]
				'Pago_elegido'=>[
					'name'=>'Pago_elegido',
					'type'=>'hidden',
					'value'=>'SSL'
				]				
			])
			->setLogo(_MODULE_DIR_.'cecamodule/views/img/ceca-logo.png')
			->setModuleName($this->name)
			->setAction($this->urltpv);
			$payment_options[] = $new_option;
        	return $payment_options;	
	}

	public function checkCurrency($cart){
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);
		if (is_array($currencies_module)){
			foreach ($currencies_module as $currency_module){
				if ($currency_order->id == $currency_module['id_currency']){
					return true;
				}
			}
		}
		return false;
	}

	public function hookPaymentReturn($params){
		if(!$this->active){
			return;
		}

		$this->smarty->assign(array(
			'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
			'status' => 'ok',
			'id_order' => $params['order']->id,
			'this_path' => $this->_path
		));
		if (isset($params['order']->reference) && !empty($params['order']->reference)) {
            $this->smarty->assign('reference', $params['order']->reference);
        }
		return $this->display(__FILE__, 'payment_return.tpl');
	}

}  
?>
