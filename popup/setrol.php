<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
if(isset($_GET['id'])){
  include_once('../modulos/rolesprod.php');
  $rolesprod = new modelrolesprod();
  $rolesprod->select($_GET['id']);

  $title = "EDITAR";
  if($rolesprod->estatus == "A")
    $checked = "checked";
  else
    $checked = "";

$inpid = $_GET['id'];
}
else{
  $title = "AGREGAR";
  $checked="checked";
  $inpid = '';
}

/* echo '
<style>
.full-width-table {
  width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
}
.full-width-table th,
.full-width-table td {
  width: 33.33%;
  text-align: left;
  border: 1px solid #000;
}        
</style>'; */

echo '
<style>
  #myTableG {
    width: 100%;
    border-collapse: collapse;
  }

  #myTableG th, #myTableG td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
    /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
  }

  #myTableG tbody tr:nth-child(odd) td {
    /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
  }

  #myTableG tbody tr:nth-child(even) td {
    /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
  }
  
  #myTableG {
    font-size: 12px; /* Cambia el tamaño de letra deseado */
  }
</style>
';

$sqlp = 'SELECT * FROM pr_procesos WHERE ppr_estatus = "A"';
$resultp = setq($sqlp);

echo '
    <div class="container">
    <div class="col-12 alert alert-primary">'.$title.' UN ROL</div>
      <form class="row" method="post" action="index?modulo=rolesprod&accion=setrol">

        <div class="mb-5 col-md-4">
        <label>Nombre del rol:</label>
          <input type="text" id="nmb" class="form-control focus" value="'.$rolesprod->nmb.'" placeholder="Nombre del rol" name="nmb" required="required" >
        </div>

        <div class="mb-5 col-md-6">
        <label>Descripción::</label>
          <textarea rows="4" type="text" id="descripcion" class="form-control" data-toggle="tooltip" data-placement="top" placeholder="Descripción del rol" name="descripcion" required="required">'.$rolesprod->descripcion.'</textarea>
        </div>  

        <div class="mb-5 col-md-2">
        <label>Estatus:</label>
          <input class= "flipswitch form-control" type="checkbox" name="estatus" id="estatus" '.$checked.'>
        </div> 

        <input type="hidden" name="grupo" id="grupo" value="PRODUCCION">
        <input type="hidden" name="id" id="id" value="'.$inpid.'">

        <div class="col-12 alert alert-primary">PERMISOS DEL ROL</div>
        <div class="col-12">
        <table class="full-width-table" id="myTableG">
          <thead>
            <th colspan="3"><center>LISTADO DE ROLES</center></th>
          </thead>
          <tbody>';
          $contador = 1;
          echo '<tr>'; // Abre la primera fila
          while ($rowp = $resultp->fetch_array()) {

            if(!empty($inpid)){
              $existe = busca($rowp['ppr_id'], 'roles_permisos', 'rp_idrol = "'.$inpid.'" AND rp_proceso', 'COUNT(*)');
            if($existe > 0){
              $checkb = ' checked';
            } else{
              $checkb = '';
            }
            } else{
              $checkb = '';
            }

            $planta = busca($rowp['ppr_planta'],'pr_plantas', 'pp_id', 'pp_nmb');

            echo '<td style="width: 33%;"><input type="checkbox" '.$checkb.' class="form-check-input" id="rol'.$rowp['ppr_id'].'" name="rol'.$rowp['ppr_id'].'">&nbsp;&nbsp;' . $rowp['ppr_nmb'] . ' - ' . $planta . '</td>';
            if ($contador == 3) {
              echo '</tr><tr>'; // Cierra la fila actual y abre una nueva
              $contador = 1;
            } else {
              $contador++;
            }
          }
          echo '</tr>'; // Cierra la última fila
          
          echo '
          </tbody>
        </table>
        <div class="mb-5 col-md-12">
        <center><button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar</center>
      </div>
      </div>
      




      </form>
    </div>';

?>