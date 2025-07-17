<?php
  session_start();
  ini_set('display_errors',1);
  include_once('../funciones.php');


  $idesquema = $_GET['id'];
  if($idesquema){
    $sql = 'SELECT * FROM esquema_precio WHERE ep_id = "'.$idesquema.'" AND ep_empresa = "'.$_SESSION['emp'].'"';
    $result = setq($sql);
    $row = $result->fetch_array();
  } 

  $basep = "";
  $basec = "";
  $basef = "";
  $chiva = "";
  if($row['ep_id']){
    $accion = 'update';
    $titulo = "Actualizar";
    if($row['ep_base'] == "C") $basec = "selected";
    elseif($row['ep_base'] == "P") $basep = "selected";
    else $basef = "selected";

    if($row['ep_miva'] == "1") $chiva = "checked";
  }else{
    $accion = 'insert';
    $basec = "selected";
    $titulo = "Agregar un";
  }
  if($row['ep_puntodv'] == "1") $checked = "checked"; else $checked = "";
  if($estatus == "A") $chest = "checked"; else $chest = "";

  $esta = "";
  $esti = "";
  $estt = "";
  if(!isset($estatus) || $estatus == "A") $esta = "selected";
  elseif($estatus == "I") $esti = "selected";
  else $estt = "selected";

  echo '
  <script language="JavaScript">
    function checkSubmitedit() {
      document.getElementById("guardar").value = "JD";
      document.getElementById("guardar").disabled = true;
      return true;
    }
  </script>';

  echo '<form method="post" action="index?modulo=precios&accion='.$accion.'" name="addprecio" onsubmit="checkSubmitedit();">
    <input type="hidden" value="'.$row['ep_id'].'" name="id" />';
    echo '<div class="row">
      <div class="col-md-12 col-lg-12 alert alert-primary">
        '.$titulo.' esquema de precios
      </div>
    </div>
    <div class="row container">
      <div class="col-md-6 col-lg-3">
        <div class="">
          <label>Nombre del esquema</label>
          <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre del esquema" name="nmb" value="'.$row['ep_nmb'].'" required="required" >
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="">
          <label>Monto de referencia</label>
          <select name="base" id="base" required="required" class="form-control" onchange="showbase()">
            <option value="C" '.$basec.' >Sacar precio a partir del costo</option>
            <option value="P" '.$basep.'>Determinar el precio desde un precio existente</option>
            <option value="F" '.$basef.'>Fijar el costo manualmente</option>
          </select>
        </div>
      </div>
      <div class="col-sm-8 col-md-6 col-lg-3" id="preciob" style="display:none">
        <div class="mb-5">
          <label>Precio Referencia</label>
          <select name="precio" id="precio" required="required" class="form-control">';
            $sqlp = 'SELECT * FROM esquema_precio WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_estatus = "A"';
            $resultp = setq($sqlp);
            while($rowp = $resultp->fetch_array()){
              echo '<option value="'.$rowp['ep_id'].'" >'.$rowp['ep_nmb'].'</option>';
            }
          echo '</select>
        </div>
      </div>
      <div class="col-md-6 col-lg-3" id="masomenos" style="display:none">
        <div class="mb-5">
          <label>Comportamiento de la formula</label>
          <select name="masmenos" id="masmenos" required="required" class="form-control" onchange="showbase()">
            <option value="+">Sumar un porcentaje al precio</option>
            <option value="-">Restar un porcentaje al precio</option>
          </select>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="mb-5">
          <label>% de utilidad</label>
          <input type="number" name="sumarp" id="sumarp" class="form-control" required="required" min="0" max="9999" step="0.01" value="'.$row['ep_sumarp'].'" placeholder="% de utilidad sobre la referencia" />
        </div>
      </div>
      <div class="col-md-6 col-lg-3" id="agregarm">
        <div class="mb-5">
          <label>Agregar</label>
          <span title="Monto fijo en pesos que será sumado al precio final para este esquema"><i class="icon-question-circle"></i></span>
          <input type="number" name="sumarf" id="sumarf" value="'.$row['ep_sumarf'].'" class="form-control" required="required" min="0" max="9999" step="0.01" placeholder="Sumar adicional" />
        </div>
      </div>
      <div class="col-md-6 col-lg-2 mt-1" id="masiva">
        <div class="mb-5">
          <center>
            <label>Sumar IVA</label><br>
            <input type="checkbox" name="miva" id="miva" '.$chiva.' data-toggle="tooltip" data-placement="top" data-original-title="Referencia X Utilidad + IVA"/>
          </center>
        </div>
      </div>
      <div class="col-md-6 col-lg-2 mt-1">
        <div class="mb-5">
        <center>
          <label>Punto de Venta  </label><i id="pvs" style="cursor: pointer;" class="fa fa-info" data-toggle="popover" data-placement="top" title="Selecciona este equema para ser el predeterminado en el punto de venta"></i><br>
          <input type="checkbox" name="pv" id="pv" '.$checked.'  data-toggle="tooltip" data-placement="top" data-original-title="Predeterminado para precios del Punto de Venta" >
        </center>
        </div>
      </div>
    </div>
      <div class="row">
      <div class="col-sm-12 col-md-12 text-xs-center mb-1">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button></center>
      </div>';
    echo '</div>
  </form>

  
  ';
?>

<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

showbase();

</script>

