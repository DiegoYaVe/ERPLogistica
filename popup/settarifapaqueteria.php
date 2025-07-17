<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
if(isset($_GET['idtarifa'])){
  include_once('../modulos/paqueterias.php');
  $paquete = new modelpaqueterias();
  $paquete->selecttarifa($_GET['idtarifa']);

  $accion = 'updatetarifa&idtarifa='.$_GET['idtarifa'];
  $title = "EDITAR";
}
else{
  $accion = "inserttarifa";
  $title = "AGREGAR";
}

$id = $_GET['id'];
echo '
    <div class="container">
    <div class="col-12 alert alert-primary">'.$title.' Tarifa de paqueteria '.busca($id,'paqueterias','p_id','p_nmb').'</div>
      <form class="row" method="post" action="index?modulo=paqueterias&accion='.$accion.'">
        <input type="hidden" name="paqueteria" value="'.$id.'" />
        <div class="mb-5 col-md-4">
        <label>Nombre de la tarifa:</label>
          <input type="text" id="nmb" class="form-control focus mayus" value="'.$paquete->nmb.'" placeholder="Nombre de la tarifa" name="nmb" required="required" >
        </div>
        <div class="mb-5 col-md-3">
        <label>Rango de KM:</label>
          <input type="number" step="1" min="0" max="999999" id="kmmin" class="form-control" data-toggle="tooltip" value="'.$paquete->kmmin.'" data-placement="top" data-original-title="Mínimo en KM" placeholder="Distancia mínima" name="kmmin" required="required"  >
          <input type="number" step="1" min="0" max="999999" id="kmmax" class="form-control" data-toggle="tooltip" value="'.$paquete->kmmax.'" data-placement="top" data-original-title="Máximo en KM" placeholder="Distancia máxima" name="kmmax" required="required"  >
        </div>
        <div class="mb-5 col-md-3">
        <label>Rango de peso (KG):</label>
          <input type="number" step="0.01" min="0" max="999999" id="pesomin" class="form-control" data-toggle="tooltip" value="'.$paquete->pesomin.'" data-placement="top" data-original-title="Mínimo en KG" placeholder="Peso mínimo" name="pesomin" required="required"  >
          <input type="number" step="0.01" min="0" max="999999" id="pesomax" class="form-control" data-toggle="tooltip" value="'.$paquete->pesomax.'" data-placement="top" data-original-title="Máximo en KG" placeholder="Peso máximo" name="pesomax" required="required"  >
        </div>
        <div class="mb-5 col-md-2">
        <label>Precio de la tarifa:</label>
          <input type="number" step="1" min="0" max="999999" id="precio" class="form-control" data-toggle="tooltip" value="'.$paquete->precio.'" data-placement="top" data-original-title="Precio del paquete" placeholder="Importe de la tarifa" name="precio" required="required"  >
        </div>
        <div class="mb-5 col-md-12">
          <center><button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar</center>
        </div>
      </form>
    </div>';

?>