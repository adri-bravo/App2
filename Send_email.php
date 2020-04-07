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

$resultado = "NOOK";

if (isset($email, $password)) {
 
	if (login($email, $password) == true) {

		if(isset($id_pedido)) {
			/* grab the posts from the db */
			$query = "select Id_Pedido, Producto, Uds, Importe,Um,Referencia from PedidosP where Id_Pedido = " . $id_pedido ." order by Pos_Pedido ASC";
			$result = mysql_query($query,$link) or die('Errant query:  '.$query);
		
			$mensaje = "";
			if(mysql_num_rows($result)) {
				while($post = mysql_fetch_assoc($result)) {
					$l_referencia = "";
					if (strlen($post["Referencia"]) > 0){
						$l_referencia = "[".$post["Referencia"]."] ";
						}
					$mensaje = $mensaje.$l_referencia. $post["Producto"]." = ".$post["Uds"]. $post["Um"]."<br>";
					
				}
			}
			$query = "select * from PedidosC where Id_Pedido = " .$id_pedido;
			$result2 = mysql_query($query,$link) or die('Errant query:  '.$query);
			$num_rows=mysql_num_rows($result2);
			if($num_rows) {
				while($PedidoC = mysql_fetch_assoc($result2)) {
			
				$Id_Externo = $PedidoC["Id_Externo"];
				$Proveedor = $PedidoC["Proveedor"];
				$Notas = $PedidoC["Notas"];
				$FPrevista = $PedidoC["Fecha_prevista"];
				}


				$query = "SELECT * from Proveedores where id_usuario = ".$GL_id_usuario." and Alias = '". $Proveedor ."' order by Nombre ASC";
				$result = mysql_query($query,$link) or die('Error en consulta');
			
				if(mysql_num_rows($result)) {
					while($prov = mysql_fetch_assoc($result)) {
						$email = $prov["Email"];
					}
				}

				$query = "SELECT * from Usuarios where id_usuario = ".$GL_id_usuario;
				$result3 = mysql_query($query,$link) or die('Error en consulta');
			
				if(mysql_num_rows($result3)) {
					while($usuario = mysql_fetch_assoc($result3)) {
						$Nom_cliente = $usuario["Nombre"];
						if($usuario["NIF"] != ""){
						$Nif = $usuario["NIF"];
						}else
						{
						$Nif = "No especificado.";
						}
						
						if($usuario["Direccion"] == ""){
						$Direccion = "No especificada";
						}else
						{
							if($usuario["CodPostal"] != ""){
							$Direccion = $usuario["Direccion"]. " Cod.Postal ". $usuario["CodPostal"];
							}else
							{
							$Direccion = $usuario["Direccion"];	
							}
						}
					}
				}


				
				if (isset($email)){
					$cabecera = "<b>Cliente:</b> ". $Nom_cliente. 
								"<br><b>Nif:</b> ".$Nif.
								"<br>"."<b>Dirección:</b> ".$Direccion.
								"<br><b>Nº Pedido:</b> ".$id_pedido;
					if ($FPrevista != "01-01-1900 00:00"){
						$cabecera = $cabecera. "<b>Fecha Entrega:</b> ".$FPrevista."<br>";
						}
					if ($Notas != ""){
						$cabecera = $cabecera. "<b>Notas:</b> ".$Notas.".<br>";
						}	
					$mensaje = $mensaje."<br>Pulse el siguiente enlace para notificar al cliente la recepción del pedido:<a href='www.labericavicente.com/anotaped/Marcar_pedido_recibido.php?ID=".$Id_Externo."'>Notificar pedido recibido</a><br>";
					$to = $email;
					$subject = "Nuevo Pedido de ".$Nom_cliente;
					$headers = "From: " . "adrian.castro1985@gmail.com". "\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
					
					if (mail($to, $subject, "<HTML><BODY>".$cabecera."<br><br><b>Productos:</b><br>".$mensaje."</BODY></HTML>", $headers))
					{
						$resultado = "OK";
					}
					else
					{
						$resultado = "NOOK";
						$lista_errores[] = array('Error' =>"Error al enviar.");
					}	
					
					}

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
echo json_encode(array('Lista'=>$exportar));

?>