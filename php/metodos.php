<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('dbconn.php');

require_once('mailconn.php');

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
		$mail->addAddress($email);
		$mail->addReplyTo('naoresponda@excior.com.br', 'Excior Inventário');
		
		//Content
		$mail->isHTML(true);
		$mail->Subject = 'Recuperaçao de Senha';
		
		$token = auxValidarEmail($email);
		if ($token['erro']){
			
			$erro = [
				"erro" => true,
				"erro_desc" => $token['erro_desc']
			];
			
		} else {
			
			$body = '<!DOCTYPE html>';
			$body .= '<html><head><title>Recuperaçao de Senha</title></head><body>';
			$body .= '<h3>Recuperaçao de Senha do Sistema de Inventário.</h3>';
			$body .= '<p>Para continuar com a recuperaçao de senha, clique no link abaixo para que seja lhe seja gerada uma senha temporária.</p>';
			$body .= '<p>Caso nao foi voce que solicitou a alteraçao de senha, por favor descarte essa mensagem.</p>';
			$body .= '<p><h3><a href="http://www.excior.com.br/inventario/php/recuperar.php?token=' . urlencode($token['chave']) . '">GERAR NOVA SENHA</a></h3></p>';	
			$body .= '</body></html>';
					
			$mail->Body = $body;

			$mail->send();

			$erro = [
				"erro" => false,
				"erro_desc" => "Mensagem enviada com sucesso!"
			];
		
		}
		
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

function gerarNovaSenhaUsuario($email){
	
	$senha = auxGerarNovaSenha();
	$senha_base64 = base64_encode($senha);	
	$situacao = 'Ativo';
	
	global $dbconn;
	$sql = "UPDATE usuarios 
			SET senha = :senha, situacao = :situacao
			WHERE email = :email";

	$stmt = $dbconn->prepare($sql);
	$stmt->bindParam(':email', $email);
	$stmt->bindParam(':situacao', $situacao);
	$stmt->bindParam(':senha', $senha_base64);
		
	$result = $stmt->execute();
	$count = $stmt->rowCount();		
				
	if ($count == 0){
		
		$ret = [
			"erro" => true,
			"erro_desc" => 'Erro ao gerar a nova senha, contate o administrador!',
			"nova_senha" => ''
		];
		
	}else{
		
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
			$mail->addAddress($email);
			$mail->addReplyTo('naoresponda@excior.com.br', 'Excior Inventário');
			
			//Content
			$mail->isHTML(true);
			$mail->Subject = 'Nova Senha';

			
			$body = '<!DOCTYPE html>';
			$body .= '<html><head><title>Nova Senha</title></head><body>';
			$body .= '<h3>Nova Senha do Sistema de Inventário.</h3>';
			$body .= '<p>Sua nova senha está logo abaixo, por favor assim que possivel troque a senha por uma sua de facil memorizaçao dentro do sistema de inventário.</p>';
			$body .= '<p><h1><pre>' . $senha . '</pre></h1></p>';	
			$body .= '</body></html>';
					
			$mail->Body = $body;

			$mail->send();

			$ret = [
				"erro" => false,
				"erro_desc" => "Nova senha enviada com sucesso!"
			];
			
		} 
		catch (Exception $e) 
		{
			$ret = [
				"erro" => true,
				"erro_desc" => $mail->ErrorInfo
			];
		}
	
	}
	
	$ret = array_map('utf8_encode',$ret);
	return $ret;
	
}

function trocarSenhaUsuario($var){
	
	$senha = $var['nova_senha'];
	$senha_base64 = base64_encode($senha);	
	
	global $dbconn;
	$sql = "UPDATE usuarios 
			SET senha = :senha 
			WHERE id_usuario = :id_usuario";

	$stmt = $dbconn->prepare($sql);
	$stmt->bindParam(':id_usuario', $var['id_usuario']);
	$stmt->bindParam(':senha', $senha_base64);
		
	$result = $stmt->execute();
	$count = $stmt->rowCount();		
				
	if ($count == 0){
		
		$ret = [
			"erro" => true,
			"erro_desc" => 'Erro ao gravar a nova senha, contate o administrador!'
		];
		
	}else{
	
		$ret = [
			"erro" => false,
			"erro_desc" => 'Senha alterada!'
		];
	}
	$ret = array_map('utf8_encode',$ret);
	echo json_encode($ret);

}

function registrarUsuario($var){
	
	$valEmail = auxValidarEmailCadastrado($var['reg-email']);
	
	if ($valEmail){
	
		$ret = [
			"erro" => true,
			"erro_desc" => 'E-mail já cadastrado na nossa base, utilize o lembrar senha!'
		];
		
	} else {
		
		global $dbconn;
		$sql = "INSERT INTO usuarios
					(email, senha, nome, 
					empresa, endereco, endereco_numero, 
					endereco_complemento, endereco_cidade, endereco_uf, 
					endereco_cep, telefone, situacao, 
					chave_nova_senha, chave_nova_senha_valida)
				VALUES
					(:email, :senha, :nome, 
					:empresa, :endereco, :endereco_numero, 
					:endereco_complemento, :endereco_cidade, :endereco_uf, 
					:endereco_cep, :telefone, :situacao, 
					:chave_nova_senha, :chave_nova_senha_valida)";
		
		$senha = 'nova-senha';
		$situacao = 'Inativo';
		$chave_nova_senha = null;
		$chave_nova_senha_valida = 'Nao';
		
		$stmt = $dbconn->prepare($sql);
		$stmt->bindParam(':email', $var['reg-email']);
		$stmt->bindParam(':senha', $senha);
		$stmt->bindParam(':nome', $var['reg-nome']);
		$stmt->bindParam(':empresa', $var['reg-empresa']);
		$stmt->bindParam(':endereco', $var['reg-endereco']);
		$stmt->bindParam(':endereco_numero', $var['reg-numero']);
		$stmt->bindParam(':endereco_complemento', $var['reg-complemento']);
		$stmt->bindParam(':endereco_cidade', $var['reg-cidade']);
		$stmt->bindParam(':endereco_uf', $var['reg-uf']);
		$stmt->bindParam(':endereco_cep', $var['reg-cep']);
		$stmt->bindParam(':telefone', $var['reg-telefone']);
		$stmt->bindParam(':situacao', $situacao);
		$stmt->bindParam(':chave_nova_senha', $chave_nova_senha);
		$stmt->bindParam(':chave_nova_senha_valida', $chave_nova_senha_valida);
		
		$result = $stmt->execute();
		$count = $stmt->rowCount();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);	

		if ($count == 0){
			
			$ret = [
				"erro" => true,
				"erro_desc" => 'Erro ao inserir o registro, contate o administrador!',
			];
			
		}else{
			
			$email = $var['reg-email'];
	
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
				$mail->addAddress($email);
				$mail->addReplyTo('naoresponda@excior.com.br', 'Excior Inventário');
				
				//Content
				$mail->isHTML(true);
				$mail->Subject = 'Novo Cadastro';
				
				$token = auxValidarEmail($email);
				if ($token['erro']){
					
					$ret = [
						"erro" => true,
						"erro_desc" => $token['erro_desc']
					];
					
				} else {
					
					$body = '<!DOCTYPE html>';
					$body .= '<html><head><title>Novo Cadastro</title></head><body>';
					$body .= '<h3>Novo Cadastro - Sistema de Inventário.</h3>';
					$body .= '<p>Para continuar com a ativaçao do cadastro e geraçao da nova senha, clique no link abaixo .</p>';
					$body .= '<p><h3><a href="http://www.excior.com.br/inventario/php/recuperar.php?token=' . urlencode($token['chave']) . '">GERAR NOVA SENHA</a></h3></p>';	
					$body .= '</body></html>';
							
					$mail->Body = $body;

					$mail->send();

					$ret = [
						"erro" => false,
						"erro_desc" => "Registro efetuado com sucesso, foi lhe enviado um e-mail de ativaçao da conta!"
					];
				
				}
				
			} 
			catch (Exception $e) 
			{
				$ret = [
					"erro" => true,
					"erro_desc" => 'Usuário cadastrado, mas houve erro ao enviar o e-mail com a nova senha, contate o administrador!'
				];
			}
			
		}
		
	}
		
	$ret = array_map('utf8_encode',$ret);
	echo json_encode($ret);
	
}

//////////// @FUNÇOES ATUXILIARES /////////////////

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
		
		$ret = [
			"erro" => true,
			"erro_desc" => 'E-mail nao localizado no cadastro!',
			"chave" => ''
		];
		
	}else{
		
		$nova_chave = $email . '|' . date("d/m/Y|H:i:s");
		$nova_chave = base64_encode($nova_chave);
		$nova_chave_valida = 'Nao';
		
		
		$sql = "UPDATE usuarios 
				SET chave_nova_senha_valida = :chave_valida, chave_nova_senha = :chave 
				WHERE email = :email";

		$stmt = $dbconn->prepare($sql);
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':chave_valida', $nova_chave_valida);
		$stmt->bindParam(':chave', $nova_chave);
			
		$result = $stmt->execute();
		$count = $stmt->rowCount();		
				
		if ($count == 0){
			
			$ret = [
				"erro" => true,
				"erro_desc" => 'Erro ao gerar o token',
				"chave" => ''
			];
			
		}else{
			
			$ret = [
				"erro" => false,
				"erro_desc" => '',
				"chave" => $nova_chave
			];
			
		}
		
	}
	
	$ret = array_map('utf8_encode',$ret);
	return $ret;
	
}

function auxValidarToken($email, $token){
	
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
		
		$ret = [
			"erro" => true,
			"erro_desc" => 'E-mail nao localizado no cadastro!',
			"chave" => ''
		];
		
	}else{
		
		if ($token == $data['chave_nova_senha']){
			
			if ($data['chave_nova_senha_valida'] == 'Sim'){
				
				$ret = [
					"erro" => true,
					"erro_desc" => 'Token já validado!'
				];
				
			}else{
			
				$nova_chave_valida = "Sim";
				$sql = "UPDATE usuarios 
						SET chave_nova_senha_valida = :chave_valida
						WHERE email = :email";

				$stmt = $dbconn->prepare($sql);
				$stmt->bindParam(':email', $email);
				$stmt->bindParam(':chave_valida', $nova_chave_valida);
					
				$result = $stmt->execute();
				$count = $stmt->rowCount();		
						
				if ($count == 0){
					
					$ret = [
						"erro" => true,
						"erro_desc" => 'Erro ao validar o token.'
					];
					
				}else{
					
					$ret = [
						"erro" => false,
						"erro_desc" => 'Chave validada, voce receberá um e-mail com a sua nova senha!'
					];

				}
			}
			
		}else{
			
			$ret = [
					"erro" => true,
					"erro_desc" => 'Token inválido, por favor solicite novamente a recuperaçao do e-mail.'
				];
			
		}	
		
	}
	
	$ret = array_map('utf8_encode',$ret);
	return $ret;
	
}

function auxGerarNovaSenha($length = 8) {
	
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    
	for ($i = 0; $i < $length; $i++) {
		
        $randomString .= $characters[rand(0, $charactersLength - 1)];
		
    }
	
    return $randomString;
	
}

function auxValidarEmailCadastrado($email){
	
	global $dbconn;
	$sql = "SELECT id_usuario, nome
			FROM usuarios
			WHERE email = :email";
	
	$stmt = $dbconn->prepare($sql);
	$stmt->bindParam(':email', $email);
	
	$result = $stmt->execute();
	$count = $stmt->rowCount();
	$data = $stmt->fetch(PDO::FETCH_ASSOC);	
	
	if ($count == 0){
		
		return false;
		
	}else{
	
		return true;
	
	}
	
}

?>