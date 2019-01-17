<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('dbconn.php');

$mail_host = 'mail.excior.com.br';
$mail_smtpauth = true;
$mail_username = 'naoresponda@excior.com.br';
$mail_password = '6W06)w?hN6x1';
$mail_smtpsecure = 'ssl';
$mail_port = 465;
$mail_from = 'naoresponda@excior.com.br';
$mail_from_name = 'Excior';

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

function recuperarSenhaUsuario($var){
	

	
	$email = $var['email'];
	
	require_once 'PHPMailer/src/PHPMailer.php';
	require_once 'PHPMailer/src/SMTP.php';
	require_once 'PHPMailer/src/Exception.php';
	
	$mail = new PHPMailer(true);
	try {
		//Server settings
		$mail->SMTPDebug = 0;
		$mail->isSMTP();
		$mail->Host = $GLOBALS['mail_host'];
		$mail->SMTPAuth = $GLOBALS['mail_smtpauth'];
		$mail->Username = $GLOBALS['mail_username'];
		$mail->Password = $GLOBALS['mail_password'];
		$mail->SMTPSecure = $GLOBALS['mail_smtpsecure'];
		$mail->Port = $GLOBALS['mail_port'];

		//Recipients
		$mail->setFrom($GLOBALS['mail_from'], $GLOBALS['mail_from_name']);
		$mail->addAddress('alyssonpontes@gmail.com', 'Alysson');
		$mail->addReplyTo('naoresponda@excior.com.br', 'Excior');
		
		//Content
		$mail->isHTML(true);
		$mail->Subject = 'Teste de Envio de E-mail';
		$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		$mail->send();

		$erro = [
			"erro" => false,
			"erro_desc" => "Mensagem enviada com sucesso!"
		];
		
	} 
	catch (Exception $e) 
	{
		$erro = [
			"erro" => true,
			"erro_desc" => $mail->ErrorInfo
		];
	}
		
	$erro=array_map('utf8_encode',$erro);
	echo json_encode($erro);
	
}

function gerarNovaSenhaUsuario(){
	
}

function trocarSenhaUsuario(){
	
}

//////////// function auxiliares /////////////////

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


?>