
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
		url: 'php/metodos.php',
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