{*
*
*
*
*
*
*
*}

<img src="{$this_path|escape:'htmlall'}views/img/ceca-logo.png" /><br /><br />
{if $status == 'ok'}
	<p>
	{l s='Su pedido en %s se ha completado.' sprintf=[$shop_name] mod='cecamodule'}
		<br /><br />- {l s='Cantidad pagada.' mod='cecamodule'} <span class="price"><strong>{$total_to_pay|escape:'htmlall'}</strong></span>
		<br /><br />- N# <span class="price"><strong>{$id_order|escape:'htmlall'}</strong></span>
		<br /><br />{l s='Para más preguntas o dudas, contacte con nosotros ' mod='cecamodule'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='servicio de atención al cliente.' mod='cecamodule'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='Existe un problema con su pedido. Si cree que esto es un error, puede contactar con nosotros ' mod='cecamodule'} 
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='servicio de atención al cliente.' mod='cecamodule'}</a>.
	</p>
{/if}