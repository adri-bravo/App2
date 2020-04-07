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
	$query = "SELECT _id,Proveedor,Producto,Uds,Um,Importe as 'Importe' from (SELECT _id,Proveedor,Producto,Uds,Um,Importe from (select 1 as '_id',PedidosC.Proveedor,PedidosP.Producto, Sum(PedidosP.Uds) as 'Uds',PedidosP.Um, round(Sum(PedidosP.Importe),2) as 'Importe' from PedidosP inner join PedidosC on PedidosP.Id_Pedido = PedidosC.Id_Pedido and PedidosC.Id_Usuario = PedidosP.Id_Usuario ". $l_where ." and PedidosC.Id_Usuario = ".$GL_id_usuario." group by PedidosC.Proveedor, PedidosP.Producto, PedidosP.Um order by Uds DESC) AS T
	UNION ALL SELECT _id,Proveedor,CONCAT(Producto,'(', COALESCE(Um,''),'):') as Producto,Uds,Um,Importe from (select 1 as '_id','' as Proveedor,'Total' as 'Producto', Sum(PedidosP.Uds) as 'Uds',(SELECT CASE (PedidosP.Um)  WHEN '' THEN ' ' ELSE PedidosP.Um  END) as 'Um', round( Sum(PedidosP.Importe),2) as 'Importe' from PedidosP inner join PedidosC on PedidosP.Id_Pedido = PedidosC.Id_Pedido and PedidosC.Id_Usuario = PedidosP.Id_Usuario ". $l_where ." and PedidosC.Id_Usuario = ".$GL_id_usuario." group by PedidosP.Um) as B
	UNION ALL SELECT 1 as '_id','' as Proveedor,'TOTAL:' as Producto,'' as Uds,'' as Um,COALESCE(round(Sum(PedidosP.Importe),2),0.0) as 'Importe' from PedidosP inner join PedidosC on PedidosP.Id_Pedido = PedidosC.Id_Pedido and PedidosC.Id_Usuario = PedidosP.Id_Usuario ". $l_where ." and PedidosC.Id_Usuario = ".$GL_id_usuario.") AS C" ;

	$result = mysql_query($query,$link) or die('Error en consulta'.mysql_error());

	/* create one master array of the records */
	$posts = array();
	if(mysql_num_rows($result)>0) {
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