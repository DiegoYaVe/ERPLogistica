<?php
  session_start();
  include_once('../funciones.php');
  
  $idunidad = $_GET['id'];

  if($idunidad){
    $sql = 'SELECT * FROM unidades WHERE u_id = "'.$idunidad.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
  }

  if(!$idunidad) $titulo = 'Agregar'; else $titulo = 'Actualizar';

  if($row['u_id']) $accion = "update"; else $accion = "insert";

  echo '<div class="container col-10">
    <div class="alert alert-primary">'.$titulo.' unidad</div>
      <form method="post" action="?modulo=unidades&accion='.$accion.'" name="newunidad" id="newunidad">
        <div class="row"> 
          <input type="hidden" value="'.$row['u_id'].'" name="id" />
          <div class="col-md-4">
            <label>Nombre de unidad:</label>
            <input type="text" id="nmb" value="'.$row['u_nmb'].'" class="form-control" placeholder="Nombre de la unidad" name="nmb" required="required" >
          </div>
          <div class="col-md-4">
            <label>Descipcion:</label>
            <input type="text" id="descripcion" value="'.$row['u_descripcion'].'" class="form-control" placeholder="Descripcion" name="descripcion" required="required" >
          </div>
          <div class="col-md-3">
            <label>Clave SAT:</label>
            <div class="input-group">
              <a class="input-group-addon btn-sm btn-info" href="popup/setcondiciones?modulo=unidades&accion=insertd" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                
                <i class="fas fa-search" style="color: #ffffff;"></i>
              </a>
              <input type="text" style="font-size:1rem" id="clavesat" value="'.$row['u_clavesat'].'" class="form-control" placeholder="Clave SAT" name="clavesat" required="required" readonly="readonly" />
            </div>
          </div>
          <div class="col-md-1">
            <label>Guardar: </label>
            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i></button>
          </div>
        </div>
      </form>
    </div>
  </div>';
?>