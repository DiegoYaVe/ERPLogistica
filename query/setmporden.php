<?php 
ini_set('display_errors', 1);
include_once('../funciones.php');

$orden = $_REQUEST['orden'];
$id = $_REQUEST['idpom'];
$mp = $_POST['mp'];
$cantidad = $_REQUEST['cantidad'];
$largo = $_POST['largo'];
$ancho = $_POST['ancho'];
$alto = $_POST['alto'];

if($_REQUEST['accion'] == "1"){
  $sql = 'UPDATE pr_ordenmateriaprima SET pom_cantidad ="'.$cantidad.'" WHERE pom_id="'.$id.'"';
} else if($_REQUEST['accion'] == "2"){
  $sql = 'DELETE FROM pr_ordenmateriaprima WHERE pom_id="'.$id.'"';
} else {
  $sql = 'INSERT INTO pr_ordenmateriaprima SET pom_ordenprod = "'.$orden.'",
                                            pom_materiaprima = "'.$mp.'",
                                            pom_cantidad = "'.$cantidad.'",
                                            pom_largo = "'.$largo.'",
                                            pom_ancho = "'.$ancho.'",
                                            pom_alto = "'.$alto.'"';
  
}

setq($sql);                                            


?>