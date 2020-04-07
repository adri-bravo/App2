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
				//if(Porcen=0,false,true) as Porcen,
				$query = "SELECT *, (Importe - Importe_ant) as Diff from Stock where id_usuario = ".$GL_id_usuario." order by Categoria ASC,Producto ASC";
			}
			else
			{
				$query = "SELECT *,(Importe - Importe_ant) as Diff from Stock " . $l_where . " and id_usuario = ".$GL_id_usuario." order by Categoria ASC,Producto ASC";
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
				$lista_errores[] = array('Error' =>"Error al buscar productos");

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