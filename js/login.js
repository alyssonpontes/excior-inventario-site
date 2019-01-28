$(document).ready(function(){
	$('.modal').modal();
});

$('#btnEntrar').on('click',function(){
	
	var email = $('#email').val();
	var senha = $('#senha').val();
	
	if (!email || !senha){
		M.toast({html: 'Informe o e-mail e senha!'})
		if (!senha) $('#senha').focus();
		if (!email) $('#email').focus();
		return;
	}
	
	var usuario = {metodo:'validarUsuario', email:email, senha:senha};

	$.ajax({
		type: 'POST',
		url: 'php/servico.php',
		data: usuario,
		success: function(response) {
			var obj = JSON.parse(response);
			if (obj['erro'] == true){
				M.toast({html: obj['erro_desc']});
			}else{
				sessionStorage.setItem("id_usuario", obj['id_usuario']);
				sessionStorage.setItem("nome", obj['nome']);
				window.location = "principal.html";
			}
		},
		failure: function (response) {
			alert('Erro AJAX: ' + response);
		}
	});
	
});

$('#btnEnviar').on('click',function(){
	
	var email = $('#email-recup').val();
	var dados = {metodo:'recuperarSenhaUsuario', email:email};
	
	$.ajax({
		type: 'POST',
		url: 'php/servico.php',
		data: dados,
		success: function(response) {
			var obj = JSON.parse(response);
			if (obj['erro'] == true){
				M.toast({html: obj['erro_desc']});
			}else{
				$('#email-recup').val('');
				M.toast({html: obj['erro_desc']});
				$('#modal-recuperar-senha').close();
			}
		},
		failure: function (response) {
			alert('Erro AJAX: ' + response);
		}
	});
	
});	

$('#btnRegistrar').on('click',function(){
	
	var registroValido = true;
	var registroValidoObj;
	var registroValores = "{";
	
	$(".registre-se").each(function(){
		
		if (registroValido && $(this)[0].checkValidity() === false){
			
			registroValido = false;
			registroValidoObj = $(this)
		
		}
		
		registroValores += "\"" + $(this).attr('id') + "\":\"" + $(this).val() + "\",";
	
	});
	
	registroValores = registroValores.substring(0, registroValores.length - 1);
	registroValores += "}";
	registroValores = JSON.parse(registroValores);
	
	if (registroValido){
		
		var dados = {metodo:'registrarUsuario', };
		dados = {...dados, ...registroValores};
		
		$.ajax({
			type: 'POST',
			url: 'php/servico.php',
			data: dados,
			success: function(response) {
				var obj = JSON.parse(response);
				if (obj['erro'] == true){
					M.toast({html: obj['erro_desc']});
				}else{
					M.toast({html: obj['erro_desc']});
					$('#modal-registre-se').close();
				}
			},
			failure: function (response) {
				alert('Erro AJAX: ' + response);
			}
		});
		
	} else {
		
		var label = $("label[for='" + registroValidoObj.attr('id') + "']");
		M.toast({html: 'Erro de preenchimento no campo:' + label.text()});
		registroValidoObj.focus();
		
	}
	
});




