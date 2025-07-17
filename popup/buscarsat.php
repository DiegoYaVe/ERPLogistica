<?php
ini_set('display_errors', 1);
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolut_ceo');
include('../funciones.php');


$salida = "";
//$query = 'SELECT * FROM cfdi_unidades ORDER BY cu_nmb ASC';
    $salida = '<div class="table table-responsive">
                <table class="table table-striped table-bordered table-hover" width="100%"><thead class="thead-secondary">
                <tr style="background: white;">
                  <th width="30%"><b>Seleccionar</b></th>
                  <th width="30%"><b>Clave SAT</b></th>
                  <th width="40%"><b>Nombre</b></th>
                </tr>
               </thead>
               <tbody style="background: white;">';

  if(isset($_POST['consulta']) && !empty($_POST['consulta'])){

    $q = mb_strtoupper(trim($_POST['consulta']));
    if(strlen($q) > 3)
      $query = 'SELECT * FROM prodsat WHERE p_claveps LIKE "%'.$_POST['consulta'].'%"
                OR p_alias LIKE "%'.$_POST['consulta'].'%" OR p_nmb LIKE "%'.$_POST['consulta'].'%" ORDER BY p_claveps ASC';
  }
  else $salida.="<tr><td colspan='3'><center>Introduzca un valor para busqueda</center></td></tr>";
  if($query){
    $resultado = setq($query);
    if($resultado->num_rows > 0){

      while($row = $resultado->fetch_assoc()){
        $i++;

        $sqlc = 'SELECT COUNT(*) FROM articulos
                WHERE a_clavesat = "'.$row['p_claveps'].'"';
        $result = setq($sqlc);
        list($numcon) = $result->fetch_array();

        if($numcon > 0)
          $checkb = 'checked';
        else
          $checkb = "";
        $valueB = "'".$row['p_claveps']."'";

        $salida.='<tr style="background: white;">
        <td>
          <input type="checkbox" '.$checkb.' name="con'.$row['p_claveps'].'" id="ch'.$row['p_claveps'].'" onchange="setcondicion('.$valueB.');" />
          <input type="hidden" name="desc'.$row['p_claveps'].'"  id="desc'.$row['p_claveps'].'" value="'.$row['cu_clave'].'" />
        </td>
        <td>'.$row['p_claveps'].'</td>
        <td>'.$row['p_nmb'].'</td>
        </tr>';
      }
      $salida.='
      </tbody></table>';
      $salida .= '
      <div style="display: flex; justify-content: center;">
      <span>Si deseas ver el listado completo, da clic aquí&nbsp;</span>
      <button class="btn btn-small btn-success" onclick="gotoprodsat();"><i class="fas fa-search-plus"></i></button>
      </div>
      <div style="display: flex; justify-content: center;">
      <br><span>Al finalizar, deberas regresar al registro del artículo</span>
      </div>';
    }
    else{
      $salida = '
      <div style="display: flex; justify-content: center;">
      <span> No hay coincidencias.
      Si deseas ver el listado completo, da clic aqui&nbsp;</span>
          <button class="btn btn-sm btn-success" onclick="gotoprodsat();">
          <i class="fa fa-search-plus"></i></button>
      </div>
      <div style="display: flex; justify-content: center;">
      <span>Al finalizar, deberas regresar al registro del artículo</span>
      </div>';
    }
  }

  echo $salida;
  //Finaliza tabla dinámica de registros
  echo '<script>
          function gotoprodsat(){
            window.close();
            window.opener.location = "../index?modulo=prodsat&accion=indexps&nmb='.$_POST['consulta'].'";
          }
        </script>';
?>