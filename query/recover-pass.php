<?php
//ini_set('display_errors', 1);
include('../funciones.php');

$mail = mb_strtolower(trim($_REQUEST['mail']));
$sql = 'SELECT u_nuser FROM usuarios WHERE u_mailcorp = "'.$mail.'" OR u_correo = "'.$mail.'" AND u_estatus = "A"';
$result = setq($sql);
//echo $sql.'<<';
if($result->num_rows > 0){
  list($user) = $result->fetch_row();
  $temp = cadena_aleatoria(10,true,true,true,true);
  $sqlu = 'UPDATE usuarios SET u_passtemp = "'.$temp.'" WHERE u_nuser = "'.$user.'"';
  if(setq($sqlu)){
    $urlroot = busca(1,'configuracion','c_id','c_urlroot');

    $sql = 'SELECT u_nmb,u_apellidos FROM usuarios WHERE u_mailcorp = "'.$mail.'" OR u_correo = "'.$mail.'"';
    $resultin = setq($sql);
    list($nmbin,$apelin) = $resultin->fetch_array();

    $message = 'Hola '.$nmbin.' '.$apelin.'<br><br>
Has solicitado un cambio de contraseña<br>
Para cambiar la contraseña sigue&nbsp;
<a href="'.$urlroot.'/resetpass=codeblq='.$temp.'">este enlace</a>
<br><br>
Si tu no solicitaste un cambio de contraseña, da clic sobre
<a href="'.$urlroot.'/resetpass=codeblq='.$temp.'&notgive=1">Este enlace</a>';
    if(sendmail(1,$mail,utf8_decode($message),$user)) $return = "1"; else $return = "3";
  }
}
else $return = "2";

echo $return;

?>