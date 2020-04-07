<?php
require_once 'config.php';
require_once 'functions.php';
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die('Cannot connect to the DB');
	mysql_select_db(DB_NAME,$link) or die('Cannot select the DB');
$json = file_get_contents('php://input');
$obj = json_decode($json,true);


//$lista_errores[] =array('Error' =>"");
$lista_errores = [];

foreach ($obj['Login'] as $k=>$login)
{	
    $email = $login['email'];
    $password = $login['pass']; // La contraseña con hash
}
if(isset($obj['Find'])){
	foreach ($obj['Find'] as $f=>$dato)
	{	
		$find = $dato;
	}
}


$l_where = "";
if (isset($email, $password)) {
 
    if (login($email, $password) == true) {

			if(isset($find)){
				$l_where = urldecode($find);
			}
			/* grab the posts from the db */
			if($l_where == ""){
				$query = "SELECT *,'' as Pass from Usuarios order by Nombre ASC";
			}
			else
			{
				$query = "SELECT *,'' as Pass from Usuarios" . $l_where . " order by Nombre ASC";
			}
				$result = mysql_query($query,$link) or die('Error en consulta'.mysql_error());
			
				/* create one master array of the records */
				$posts = array();
				if(mysql_num_rows($result)) {
					$i = 0;
					while($post = mysql_fetch_assoc($result)) {
					$posts[] = $post;
					$resultado = "OK";
					}
				}else
				{
				$resultado = "NOOK";
				$lista_errores[] = array('Error' =>"Error al buscar Usuarios");

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
//echo json_encode(array('Datos'=>$posts));
?>