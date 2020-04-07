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

if(isset($obj['Recuento'])){
	foreach ($obj['Recuento'] as $f=>$dato)
	{	
		$id_pedido = $dato;
	}
}



if (isset($email, $password)) {
 
	if (login($email, $password) == true) {

		if(isset($id_pedido)) {
			/* grab the posts from the db */
			$query = "select Id_Recuento,Proveedor, Producto, Uds, Um,Referencia from RecuentosP where Id_Usuario = ". $GL_id_usuario." and Id_Recuento = " . $id_pedido ." order by Pos_Recuento ASC";
			$result = mysql_query($query,$link) or die('Errant query:  '.$query);
		
			/* create one master array of the records */
			$posts = array();
			if(mysql_num_rows($result)) {
				while($post = mysql_fetch_assoc($result)) {
					$posts[] = $post;
					
				}
			}
			$query = "select Id_Recuento,concat('-Total(',Um,')-') as Producto, sum(Uds) as Uds,Um,'' as Referencia from RecuentosP where Id_Usuario = ". $GL_id_usuario." and Id_Recuento = " 
			.$id_pedido . " group by Um order by Pos_Recuento ASC ";
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
		
				$l_uds = $l_uds + $post["Uds"];
				$l_um = $post["Um"];
				}
		
				if($num_rows == 1){
					
					$l_um_string = $l_um;
					$l_uds_string = $l_uds;
				
				$post["Id_Recuento"] = $id_pedido;
				$post["Producto"] = "-Total Recuento-";
				$post["Uds"] = $l_uds_string;
				$post["Um"] = $l_um_string;	
				$post["Referencia"] = '';	
				$posts[] = $post;
				}
				$resultado = "OK";
			}

			mysql_close($link);
			}
		else{
			$lista_errores[] = array('Error' =>"No se ha indicado ningún recuento.");
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

echo json_encode(array('Lista'=>$exportar));

?>