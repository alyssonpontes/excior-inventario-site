<?php

require_once('metodos.php');

if (isset($_POST['metodo'])){

	$var = $_POST;
	$comando = $_POST['metodo'] . '($var);';
	eval($comando);

}


?>