<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
if(isset($_GET['id'])){
  include_once('../modulos/paqueterias.php');
  $paquete = new modelpaqueterias();
  $paquete->select($_GET['id']);

  $accion = 'update&id='.$_GET['id'];
  $title = "EDITAR";
  if($paquete->estatus == "A")
    $checked2 = "checked";
  else
    $checked2 = "";

  if($paquete->adomicilio == 1)
    $checked3 = "checked";
  else
    $checked3 = "";

  if($paquete->ocurre == 1)
    $checked4 = "checked";
  else
    $checked4 = "";
}
else{
  $accion = "insert";
  $title = "AGREGAR";
  $checked2="checked";
}

echo '
    <div class="container">
    <div class="col-12 alert alert-primary">'.$title.' Paqueteria</div>
      <form class="row" method="post" action="index?modulo=paqueterias&accion='.$accion.'">
        <div class="mb-5 col-md-4">
        <label>Nombre de la paqueteria:</label>
          <input type="text" id="nmb" class="form-control focus mayus" value="'.$paquete->nmb.'" placeholder="Nombre de la paqueteria" name="nmb" value="'.$row['cat_nmb'].'" required="required" >
        </div>
        <div class="mb-5 col-md-6">
        <label>URL de rastreo:</label>
          <input type="text" id="siglas" class="form-control" data-toggle="tooltip" value="'.$paquete->urlrastreo.'" data-placement="top" data-original-title="Determina un nombre corto de tu Linea de negocio, 3 Carácteres" placeholder="Esta URL se le comparte a los clientes cuando se envia su guía" name="urlrastreo" value="'.$row['cat_siglas'].'" required="required"  >
        </div>
        <div class="mb-5 col-md-2">
        <label>Estatus:</label>
          <input class= "flipswitch form-control" type="checkbox" name="estatus" id="estatus" '.$checked2.'>
        </div>
        <div class="mb-5 col-md-4">
        <div class="mb-5 col-md-6">
        <label>Entrega a domicilio:</label>
          <input class= "flipswitch form-control" type="checkbox" name="adomicilio" id="adomicilio" '.$checked3.'>
        </div>
        </div>
        <div class="mb-5 col-md-2">
        <label>Entrega ocurre:</label>
          <input class= "flipswitch form-control" type="checkbox" name="ocurre" id="ocurre" '.$checked4.'>
        </div>
        <div class="mb-5 col-md-12">
          <center><button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar</center>
        </div>
      </form>
    </div>';

?>