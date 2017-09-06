{*
*
*
*
*
*
*
*}

<form action="{$urltpv|escape:'htmlall'}" method="post" id="CECA_form" class="hidden">	
	<input type="hidden" name="MerchantID" value="{$MerchantID|escape:'htmlall'}" />
	<input type="hidden" name="AcquirerBIN" value="{$AcquirerBIN|escape:'htmlall'}" />
	<input type="hidden" name="TerminalID" value="{$TerminalID|escape:'htmlall'}" />
	<input type="hidden" name="Exponente" value="{$exponente|escape:'htmlall'}" />
	<input type="hidden" name="TipoMoneda" value="{$tipomoneda|escape:'htmlall'}" />
	<input type="hidden" name="URL_OK" value="{$URL_OK|escape:'htmlall'}" />
	<input type="hidden" name="URL_NOK" value="{$URL_NOK|escape:'htmlall'}" />
	<input type="hidden" name="Num_operacion" value="{$Num_operacion|escape:'htmlall'}" />
	<input type="hidden" name="Importe" value="{$Importe|escape:'htmlall'}" />
	<input type="hidden" name="Descripcion" value="{$Descripcion|escape:'htmlall'}" />
	<input type="hidden" name="Firma" value="{$Firma|escape:'htmlall'}" />
	<input type="hidden" name="Cifrado" value="{$Cifrado|escape:'htmlall'}" />
	<input type="hidden" name="Pago_soportado" value="{$Pago_soportado|escape:'htmlall'}">
</form>