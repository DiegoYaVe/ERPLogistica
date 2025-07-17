<?php
require('../funciones.php');


if($_POST['tipo'] == "img"){

  $iddoc = $_POST['id'];
  $respuesta = $iddoc;
  
  $sql = 'DELETE FROM mailing_imagenes WHERE mi_id = "'.$iddoc.'"';
  setq($sql);


}else{

$iddoc = $_POST['id'];
$respuesta = $iddoc;

$sql = 'DELETE FROM mailing_documentos WHERE md_id = "'.$iddoc.'"';
setq($sql);
}

echo $respuesta;


?>