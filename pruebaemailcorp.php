<?php
session_start();
include('funciones.php');
require_once("lib/mailer/class.phpmailer.php");
require_once("lib/mailer/class.smtp.php");
ini_set('display_errors', 0);
//$sql = 'SELECT * FROM usuarios WHERE u_id = "'.$_SESSION['uid'].'"';
$sql = 'SELECT * FROM usuarios WHERE u_id = "'.$_GET['id'].'"';
$result = setq($sql);
$row = $result->fetch_array();

//$sqlpass = ""
$mail = new PHPMailer();

//$mail->SMTPSecure = "ssl";
//$mail->SMTPSecure = "tls";
//$mail->Host='jdshop.mx';
//$mail->Port = "465";
$email = $row['u_mailcorp'];
//$email = "caepegu08@gmail.com";

$mail->IsSMTP();
$mail->Host = $row['u_host'];
$mail->Port = $row['u_puerto'];
if($row['u_seguridad'] != 0){
    $mail->SMTPSecure = $row['u_seguridad'];
}
$mail->SMTPAuth = true;
$mail->Username = $row['u_mailcorp'];
$mail->Password = base64_decode($row['u_contraseñacorp']);
//$mail->SMTPDebug = 2;
//Defino el email y nombre del remitente del mensaje
$mail->setFrom($row['u_mailcorp'],utf8_decode($row['u_remitente']));

//Defino la dirección de correo a la que se envía el mensaje
$mail->addAddress($email);    

//Defino la dirección de email de "reply", a la que responder los mensajes es bueno dejar la misma dirección que el From, para no caer en spam
//$mail->addReplyTo('registros-distribuidor@jdshop.mx');

//Añado un asunto al mensaje
$mail->Subject = utf8_decode('Verificación de email');

//Puedo definir un cuerpo alternativo del mensaje, que contenga solo texto
$mail->AltBody = "Cuerpo alternativo del mensaje";

$mensaje = utf8_decode('Esta es una prueba de verificación del correo: '.$row['u_mailcorp'].' para JDCEO v2.0');
$mail->MsgHTML($mensaje);



//Envío el mensaje, comprobando si se envió correctamente
if(!$mail->send()) {
    echo 'Error: Conexión no completada, verificar datos';
    
    //echo 'Descripcion del error: ' . $mail->ErrorInfo;
} else{

	echo "Datos correctos, configuración completa";

}





?>