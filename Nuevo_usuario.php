<?php
require_once 'config.php';
require_once 'ApiException';
// Obtener parámetros de la petición
$parameters = file_get_contents('php://input');
$decodedParameters = json_decode($parameters);

// Verificar integridad de datos
// TODO: Implementar restricciones de datos adicionales
if (!isset($decodedParameters["pass"]) ||
	!isset($decodedParameters["email"]) ||
	!isset($decodedParameters["nombre"]) ||
	!isset($decodedParameters["nif"])) 
{
	// TODO: Crear una excepción individual por cada causa anómala
	throw new ApiException(400, 0,
		"Verifique los datos del usuario",
		" ",
		"");
}else
{
//Insertar usuario
	try {
		$name = $decodedParameters["Nombre"];
		$nif = $decodedParameters["Nif"];
		$email = $decodedParameters["Email"];
		$password = $decodedParameters["Pass"];
	
		$pdo = MysqlManager::get()->getDb();
		
		$stmt = $pdo->prepare("SELECT Id_Usuario FROM Usuarios Order by Id_Usuario DESC limit 1");
		$stmt->execute();
		$id_usuario = -1;
		while($row = $stmt->fetch(PDO::FETCH_OBJ)){
			$id_usuario = $row->Id_Usuario +1;
		}
		if ($id_usuario <> -1){
		// Componer sentencia INSERT
		$sentence = "inserto into Usuarios ( Id_Usuario,Nombre,Nif,Email,Pass)" .
			" values (?,?,?,?,?)";

		// Preparar sentencia
		$preparedStament = $pdo->prepare($sentence);
		$preparedStament->bindParam(1, $id_usuario);
		$preparedStament->bindParam(2, $name);
		$preparedStament->bindParam(3, $nif);
		$preparedStament->bindParam(4, $email);
		$preparedStament->bindParam(5, $password);

		// Ejecutar sentencia
		 if ($preparedStament->execute()){
				echo json_encode(array("Id_Usuario"=>$id_usuario));
			 };
		}
	} catch (PDOException $e) {
		throw new ApiException(
			500,
			0,
			"Error de base de datos en el servidor",
			" ",
			"Ocurrió el siguiente error al intentar insertar el usuario: " . $e->getMessage());
	}
}
?>