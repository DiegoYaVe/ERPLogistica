<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
/* foreachdie(); */
$idfor = $_GET['id'];
$sql = 'SELECT * FROM pr_forecast WHERE prf_id = "'.$idfor.'"';
$result = setq($sql);
$row = $result->fetch_array();
$cpoint = busca($idfor, 'pr_checkpoints', 'pc_estatus = "N" AND pc_forecast', 'pc_id');
$fini = busca($idfor, 'pr_checkpoints', 'pc_estatus != "N" AND pc_forecast', 'MAX(pc_ffin)');
if(!$fini) $fini = $row['prf_fini'];
$ffin = date('Y-m-d',strtotime($row['prf_ffin']));

echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">Finalizar checkpoint '.$cpoint.' del forecast '.$row['prf_nmb'].'</div>
      <form method="post" action="index?modulo=forecast&accion=finalizarcp&id='.$idfor.'" id="form">
        <div class="row">
          <div class="mb-5 col-md-4">  
            <label>Fecha de inicio:</label>
            <input type="date" id="finicio" class="form-control" data-toggle="tooltip" data-placement="top" min="'.$fini.'" name="finicio" value="'.date('Y-m-d',strtotime($fini)).'" required="required"  '.$read.'>
          </div>
          <div class="mb-5 col-md-4">  
            <label>Fecha de fin:</label>
            <input type="date" id="ffinal" class="form-control" data-toggle="tooltip" data-placement="top" name="ffinal" max="'.$ffin.'" value="'.$ffin.'" required="required"  '.$read.'>
          </div>';
          echo '<div class="mb-5 col-md-2">
            <label>Guardar:</label>
            <button type="button" class="btn btn-primary" onclick="fechas()"><i class="fa fa-save"></i>
          </div>
          </div>
          </form>';

        $sql = 'SELECT * FROM pr_checkpoints WHERE pc_forecast = "'.$idfor.'" AND pc_estatus != "N"';
        $result = setq($sql);
        if($result ->num_rows > 0){
          echo '<div>
          <table class="table table-hover">
            <thead class="bg-primary text-white">
              <tr>
                <th colspan="3">
                  <center>Checkpoints anteriores </center>
                </th>
              </tr>
              <tr>
                <th>Checkpoint </th>
                <th>Fecha de inicio </th>
                <th>Fecha de fin </th>
              </tr>
            </thead>
            <tbody>';
              while($row= $result -> fetch_array()){
                echo '<tr>
                  <td>'.$row['pc_id'].' </td>
                  <td>'.fecha_formato($row['pc_fini'], false, true).' </td>
                  <td>'.fecha_formato($row['pc_ffin'], false, true).' </td>
                </tr>';
              }
            echo '</tbody>
          </table>
        </div>';  
        }
      
    echo '</div>
    </div>';

?>
<script>
  function fechas(){
    var fini = document.getElementById("finicio");
    var ffin = document.getElementById("ffinal");
    var nmb = document.getElementById("nmb");
    var form = document.getElementById("form");

    var fechaini = new Date(fini.value);
    var fechafin = new Date(ffin.value);
    if(fechafin < fechaini){
      alert('La fecha de fin no puede ser menor que la de inicio.');
    }else if(!fini.checkValidity()){
      alert('La fecha de inicio no puede ser menor que la fecha de fin del checkpoint anterior.');
    }else if(!fini.checkValidity()){
      alert('La fecha de fin no puede ser mayor a la fecha de fin del forecast.');
    }else{
      form.submit();
    }
  }

</script>