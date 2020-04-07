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
	
	$index = $index + 1;
		$id_pedido = -1;
		$sql0 = "SELECT Id_Pedido FROM PedidosC where Id_Usuario = ".$GL_id_usuario." order by Id_Pedido DESC limit 1";
		$result = mysql_query($sql0,$link);
		if ($result !== false) {
			$row=mysql_fetch_array($result);
	
			$id_pedido = $row['Id_Pedido']+1;
			if ($index > 1){
			$l_where = $l_where . " or Id_Pedido = " . $id_pedido;
				
			}else
			{
			$l_where = " Id_Pedido = " . $id_pedido;
			}
	
			$salt = 'anota';
			$Id_Externo = hash('sha512', 'Ped'.$GL_id_usuario. $id_pedido . $salt);
                      
			if (isset($cabecera["Regalo"]))
			{
			$sql1 = "INSERT INTO PedidosC(Id_Usuario, Id_Pedido,Proveedor, Fecha, Importe,Importe_real, Fecha_prevista, Notas,Id_Externo,Recib_prov,Recib_cli,Regalo)VALUES(".$GL_id_usuario.",".$id_pedido.",'".$cabecera["Proveedor"]."','".$cabecera["Fecha"]."',".$cabecera["Importe"].",".$cabecera["Importe"].",'".$cabecera["Fecha_prevista"]."','".$cabecera["Notas"]."','".$Id_Externo."','0','0','".$cabecera["Regalo"]."')";
				
			}
			else	
			{		
			$sql1 = "INSERT INTO PedidosC(Id_Usuario, Id_Pedido,Proveedor, Fecha, Importe,Importe_real, Fecha_prevista, Notas,Id_Externo,Recib_prov,Recib_cli)VALUES(".$GL_id_usuario.",".$id_pedido.",'".$cabecera["Proveedor"]."','".$cabecera["Fecha"]."',".$cabecera["Importe"].",".$cabecera["Importe"].",'".$cabecera["Fecha_prevista"]."','".$cabecera["Notas"]."','".$Id_Externo."','0','0')";
			}
		 if (mysql_query( $sql1,$link)==true)
		 {
	
			if(mysql_affected_rows()==-1){
				$errores = $errores +1;
				$lista_errores[] = array('Error' =>"Proveedor:".$cabecera["Proveedor"]);
				break;
				}
		 }
		 else{$errores = $errores +1;$lista_errores[] = array('Error' =>"Proveedor:".$cabecera["Proveedor"].mysql_error());
		 break;
		 }
		
			$id_pos = 0;
			foreach ($obj['Detalles'] as $D=>$detalle)
			{	
				if ($cabecera{'Proveedor'}==$detalle{'Proveedor'}){
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
							$lista_errores[] = array('Error' =>"Proveedor:".$cabecera{'Proveedor'});
							break;
							}
					 }
					 else{$errores = $errores +1;$lista_errores[] = array('Error' =>"Proveedor:".$cabecera{'Proveedor'});
					 break;
					 }
	
				
				}
			}
		}
	}
	
	if($errores == 0){
		mysql_query("COMMIT");
		$resultado = "OK";
		$query3 = "SELECT * from PedidosC where Id_Usuario = ". $GL_id_usuario." and ( " . $l_where . " ) order by Id_Pedido ASC";
		$result2 = mysql_query($query3,$link) or die('Error en consulta');
		$lista_cabeceras = array();
		if(mysql_num_rows($result2)) {
			while($post = mysql_fetch_assoc($result2)) {
				$lista_cabeceras[] = $post;
			}
		}
	
		$query4 = "SELECT Id_Pedido, Producto, Uds, Importe, Um, Referencia from PedidosP where Id_Usuario = ". $GL_id_usuario." and (" . $l_where . " ) order by Id_Pedido ASC";
		$result3 = mysql_query($query4,$link) or die('Error en consulta');
		$lista_detalles = array();
		if(mysql_num_rows($result3)) {
			while($post = mysql_fetch_assoc($result3)) {
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

	$exportar[] =array('Cabeceras'=>$lista_cabeceras);
	$exportar[] =array('Detalles'=>$lista_detalles);
echo json_encode(array('Lista'=>$exportar));

?>