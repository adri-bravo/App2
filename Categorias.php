<?php
/* connect to the db */
require_once 'config.php';
require_once 'functions.php';
$json = file_get_contents('php://input');
$obj = json_decode($json,true);

foreach ($obj['Login'] as $k=>$login)
{	
    $email = $login['email'];
    $password = $login['pass']; // La contraseña con hash
}
if (isset($email, $password)) {
 
    if (login($email, $password) == true) {


	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die('Cannot connect to the DB');
	mysql_select_db(DB_NAME,$link) or die('Cannot select the DB');

	/* grab the posts from the db */
	$query = "SELECT Categoria from Stock where id_usuario = ".$GL_id_usuario." and Categoria <> '' group by Categoria order by Categoria ASC";
	$result = mysql_query($query,$link) or die('Errant query:  '.$query);

	/* create one master array of the records */
	$posts = array();
	if(mysql_num_rows($result)) {
		while($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
			$resultado = "OK";

		}
	}else
	 {
		$resultado = "OK";
		//$lista_errores[] = array('Error' =>"Error al buscar Categorías");
			 
	 }
 mysql_close($link);
	}
else
{
	$lista_errores[] = array('Error' =>"Error al iniciar sesión");
	$resultado = "NOOK";
	
}
}else
{
	$lista_errores[] = array('Error' =>"No se ha iniciado sesión");
	$resultado = "NOOK";
}


$exportar[] =array('Resultado'=>$resultado);
$exportar[] =array('Errores'=>$lista_errores);
$exportar[] =array('Datos'=>$posts);

echo json_encode(array('Lista'=>$exportar));
?>