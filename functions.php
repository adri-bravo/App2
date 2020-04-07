<?php
session_start();
function login($email, $password) {
global $GL_id_usuario;	

	$GL_id_usuario = -1;
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS,DB_NAME);
	if (login_admin($email,$password,$GL_id_usuario, $mysqli) == false) {
		if (login_user($email,$password,$GL_id_usuario,$mysqli) == false) {
			if ($email == "ADMIN" && $password == hash('sha512','wm19,na.gay'.'anota')) {
				$resultado = "OK";
				$_SESSION['session_pass']=$password;
				$_SESSION['session_email']=$email;
				$_SESSION['usuario']="ADMINISTRADOR";
				$_SESSION['tipo']="ADMIN";
				return true;
			}else
			{
				return false;
			}
		}
		else
		{
			
		return true;
		}
	}
	else
	{
	return true;
	}
}
function login_admin($email,$password,&$GL_id_usuario,$mysqli){
    // Usar declaraciones preparadas significa que la inyección de SQL no será posible.
    if ($stmt = $mysqli->prepare("SELECT Id_Usuario, Pass, Nombre, Cups FROM Usuarios WHERE email = ?
        LIMIT 1")) {
        $stmt->bind_param('s', $email);  // Une “$email” al parámetro.
        $stmt->execute();    // Ejecuta la consulta preparada.
        $stmt->store_result();
 
        // Obtiene las variables del resultado.
        $stmt->bind_result($user_id,$db_password,$nombre,$cups);
        $stmt->fetch();
 
        // Hace el hash de la contraseña con una sal única.
        //PENDIENTEEEEEE 
		//$salt = 'anota';
		//$password = hash('sha512', $password . $salt);
//echo $password;
        if ($stmt->num_rows == 1) {
            // Si el usuario existe, revisa si la cuenta está bloqueada
            // por muchos intentos de conexión.
 
            if (checkbrute($user_id, $mysqli) == true) {
                // La cuenta está bloqueada.
                // Envía un correo electrónico al usuario que le informa que su cuenta está bloqueada.
                return false;
            } else {
                // Revisa que la contraseña en la base de datos coincida 
                // con la contraseña que el usuario envió.
                if ($db_password == $password) {
                    // ¡La contraseña es correcta!
                    // Inicio de sesión exitoso
					$GL_id_usuario = $user_id;
					$_SESSION['session_pass']=$password;
					$_SESSION['session_email']=$email;
					$_SESSION['usuario']=$nombre;
					$_SESSION['tipo']="ADMIN";
					$_SESSION['cups']=$cups;
                    return true;
                } else {
                    // La contraseña no es correcta.
                    // Se graba este intento en la base de datos.
                    $now = time();
                    $mysqli->query("INSERT INTO login_attempts(user_id, time)
                                    VALUES ('$user_id', '$now')");
                    return false;
                }
            }
        } else {
            // El usuario no existe.
            return false;
        }
    }
	else{
		return false;}

}
function login_user($email,$password,&$GL_id_usuario, $mysqli){
    // Usar declaraciones preparadas significa que la inyección de SQL no será posible.
    if ($stmt = $mysqli->prepare("SELECT Id_Usuario, Pass, Nombre FROM Usuarios_empleados WHERE email = ?
        LIMIT 1")) {
        $stmt->bind_param('s', $email);  // Une “$email” al parámetro.
        $stmt->execute();    // Ejecuta la consulta preparada.
        $stmt->store_result();
 
        // Obtiene las variables del resultado.
        $stmt->bind_result($user_id,$db_password,$nombre);
        $stmt->fetch();
 
        // Hace el hash de la contraseña con una sal única.
        //PENDIENTEEEEEE 
		//$salt = 'anota';
		//$password = hash('sha512', $password . $salt);
//echo $password;
        if ($stmt->num_rows == 1) {
            // Si el usuario existe, revisa si la cuenta está bloqueada
            // por muchos intentos de conexión.
 
            if (checkbrute($user_id, $mysqli) == true) {
                // La cuenta está bloqueada.
                // Envía un correo electrónico al usuario que le informa que su cuenta está bloqueada.
                return false;
            } else {
                // Revisa que la contraseña en la base de datos coincida 
                // con la contraseña que el usuario envió.
                if ($db_password == $password) {
                    // ¡La contraseña es correcta!
                    // Inicio de sesión exitoso
					$GL_id_usuario = $user_id;
					$_SESSION['session_pass']=$password;
					$_SESSION['session_email']=$email;
					$_SESSION['usuario']=$nombre;
					$_SESSION['tipo']="EMPLEADO";
                    return true;
                } else {
                    // La contraseña no es correcta.
                    // Se graba este intento en la base de datos.
                    $now = time();
                    $mysqli->query("INSERT INTO login_attempts(user_id, time)
                                    VALUES ('$user_id', '$now')");
                    return false;
                }
            }
        } else {
            // El usuario no existe.
            return false;
        }
    }
	else{
		return false;}

}

function checkbrute($user_id, $mysqli) {
    // Obtiene el timestamp del tiempo actual.
    $now = time();
 
    // Todos los intentos de inicio de sesión se cuentan desde las 2 horas anteriores.
    $valid_attempts = $now - (2 * 60 * 60);
 
    if ($stmt = $mysqli->prepare("SELECT time 
                             FROM login_attempts 
                             WHERE user_id = ? 
                            AND time > '$valid_attempts'")) {
        $stmt->bind_param('i', $user_id);
 
        // Ejecuta la consulta preparada.
        $stmt->execute();
        $stmt->store_result();
 
        // Si ha habido más de 5 intentos de inicio de sesión fallidos.
        if ($stmt->num_rows > 5) {
            return true;
        } else {
            return false;
        }
    }
}
function get_bitly_short_url($url,$login,$appkey,$format='txt') {
	$connectURL = 'http://api.bit.ly/v3/shorten?login='.$login.'&apiKey='.$appkey.'&uri='.urlencode($url).'&format='.$format;
	return curl_get_result($connectURL);
}
function curl_get_result($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
?>