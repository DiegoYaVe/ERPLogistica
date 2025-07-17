<?php
ini_set('display_errors', 0);
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolut_ceo');
include_once('../funciones.php');
$empresa = $_POST['empresa'];
$articulo = $_POST['articulo'];

$salida = "";
//$query = 'SELECT * FROM cfdi_unidades ORDER BY cu_nmb ASC';
    $salida = '<div class="table table-responsive">
                <table class="table table-striped table-bordered table-hover" width="100%">
                <thead class="thead-secondary">';

  if(isset($_POST['consulta']) && !empty($_POST['consulta'])){

    $q = mb_strtoupper(trim($_POST['consulta']));
    if(strlen($q) > 2)
      $query = 'SELECT * FROM articulos_colores WHERE ac_nmbcolor LIKE "%'.$_POST['consulta'].'%"
                ORDER BY ac_nmbcolor ASC AND ac_empresa = "'.$empresa.'"';
  }
  echo '  <tr>
            <th width="10%">Seleccionar</th>
            <th width="80%">Nombre del color</th>
            <th width="10%">Color</th>
          </tr>
        </thead><tbody>';
  if($query){
    $resultado = setq($query);
    if($resultado->num_rows > 0){

      while($row = $resultado->fetch_assoc()){
        $i++;

        $sqlc = 'SELECT COUNT(*) FROM articulos_variantes
                 WHERE av_articulo = "'.$articulo.'" AND av_articulo IN
                 (SELECT a_id FROM articulos WHERE a_empresa = "'.$empresa.'" )';
        $result = setq($sqlc);
        list($numcon) = $result->fetch_array();

        if($numcon > 0)
          $checkb = 'checked';
        else
          $checkb = "";

        $salida.='<tr>
        <td>
          <input type="checkbox" '.$checkb.' name="con'.$row['ac_id'].'" id="ch'.$row['ac_id'].'" onchange="setcondicion('.$row['ac_id'].');" />
        </td>
        <td>'.$row['ac_nmbcolor'].'</td>
        <td style="background:'.$row['ac_hexadecimal'].'">&nbsp;</td>
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