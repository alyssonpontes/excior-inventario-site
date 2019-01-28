$( document ).ready(function() {
  
	$(".dropdown-trigger").dropdown({ 
		inDuration: 300,
		outDuration: 225,
		constrain_width: true, 
		hover: false, 
		gutter: 0, 
		belowOrigin: false,
		coverTrigger: false
	});
   
	$('.modal').modal();
  
	if (!sessionStorage.getItem("id_usuario") || !sessionStorage.getItem("nome")){
		sessionStorage.clear();
		window.location = "login.html";
	}

	$("#id_usuario").val(sessionStorage.getItem("id_usuario"));
	$("#nome").val(sessionStorage.getItem("nome"));
	
});

$('#sair').on('click',function(){
	
	sessionStorage.clear();
	window.location = "login.html";
	
});

$('#trocar-nova-senha').on('click',function(){
	
	var novaSenha = $('#nova-senha').val();
	var repretirNovaSenha = $('#repetir-nova-senha').val();
	
	if (!novaSenha || !repretirNovaSenha){

		M.toast({html: 'Informe a nova senha nos dois campos!'})
		if (!repretirNovaSenha) $('#repetir-nova-senha').focus();
		if (!novaSenha) $('#nova-senha').focus();
		return;

	} else {
		
		if (novaSenha == repretirNovaSenha){
			
			var dados = {metodo:'trocarSenhaUsuario', id_usuario:sessionStorage.getItem("id_usuario"), nova_senha:novaSenha};
			
			$.ajax({
				type: 'POST',
				url: 'php/servico.php',
				data: dados,
				success: function(response) {
					var obj = JSON.parse(response);
					if (obj['erro'] == true){
						M.toast({html: obj['erro_desc']});
					}else{
						$('#nova-senha').val('');
						$('#repetir-nova-senha').val('');
						$('#modal-trocar-senha').modal('close');
						M.toast({html: obj['erro_desc']});
					}
				},
				failure: function (response) {
					alert('Erro AJAX: ' + response);
				}
			});
			
		} else {
			
			M.toast({html: 'As senhas são diferentes!'})
			$('#repetir-nova-senha').focus();
			return;
			
		}
		
	}
	
	
});
