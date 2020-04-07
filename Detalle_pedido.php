<?php
require_once 'config.php';
require_once 'functions.php';
/* connect to the db */

$link = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die('Cannot connect to the DB');
mysql_select_db(DB_NAME,$link) or die('Cannot select the DB');

$json = file_get_contents('php://input');
$obj = json_decode($json,true);
foreach ($obj['Login'] as $k=>$login)
{	
    $email = $login['email'];
    $password = $login['pass']; // La contraseña con hash
}

if(isset($obj['Pedido'])){
	foreach ($obj['Pedido'] as $f=>$dato)
	{	
		$id_pedido = $dato;
	}
}



if (isset($email, $password)) {
 
	if (login($email, $password) == true) {

		if(isset($id_pedido)) {
			/* grab the posts from the db */
			$query = "select Id_Pedido, Producto, Uds, Importe,Um,Referencia from PedidosP where Id_Usuario = ". $GL_id_usuario." and Id_Pedido = " . $id_pedido ." order by Pos_Pedido ASC";
			$result = mysql_query($query,$link) or die('Errant query:  '.$query);
		
			/* create one master array of the records */
			$posts = array();
			if(mysql_num_rows($result)) {
				while($post = mysql_fetch_assoc($result)) {
					$posts[] = $post;
					
				}
			}
			$query = "select Id_Pedido,concat('-Total(',Um,')-') as Producto, sum(Uds) as Uds,  ROUND(SUM(Importe), 2) as Importe,Um,'' as Referencia from PedidosP where Id_Usuario = ". $GL_id_usuario." and Id_Pedido = " 
			.$id_pedido . " group by Um order by Pos_Pedido ASC ";
			$result2 = mysql_query($query,$link) or die('Errant query:  '.$query);
			$l_importe = 0;
			$l_um = "";
			$l_um_string = "";
			$l_uds_string = "";
			$num_rows=mysql_num_rows($result2);
			if($num_rows) {
				while($post = mysql_fetch_assoc($result2)) {
			
				if($num_rows > 1){
				$posts[] = $post;
				}
		
				$l_importe = $l_importe + $post["Importe"];
				$l_uds = $l_uds + $post["Uds"];
				$l_um = $post["Um"];
				}
		
				if($num_rows == 1){
					
					$l_um_string = $l_um;
					$l_uds_string = $l_uds;
				
				}
				$post["Id_Pedido"] = $id_pedido;
				$post["Producto"] = "-Total Factura-";
				$post["Uds"] = $l_uds_string;	        
				$post["Importe"] = $l_importe;
				$post["Um"] = $l_um_string;	
				$post["Referencia"] = '';	
				$posts[] = $post;
			}


			$query = "SELECT * from Proveedores where id_usuario = ".$GL_id_usuario." order by Nombre ASC";
			$result = mysql_query($query,$link) or die('Error en consulta');
		
			/* create one master array of the records */
			$proveedores = array();
			if(mysql_num_rows($result)) {
				while($prov = mysql_fetch_assoc($result)) {
					$proveedores[] = $prov;
					$resultado = "OK";
				}
			}else
			{
				$resultado = "OK";
				//$lista_errores[] = array('Error' =>"Error al buscar Proveedores");
		
			}
			
			$query2 = "SELECT * from PedidosC where Id_Usuario = ". $GL_id_usuario." and Id_Pedido = " . $id_pedido ."";
			$result2 = mysql_query($query2,$link) or die('Error en consulta');
		
			/* create one master array of the records */
			$cabeceras = array();
			if(mysql_num_rows($result2)) {
				while($cab = mysql_fetch_assoc($result2)) {
					$cabeceras[] = $cab;
					$resultado = "OK";
				}
			}else
			{
				$resultado = "OK";
				//$lista_errores[] = array('Error' =>"Error al buscar Proveedores");
		
			}
			


		
			mysql_close($link);
			}
		else{
			$lista_errores[] = array('Error' =>"No se ha indicado ningún pedido.");
			$resultado = "NOOK";

			}
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
$exportar[] =array('Proveedor'=>$proveedores);
$exportar[] =array('Cabecera'=>$cabeceras);

echo json_encode(array('Lista'=>$exportar));

?>