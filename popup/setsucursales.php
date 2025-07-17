<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$paqueteria = $_GET['paqueteria'];
$idsuc = $_GET['id'];
$sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_id = "'.$idsuc.'"';
$result = setq($sql);
$row = $result->fetch_array();
$accion = 'updatesucursal';
$titulo = "Actualizar";
if($row['ps_estatus'] == "1") $sel = "checked";
else $sel = "";

echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' datos de la sucursal</div>
      <form method="post" action="index?modulo=paqueterias&paqueteria='.$paqueteria.'&accion='.$accion.'">
        <input type="hidden" value="'.$idsuc.'" name="sucursal"/>
        <div class="row">
          <div class="mb-5 col-12 col-md-4">
            <label>Sucursal:</label>
            <input type="text" id="nmbsuc" class="form-control focus mayus" placeholder="Nombre corto de la sucursal" name="nmbsuc" value="'.$row['ps_sucursal'].'" readonly>
          </div>  
          <div class="mb-5 col-12 col-md-4">
            <label>Nombre de la sucursal:</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la categoria" name="nmb" value="'.$row['ps_nmb'].'" required="required" >
          </div> 
          <div class="mb-5 col-12 col-md-4">
            <label>Plaza:</label>
            <input type="text" id="plaza" class="form-control focus mayus" placeholder="Plaza" name="plaza" value="'.$row['ps_plaza'].'" required="required" >
          </div>
          <div class="mb-5 col-12 col-md-5">
            <label>Dirección:</label>
            <input type="text" id="direccion" class="form-control focus mayus" placeholder="Direccion de la sucursal" name="direccion" value="'.$row['ps_direccion'].'" required="required" >
          </div>
          <div class="mb-5 col-12 col-md-2">  
          <label>Estatus:</label>
            <input name="estatus" type="checkbox" id="estatus" class="form-control flipswitch2" data-toggle="tooltip" data-placement="top" '.$sel.'>
          </div>    
          <div class="mb-5 col-md-12">
            <center>
              <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar
            </center>
          </div>
        </div>
      </form>
    </div>';

?>