$( document ).ready(function() {
  
	$(".dropdown-trigger").dropdown({ 
		inDuration: 300,
		outDuration: 225,
		constrain_width: true, 
		hover: false, 
		gutter: 0, 
		belowOrigin: false
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
