<?php
session_start();
ini_set('display_errors',0);
include_once('../funciones.php');

$idcat = $_GET['id'];
if($idcat){
  $sql = 'SELECT * FROM ruta_tarifario WHERE rt_id = "'.$idcat.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $accion = 'update';
  $titulo = "Actualizar";
} else {
  $sel = "";
  $accion = 'insert';
  $titulo = "Agregar";
}


echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' tarifa</div>
      <form method="post" action="index?modulo=rutas&accion='.$accion.'">
        <input type="hidden" value="'.$idcat.'" name="id" />
        <div class="row">
          <div class="mb-6 col-md-6">
            <label>Origen:</label>
            <select id="origen" name="origen" class="form-control">';
            $sqlo ='SELECT * FROM ruta_origen WHERE ro_estatus = "A"';
            $resulto = setq($sqlo);
            while($rowo = $resulto->fetch_array()){
              if($rowo['ro_id'] == $row['rt_origen'])$selo = 'selected';
              else $selo = '';
              echo '<option value="'.$rowo['ro_id'].'" '.$selo.'>'.$rowo['ro_nombre'].'</option>';
            }
            echo '</select>
          </div>
          <div class="mb-5 col-md-4">
            <label>Destino:</label>
            <select id="origen" name="destino" class="form-control">';
            $sqld ='SELECT * FROM ruta_destino WHERE rd_estatus = "A"';
            $resultd = setq($sqld);
            while($rowd = $resultd->fetch_array()){
              if($rowd['rd_id'] == $row['rt_destino'])$seld = 'selected';
              else $seld = '';
              echo '<option value="'.$rowd['rd_id'].'" '.$seld.'>'.$rowd['rd_nombre'].'</option>';
            }
            echo '</select>
          </div>
          <div class="mb-6 col-md-6">
            <label>Kilometros:</label>
            <input type="number" step="0.01" min="0.01" id="kilometros" class="form-control" placeholder="Kilometros" name="kilometros" value="'.$row['rt_km'].'" required="required" >
          </div>
          <div class="mb-4 col-md-4">
            <label>Rendimiento:</label>
            <input type="number" step="0.01" min="0.01" id="rendimiento" class="form-control" placeholder="Rendimiento" name="rendimiento" value="'.$row['rt_rendimiento'].'" required="required" >
          </div>
          <div class="mb-4 col-md-4">
            <label>Costo gasolina:</label>
            <input type="number" step="0.01" min="0.01" id="precio" class="form-control" placeholder="Costo de gasolina" name="precio" value="'.$row['rt_precio'].'" required="required" >
          </div>
          <div class="mb-4 col-md-4">
            <label>Capacidad tanque:</label>
            <input type="number" step="0.01" min="0.01" id="capacidad" class="form-control" placeholder="Capacidad del tanque" name="capacidad" value="'.$row['rt_capacidad_tanque'].'" required="required" >
          </div>
          <div class="mb-5 col-md-4">
            <label>Tanques:</label>
            <input type="number" step="0.01" min="0.01" id="tanques" class="form-control" placeholder="Tanques" name="tanques" value="'.$row['rt_tanques'].'" required="required" >
          </div>
          <div class="mb-5 col-md-4">
            <label>Combustible:</label>
            <input type="number" step="0.01" min="0.01" id="combustible" class="form-control" placeholder="Combustible" name="combustible" value="'.$row['rt_combustible'].'" required="required" >
          </div>
          <div class="mb-5 col-md-4">
            <label>Casetas:</label>
            <input type="number" step="0.01" min="0.01" id="casetas" class="form-control" placeholder="Casetas" name="casetas" value="'.$row['rt_casetas'].'" required="required" >
          </div>
          <div class="mb-5 col-md-4">
            <label>Desgaste:</label>
            <input type="number" step="0.01" min="0.01" id="desgaste" class="form-control" placeholder="Desgaste" name="desgaste" value="'.$row['rt_desgaste'].'" required="required" >
          </div>
          <div class="mb-5 col-md-4">
            <label>Operador:</label>
            <input type="number" step="0.01" min="0.01" id="operador" class="form-control" placeholder="Operador" name="operador" value="'.$row['rt_operador'].'" required="required" >
          </div>
          <div class="mb-5 col-md-4">
            <label>Total Costo DVL:</label>
            <input type="number" step="0.01" min="0.01" id="costodvl" class="form-control" placeholder="total costo DVL" name="costodvl" value="'.$row['rt_costodvl'].'" required="required" >
          </div>
          <div class="mb-5 col-md-4">
            <label>Venta DVL:</label>
            <input type="number" step="0.01" min="0.01" id="ventadvl" class="form-control" placeholder="Venta DVL" name="ventadvl" value="'.$row['rt_ventadvl'].'" required="required" >
          </div>
          <div class="mb-5 col-md-4">
            <label>Venta Krauss:</label>
            <input type="number" step="0.01" min="0.01" id="ventakraus" class="form-control" placeholder="ventakraus" name="ventakraus" value="'.$row['rt_ventakraus'].'" required="required" >
          </div>
          <div class="mb-5 col-md-12">
            <center>
              <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>Guardar</button>
            </center>
          </div>
        </div>
      </form>
    </div>';

?>