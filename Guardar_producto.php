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

if (isset($email, $password)) {
 
    if (login($email, $password) == true) {

mysql_query("START TRANSACTION");
$errores = 0;
foreach ($obj['Borrar'] as $k=>$producto)
{	
	 $sql = "DELETE  FROM Stock where Id_Usuario = ".$GL_id_usuario." and Id_producto = ". (int)$producto['Id_Producto'];
	 if ((mysql_query( $sql,$link))==true)
	 {
		if(mysql_affected_rows()==-1){
		 	$errores = $errores +1;
			$lista_errores[] = array('Error' =>mysql_error());
			}
	 }else{
		 $errores = $errores +1;
		 $lista_errores[] = array('Error' =>mysql_error());
		 }
}

foreach ($obj['Guardar'] as $k=>$producto)
{	

	if((int)$producto{'Id_Producto'} == -1){
		$id_producto = -1;
		$sql0 = "SELECT Id_producto FROM Stock where Id_Usuario = ".$GL_id_usuario." order by Id_producto DESC limit 1";
		$result = mysql_query($sql0,$link);
		if ($result !== false) {
			$row=mysql_fetch_array($result);
	
			$id_producto = $row['Id_producto']+1;
		}else{$errores = $errores +1;$lista_errores[] = array('Error' =>"Producto: ".$producto{'Nombre'}." ".mysql_error());
		 break;
		}
			if(isset($producto{'Uds_rapel'})){

				 $sql = "INSERT INTO Stock( Id_Usuario,Id_producto,Proveedor, Producto, Uds, Uds_rapel, Importe_ant,Importe, Unidad, Categoria, Referencia, CodProv,Fecha_rapel,Porcen,Fecha_precio) VALUES ("
				 .$GL_id_usuario.
				 ",".$id_producto.
				 ",'".$producto{'Proveedor'}.
				 "','".$producto{'Nombre'}.
				 "',".$producto{'Uds'}.
				 ",".$producto{'Uds_rapel'}.
				 ",0,".$producto{'Importe'}.
				 ",'".$producto{'Um'}.
				 "','".$producto{'Categoria'}.
				 "','".$producto{'Referencia'}.
				 "','".$producto{'CodProv'}.
				 "','".$producto{'Fecha_rapel'}.
				 "','".$producto{'Porcen'}.
				"','".$producto{'Fecha_precio'}."')";
				// "','0000-00-00 00:00:00')";
			}
			else
			{
				 $sql = "INSERT INTO Stock( Id_Usuario,Id_producto,Proveedor, Producto, Uds, Uds_rapel, Importe_ant,Importe, Unidad, Categoria, Referencia, CodProv,Fecha_rapel,Porcen,Fecha_precio) VALUES ("
				 .$GL_id_usuario. 
				 ",".$id_producto.
				 ",'".$producto{'Proveedor'}.
				 "','".$producto{'Nombre'}.
				 "',".$producto{'Uds'}.
				 ","."0".
				 ",0,".$producto{'Importe'}.
				 ",'".$producto{'Um'}.
				 "','".$producto{'Categoria'}.
				 "','".$producto{'Referencia'}.
				 "','".$producto{'CodProv'}.
				 "','".$producto{'Fecha_Rapel'}.
				 "','".$producto{'Porcen'}.
				"','".$producto{'Fecha_precio'}."')";
				// "','0000-00-00 00:00:00')";
				}
				$l_ok = true;
	}else
	{
		$l_ok = false;
			$query = "select Producto from Stock where Id_Usuario = ". $GL_id_usuario." and Id_producto = " . (int)$producto{'Id_Producto'};
			$result = mysql_query($query,$link) or die('Errant query:  '.$query);
		
			/* create one master array of the records */
			$posts = array();
			if(mysql_num_rows($result)) {
				while($post = mysql_fetch_assoc($result)) {
					if($post["Producto"] == $producto{'Nombre'}){
					$l_ok = true;
					}
					else{
					 $sql = "UPDATE PedidosP SET Producto = '".$producto{'Nombre'}."'".
									" where Id_Usuario = ". $GL_id_usuario." and Producto = '". $post["Producto"]."'".
									" and Id_Pedido in (Select Id_Pedido from PedidosC where Id_Usuario = ". $GL_id_usuario." and Proveedor = '".$producto{'Proveedor'}."')" ; 
						
						 if (mysql_query( $sql,$link)==true)
						 {
							if(mysql_affected_rows()==-1){
								$errores = $errores +1;
								$lista_errores[] = array('Error' =>"Producto:".$producto{'Nombre'});
	
								}
							else{
								$l_ok = true;
								}
						 }else
						 {
							 $errores = $errores +1;$lista_errores[] = array('Error' =>"Producto:".$producto{'Nombre'});
						 }
				
	
					}

				}
			}
		
			if(isset($producto{'Uds_rapel'})){
				 $sql = "UPDATE Stock SET Proveedor = '".$producto{'Proveedor'}."',".
									" Producto = '".$producto{'Nombre'}."',".
									" Uds = ".$producto{'Uds'}.",".
									" Uds_rapel = ".$producto{'Uds_rapel'}.",".
									" Fecha_rapel = '".$producto{'Fecha_rapel'}."',".
									" Porcen = '".$producto{'Porcen'}."',".
									" Fecha_precio = if (Importe <> ".$producto{'Importe'}.",'".$producto{'Fecha_precio'}."',Fecha_precio),".
									" Importe_ant = if (Importe <> ".$producto{'Importe'}.",Importe,Importe_ant),".
									" Importe = ".$producto{'Importe'}.",".
									" Unidad = '".$producto{'Um'}."',".
									" Categoria = '".$producto{'Categoria'}."',".
									" Referencia = '".$producto{'Referencia'}."',".
									" CodProv = (Select CodProv 
												  from Proveedores 
												 where Id_Usuario = ". $GL_id_usuario." and Alias = '".$producto{'Proveedor'}."')".
								" where Id_Usuario = ". $GL_id_usuario." and Id_producto = ". (int)$producto{'Id_Producto'} ; 
			}
			else
			{
				 $sql = "UPDATE Stock SET Proveedor = '".$producto{'Proveedor'}."',".
									" Producto = '".$producto{'Nombre'}."',".
									" Uds = ".$producto{'Uds'}.",".
									" Fecha_precio = if (Importe <> ".$producto{'Importe'}.",'".$producto{'Fecha_precio'}."',Fecha_precio),".
									" Importe_ant = if (Importe <> ".$producto{'Importe'}.",Importe,Importe_ant),".
									" Importe = ".$producto{'Importe'}.",".
									" Unidad = '".$producto{'Um'}."',".
									" Categoria = '".$producto{'Categoria'}."',".
									" Referencia = '".$producto{'Referencia'}."',".
									" CodProv = (Select CodProv 
												  from Proveedores 
												 where Id_Usuario = ". $GL_id_usuario." and Alias = '".$producto{'Proveedor'}."')".
								" where Id_Usuario = ". $GL_id_usuario." and Id_producto = ". (int)$producto{'Id_Producto'} ; 
			
			}
		}
		if ($l_ok == true){
		 if (mysql_query( $sql,$link)==true)
		 {
			if(mysql_affected_rows()==-1){
				$errores = $errores +1;
				$lista_errores[] = array('Error' =>"Producto:".$producto{'Nombre'});
				}
		 }else{$errores = $errores +1;$lista_errores[] = array('Error' =>"Producto:".$producto{'Nombre'}.mysql_error());}
		}
	
}

if($errores == 0){
	$resultado = "OK";
	mysql_query("COMMIT");
}else
{
	$resultado = "NOOK";
	mysql_query("ROLLBACK");
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
echo json_encode(array('Lista'=>$exportar));

?>