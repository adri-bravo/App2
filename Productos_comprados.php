<?php
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

	/* grab the posts from the db */
	$query = "select P.Producto from PedidosP as P inner join PedidosC as C on C.Id_Pedido = P.Id_pedido where C.Id_Usuario = ".$GL_id_usuario." group by Producto order by Producto  ASC ";
	$result = mysql_query($query,$link) or die('Error en consulta');

	/* create one master array of the records */
	$posts = array();
	if(mysql_num_rows($result)) {
		while($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
			$resultado = "OK";
		}
	}else
	{
	$resultado = "NOOK";
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