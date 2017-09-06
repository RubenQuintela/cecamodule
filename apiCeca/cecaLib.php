<?php 

/****************************--COMPROBACIONES--********************************************/

	function checkNombreComecioCECA($nombre) {
		return preg_match("/^\w*$/", $nombre);
	}

/****************************--LOG--********************************************/

	function generateIdLogCECA(){
	    $vars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $stringLength = strlen($vars);
	    $result = '';
	    for ($i = 0; $i < 20; $i++) {
	        $result .= $vars[rand(0, $stringLength - 1)];
	    }
	    return $result;
	}

	function writeLog($text,$status){
		if($status==true){
			// Log
			$logfilename = 'logs/CECA_log.log';
			$fp = @fopen($logfilename, 'a');
			if($fp){
				fwrite($fp, date('d-m-Y h:i:s') . ' -- ' . $text . "\r\n");
				fclose($fp);
			}
		}
	}

?>