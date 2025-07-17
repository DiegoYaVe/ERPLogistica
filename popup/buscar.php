<?php
ini_set('display_errors', 0);
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolut_ceo');
include('../funciones.php');

$salida = "";
//$query = 'SELECT * FROM cfdi_unidades ORDER BY cu_nmb ASC';
    $salida = '<div class="table table-responsive">
                <table class="table table-striped table-bordered table-hover" width="100%"><thead class="thead-secondary">
                <tr>
                  <th width="10%">Seleccionar</th>
                  <th width="30%">Clave unidad</th>
                  <th width="60%">Nombre</th>
                </tr>
               </thead>
               <tbody>';

  if(isset($_POST['consulta']) && !empty($_POST['consulta'])){

    $q = mb_strtoupper(trim($_POST['consulta']));
    if(strlen($q) > 2)
      $query = 'SELECT * FROM cfdi_unidades WHERE cu_nmb LIKE "%'.$_POST['consulta'].'%"
                OR cu_clave LIKE "%'.$_POST['consulta'].'%" ORDER BY cu_nmb ASC';
  }
  else $salida.="<tr><td colspan='3'><center>Introduzca un valor para busqueda</center></td></tr>";
  if($query){
    $resultado = setq($query);
    if($resultado->num_rows > 0){

      while($row = $resultado->fetch_assoc()){
        $i++;

        $sqlc = 'SELECT COUNT(*) FROM unidades
                WHERE u_clavesat = "'.$row['cu_clave'].'" ';
        $result = setq($sqlc);
        list($numcon) = $result->fetch_array();

        if($numcon > 0)
          $checkb = 'checked';
        else
          $checkb = "";

        $salida.='<tr>
        <td>
          <input type="checkbox" '.$checkb.' name="con'.$row['cu_id'].'" id="ch'.$row['cu_id'].'" onchange="setcondicion('.$row['cu_id'].');" />
          <input type="hidden" name="desc'.$row['cu_id'].'"  id="desc'.$row['cu_id'].'" value="'.$row['cu_clave'].'" />
        </td>
        <td>'.$row['cu_clave'].'</td>
        <td>'.$row['cu_nmb'].'</td>
        </tr>';
      }
      $salida.='</tbody></table>';
    }
    else{
      $salida = '<tr><td colspan="3"><center>Por favor escriba un indicio de palabra</center></td></tr>';
    }
  }

  echo $salida;
  //Finaliza tabla dinámica de registros
?>