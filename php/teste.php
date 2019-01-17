<?php

require_once('dbconn.php');

function auxValidarEmail($email){

	global $dbconn;
	$sql = "SELECT id_usuario, nome, chave_nova_senha_valida, chave_nova_senha
			FROM usuarios
			WHERE email = :email";
	
	$stmt = $dbconn->prepare($sql);
	$stmt->bindParam(':email', $email);
		
	$result = $stmt->execute();
	$count = $stmt->rowCount();
	$data = $stmt->fetch(PDO::FETCH_ASSOC);	
	
	if ($count == 0){
		
				
	}else{
	

	}
	
	//echo json_encode($retUsuario);	
	
}

auxValidarEmail('alyssonpontes@gmail.com');
	
?>