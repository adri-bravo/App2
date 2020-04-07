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
		$id_recuento = -1;
		$sql0 = "SELECT Id_Recuento FROM RecuentosC where Id_Usuario = ". $GL_id_usuario." order by Id_Recuento DESC limit 1";
		$result = mysql_query($sql0,$link);
		if ($result !== false) {
			$row=mysql_fetch_array($result);
	
			$id_recuento = $row['Id_Recuento']+1;
			if ($index > 1){
			$l_where = $l_where . " or Id_Recuento = " . $id_recuento;
				
			}else
			{
			$l_where = " where Id_Recuento = " . $id_recuento;
			}
	
			$salt = 'anota';
			$Id_Externo = hash('sha512', 'Rec'.$GL_id_usuario. $id_recuento . $salt);
                      
			
$sql1 = "INSERT INTO RecuentosC(Id_Usuario, Id_Recuento,Fecha, Notas,Id_Externo,Ped_creado,Id_Pedido)VALUES(".$GL_id_usuario.",".$id_recuento.",'".$cabecera["Fecha"]."','".$cabecera["Notas"]."','".$Id_Externo."','0','0')";
		 if (mysql_query( $sql1,$link)==true)
		 {
	
			if(mysql_affected_rows()==-1){
				$errores = $errores +1;
				$lista_errores[] = array('Error' =>mysql_error());
				break;
				}
		 }
		 else{$errores = $errores +1;$lista_errores[] = array('Error' =>mysql_error());
		 break;
		 }
		
			$id_pos = 0;
			foreach ($obj['Detalles'] as $D=>$detalle)
			{	
				//if ($cabecera{'Proveedor'}==$detalle{'Proveedor'}){
				$id_pos = $id_pos + 1;
							$sql2 = "INSERT INTO RecuentosP(Id_Usuario,Id_Recuento,Pos_Recuento,Proveedor,Producto,Uds,Um,Referencia)VALUES(".
							$GL_id_usuario.",".
							$id_recuento.
							",".$id_pos.
							",'".$detalle{'Proveedor'}.
							"','".$detalle{'Producto'}.
							"',".$detalle{'Uds'}.
							",'".$detalle{'Um'}.
							"','".$detalle{'Referencia'}."')";
					if (mysql_query( $sql2,$link)==true)
					 {
						if(mysql_affected_rows()==-1){
							$errores = $errores +1;
							$lista_errores[] = array('Error' =>mysql_error());
							break;
							}
					 }
					 else{$errores = $errores +1;$lista_errores[] = array('Error' =>"aa".mysql_error());
					 break;
					 }
	
				
				//}
			}
		}
	}
	
	if($errores == 0){
		mysql_query("COMMIT");
		$resultado = "OK";
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