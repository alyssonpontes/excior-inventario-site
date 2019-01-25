<?php

require_once('metodos.php');

if (isset($_GET['token'])){

	$token = $_GET['token'];
	$chave = base64_decode($token);
	$email = substr($chave, 0, strpos($chave, '|'));
	
	$val = auxValidarToken($email, $token);
	
	if ($val['erro']){
		
		echo $val['erro_desc'];
	
	} else {
		
		$ret  = gerarNovaSenhaUsuario($email);
		if ($ret['erro']){
			
			echo $ret['erro_desc'];
			
		} else {
			
			echo $ret['erro_desc'];
			
		}
		
	}
	
}



?>