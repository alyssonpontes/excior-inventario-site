<?php

require_once('dbconn.php');

if (isset($_POST['metodo'])){

	$var = $_POST;
	$comando = $_POST['metodo'] . '($var);';
	eval($comando);

}

function validarUsuario($var){
	
	$email = $var['email'];
	$senha = $var['senha'];
	if ( empty($email) or empty($senha) ) {
			
		$erro = [
				"erro" => true,
				"erro_desc" => 'E-mail ou senha inválido!'
				];
		
		$erro=array_map('utf8_encode',$erro);
		echo json_encode($erro);
		
	}else{

		$senha = base64_encode($senha);
			
		global $dbconn;
		$sql = "SELECT id_usuario, nome
				FROM usuarios
				WHERE email = :email
				AND senha = :senha";
		
		$stmt = $dbconn->prepare($sql);
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':senha', $senha);
		
		$result = $stmt->execute();
		$count = $stmt->rowCount();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);	
		
		if ($count == 0){
			
			$erro = [
				"erro" => true,
				"erro_desc" => 'E-mail ou senha inválido!'
				];
			$erro=array_map('utf8_encode',$erro);
			echo json_encode($erro);
			
		}else{
		
			$erro = [
					"erro" => false,
					"erro_desc" => ''
					];
			$erro=array_map('utf8_encode',$erro);
			$retUsuario = array_merge($data, $erro);
			echo json_encode($retUsuario);	
		}
	}
}

?>