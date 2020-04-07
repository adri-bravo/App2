<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die('Cannot connect to the DB');
	mysql_select_db(DB_NAME,$link) or die('Cannot select the DB');
$json = file_get_contents('php://input');
$obj = json_decode($json,true);
foreach ($obj['Login'] as $k=>$login)
{	
    $email = $login['email'];
    $password = $login['pass']; // La contraseña con hash
}

if (isset($email, $password)) {
 
		if (login($email, $password) == true) {
		$resultado = "OK";
		
		}else
		{
			$resultado = "NOOK";
			$lista_errores[] = array('Error' =>"Error al iniciar sesión");
		}
}
else
{
		$lista_errores[] = array('Error' =>"No se han indicado las credenciales");
}
$exportar[] =array('Resultado'=>$resultado);
$exportar[] =array('Errores'=>$lista_errores);
$exportar[] =array('Tipo'=>$_SESSION['tipo']);
echo json_encode(array('Lista'=>$exportar));

?>