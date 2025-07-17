<?php
include_once('../funciones.php');
ini_set('display_errors',1);
session_start();

$fini = $_POST['fini'];
$ffin = $_POST['ffin'];

if(isset($_SESSION['uid'])){
  $tuser = 'U';
  $user = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else{
  $tuser = 'O';
  $user = $_SESSION['eid'];
}
$arreglo = array();
    $sqlop = 'SELECT ppx_id FROM pr_procesoexe WHERE ppx_operador = "'.$user.'" AND ppx_tuser = "'.$tuser.'" AND ppx_fecha BETWEEN "'.$fini.'" AND "'.$ffin.'" GROUP BY ppx_ordenp ORDER BY ppx_ordenp DESC';

    $resultop = setq($sqlop);
    while($row = $resultop->fetch_array()){
    $sqlop1 = 'SELECT * FROM pr_ordenprod WHERE po_id = "'.$row['ppx_id'].'"';
    $resultop1 = setq($sqlop1);
    $rowop = $resultop1->fetch_array();
    
    if(empty($rowop['po_ffin'])){
      $ffin = 'No disponible';
    } else{
      $ffin = fecha_formato($rowop['po_ffin'], true, false);
    }

    if($rowop['po_estatus'] == "N"){
      $estatus = "Nueva";
    } else if($rowop['po_estatus'] == "P"){
      $estatus = "En ejecución";
    } else if($rowop['po_estatus'] == "F"){
      $estatus = "Finalizada";
    } else{
      //Si se cancela
      $estatus = "Cancelada";
    }    

    $acciones = '<button type="button" class="btn btn-primary" onclick="verprocesos('.$rowop['po_id'].')"><i class="fas fa-eye"></i></button>';

    $arreglo[] = array($rowop['po_folio'], $rowop['po_nmbarticulo'], fecha_formato($rowop['po_fgen'], true, false), $ffin, $estatus, $acciones);
  }

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>