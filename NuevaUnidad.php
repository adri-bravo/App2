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
		
		foreach ($obj['Guardar'] as $k=>$Unidad)
		{	
		
			 $sql = "INSERT INTO Unidades( Id_Usuario,Unidad) VALUES (".$GL_id_usuario.",'" 
			 .$Unidad{'Unidad'}."')";
			 if (mysql_query( $sql,$link)==true)
			 {
				if(mysql_affected_rows()==-1){
					$errores = $errores +1;
					$lista_errores[] = array('Error' =>"Unidades:".$Unidad{'Unidad'});
					}
			 }else{$errores = $errores +1;$lista_errores[] = array('Error' =>"Unidades:".$Unidad{'Unidad'});}
		
			
		}
		
		if($errores == 0){
			$resultado = "OK";
			mysql_query("COMMIT");
		}else
		{
			$resultado = "NOOK";
			mysql_query("ROLLBACK");
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

mysql_close($link);
?>