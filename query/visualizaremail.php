<?php

ini_set('display_errors',1);
require_once("../lib/mailer/class.phpmailer.php");
require_once("../lib/mailer/class.smtp.php");
require('../funciones.php');



$coemail = "noreply@jdshop.mx";
$adminpassword = "WpJDTye2020#";

$idemail = $_GET['idd'];
$camapana = $_GET['id'];  
$email = $_POST['correo'];

$sql = 'SELECT * FROM mailing_campanasd WHERE md_id = "'.$idemail.'"';
$result = setq($sql);
$row = $result->fetch_array();

$mail = new PHPMailer();


//$body = (utf8_decode($row['md_cuerpo']));


$mail->SMTPSecure = "ssl";
//$mail->SMTPSecure = "tls";
$mail->Host='jdshop.mx';
$mail->Port = "465";
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->Username = $coemail;
$mail->Password = "WpJDTye2020#";
//$mail->SMTPDebug = 2;

//Defino el email y nombre del remitente del mensaje

$mail->setFrom($coemail,"JD SHOP");

//Defino la dirección de correo a la que se envía el mensaje
$mail->addAddress($email);    

//Defino la dirección de email de "reply", a la que responder los mensajes es bueno dejar la misma dirección que el From, para no caer en spam
//$mail->addReplyTo('registros-distribuidor@jdshop.mx');

//Añado un asunto al mensaje
$mail->Subject = utf8_decode($row['md_asunto']);

//Puedo definir un cuerpo alternativo del mensaje, que contenga solo texto
$mail->AltBody ="Cuerpo alternativo del mensaje";

//Inserto el texto del mensaje en formato HTML
//$mail->MsgHTML(utf8_decode($body));
$botonreg = utf8_encode('
<br><a href="http://jdshop.mx/iniciatunegocio/registro-de-distribuidor" style="
background: #28badb;
padding: 10px;
color: white;
border: #28badb;
cursor: pointer;
text-decoration: none;
font-family: sans-serif;
border-radius: 4px;"> ¡Completa tu Registro! </a></br>');
//////////////// NUEVOOOOOOOOO  //////////////////////////
$body = $row['md_cuerpo'];
$message = $body;
$message = str_replace('--%botonregistro%--',$botonreg,$message);
$final = (utf8_decode('
<br>'.$message.'<br>
'));

//die($final);
$mail->MsgHTML($final);
////////////// NUEVOOOOOOOOOO  //////////////////////////


$sqladj = 'SELECT * FROM mailing_imagenes WHERE mi_campanad = "'.$idemail.'"';
$resultadj = setq($sqladj);
$adjuntosimg = array();
$a=0; 

while($rowadj = $resultadj->fetch_array()){

  $adjuntos[$a] = '../'.$rowadj['mi_ruta'];
  $mail->AddAttachment($adjuntos[$a]);
  $a++;
}

 
$sqladj = 'SELECT * FROM mailing_documentos WHERE md_campanad = "'.$idemail.'"';
$resultadj = setq($sqladj);
$adjuntosimg = array(); 

while($rowadj = $resultadj->fetch_array()){

  $adjuntos[$a] = $rowadj['md_ruta'];
  $mail->AddAttachment($adjuntos[$a]);
  $a++;
}


//die();


//Envío el mensaje, comprobando si se envió correctamente
if(!$mail->send()) {
    //echo 'Correo electronico no enviado, verificar';
    
    //echo 'Descripcion del error: ' . $mail->ErrorInfo;
} 
//
else{
	//echo "Correo electronico enviado ";

  header('location: ../index.php?modulo=mailing&accion=selectcampana&id='.$camapana.'&idd='.$idemail.'');
}

?>