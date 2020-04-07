<?php
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
	mysql_query("START TRANSACTION");
	$errores = 0;
	$l_where = "";
	$index = 0;
	
	foreach ($obj['Cabeceras'] as $C=>$cabecera)
	{	
	
		$id_pedido = $cabecera["Id_Pedido"];
		$sql1 = "UPDATE PedidosC SET Importe = ".$cabecera["Importe"].",Importe_real = ".$cabecera["Importe_real"].", Fecha_prevista ='"
		.$cabecera["Fecha_prevista"]."', Fecha ='"
		.$cabecera["Fecha"]."', Notas ='".$cabecera["Notas"]."'"." where Id_Usuario = ". $GL_id_usuario." and Id_Pedido = " . $id_pedido;
		 if (mysql_query( $sql1,$link)==true)
		 {
			 $sql1 = "DELETE FROM PedidosP where Id_Usuario = ". $GL_id_usuario." and Id_Pedido = " .$id_pedido;
			if (mysql_query( $sql1,$link)==true)
		 	{
				$id_pos = 0;
				foreach ($obj['Detalles'] as $D=>$detalle)
				{	
					$id_pos = $id_pos + 1;
					$sql2 = "INSERT INTO PedidosP(Id_Usuario,Id_Pedido,Pos_Pedido,Producto,Uds,Importe,Um,Referencia)VALUES(".
					$GL_id_usuario.",".
					$id_pedido.
					",".$id_pos.
					",'".$detalle{'Producto'}.
					"',".$detalle{'Uds'}.
					",".$detalle{'Importe'}.
					",'".$detalle{'Um'}.
					"','".$detalle{'Referencia'}."')";
						if (mysql_query( $sql2,$link)==true)
						 {
							if(mysql_affected_rows()==-1){
								$errores = $errores +1;
								//$lista_errores[] = array('Error' =>"Proveedor:".$cabecera{'Proveedor'});
								break;
								}
						 }
						 else{
							 $errores = $errores +1;
							 //$lista_errores[] = array('Error' =>"Proveedor:".$cabecera{'Proveedor'});
							 break;
						 }
				}
			 }
		 	
		 }
		 else{$errores = $errores +1;$lista_errores[] = array('Error' =>"Proveedor:".$cabecera["Proveedor"].mysql_error());
		 }
		
	}
	
	if($errores == 0){
		mysql_query("COMMIT");
		$resultado = "OK";
	
		$query4 = "SELECT Id_Pedido, Producto, Uds, Importe, Um,Referencia from PedidosP where Id_Usuario = ". $GL_id_usuario." and Id_Pedido =" . $id_pedido. " order by Pos_Pedido ASC";
		$result3 = mysql_query($query4,$link) or die('Error en consulta');
		$lista_detalles = array();
		if(mysql_num_rows($result3)) {
			while($post = mysql_fetch_assoc($result3)) {
				$lista_detalles[] = $post;

			}
			$query = "select Id_Pedido,concat('-Total(',Um,')-') as Producto, sum(Uds) as Uds, sum(Importe) as Importe,Um,'' as Referencia from PedidosP where Id_Usuario = ". $GL_id_usuario." and Id_Pedido =" .$id_pedido . " group by Um order by Pos_Pedido ASC ";
			$result2 = mysql_query($query,$link) or die('Errant query:  '.$query);
			$l_importe = 0;
			$l_um = "";
			$l_um_string = "";
			$l_uds_string = "";
			$l_uds = 0;
			$num_rows=mysql_num_rows($result2);
			if($num_rows) {
				while($post = mysql_fetch_assoc($result2)) {
			
				if($num_rows > 1){
				$lista_detalles[] = $post;
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
				$post["Referencia"] ='';
				$lista_detalles[] = $post;
			}

		
		
		}
	}else
	{
		mysql_query("ROLLBACK");
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
$exportar[] =array('Datos'=>$lista_detalles);
echo json_encode(array('Lista'=>$exportar));

?>