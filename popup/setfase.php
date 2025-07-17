<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$idfase = $_GET['id'];
if($idfase){
  $sql = 'SELECT * FROM pr_fases WHERE pf_id = "'.$idfase.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $accion = 'update';
  $titulo = "Actualizar";
  if($row['pf_estatus'] == "A") $sel = "checked";
  else $sel = "";
} else {
  $sel = "checked";
  $accion = 'insert';
  $titulo = "Agregar";
}

echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' fase</div>
      <form method="post" action="index?modulo=fases&accion='.$accion.'">
        <input type="hidden" value="'.$idfase.'" name="id" />
        <div class="row">
          <div class="mb-5 col-md-3 col-12">
            <label>Nombre del fase:</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre para identificar la fase" name="nmb" value="'.$row['pf_nmb'].'" required="required" onfocus="this.select();">
          </div>
          <div class="mb-5 col-md-3 col-12">
            <label>Descripción:</label>
            <input type="text" id="descripcion" class="form-control focus mayus" placeholder="Descripcion de la fase" name="descripcion" value="'.$row['pf_descripcion'].'" required="required" onfocus="this.select();">
          </div>
          <div class="col-md-3 col-12 mb-5">
            <label for="" class="">Ligar a Planta</label>
            <select name="planta" id="" class="form-control" required>';
            $sqlplan = 'SELECT * FROM pr_plantas WHERE pp_estatus = "A" ';
            $resultplan = setq($sqlplan);
            while($rowplan = $resultplan->fetch_array()){
              if($row['pf_planta'] == $rowplan['pp_id']) $selecplan =  'selected';
              else $selecplan = '';
              echo '
              <option value="'.$rowplan['pp_id'].'" class="" '.$selecplan.'>'.$rowplan['pp_nmb'].'</option>
              ';
            }
            echo '
            </select>
          </div>
          <div class="mb-5 col-md-2 col-12">  
            <label>Estatus:</label>
            <input type="checkbox" id="estatus" class="form-control flipswitch" data-toggle="tooltip" name="estatus" '.$sel.'>
          </div>
          <div class="mb-5 col-md-1 col-12">
          <label>Guardar:</label>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>
          </div>
        </div>
      </form>
    </div>';

?>