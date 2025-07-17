<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
/* foreachdie(); */
$idfor = $_GET['id'];
$sql = 'SELECT * FROM pr_forecast WHERE prf_id = "'.$idfor.'"';
$result = setq($sql);
$row = $result->fetch_array();
$datos = array();
$i = 0;
$sql = 'SELECT * FROM pr_forecastd WHERE prfd_forecast = "'.$idfor.'"';
$result = setq($sql);
$imp='';
$dtipo = array('A' => 'Área', 'V' => 'Volumen', 'P' => 'Pieza');
$utipo = array('A' => 'mts cuadrados', 'V' => 'metros cubicos', 'P' => 'unidades');

while($row = $result -> fetch_array()){
  $sqlmp = 'SELECT * FROM articulos_composicion WHERE ac_articulo = "'.$row['prfd_articulo'].'" AND ac_modelo = "'.$row['prfd_modelo'].'"';
  $resultmp = setq($sqlmp);
  while($rowmp = $resultmp -> fetch_array()){
    $tipo= busca($rowmp['ac_mp'], 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
    if($tipo == "A"){
      $cant = $rowmp['ac_largo']*$rowmp['ac_ancho']*$rowmp['ac_cantidad'];
    } else if($tipo == "V"){
      $cant = $rowmp['ac_largo']*$rowmp['ac_ancho']*$rowmp['ac_alto']*$rowmp['ac_cantidad'];
    } else {
      $cant = $rowmp['ac_cantidad'];
    }
    if(!$datos[$rowmp['ac_mp']]) $datos[$rowmp['ac_mp']] = 0;
    $datos[$rowmp['ac_mp']] += ($cant*$row['prfd_cantidad']);
    $i++;
  }
}

echo '
  <div class="container col-8">  
    <div class="col-12 alert alert-primary">Materia prima estimada para el Forecase '.$row['prf_nmb'].'</div>
      <div class="row">';
      if($i == 0){
        echo '<tr><td colspan="2"> <div class="alert alert-danger"> <center>Los artículos para este forecast no tienen configurado su composición</center></div> </tr>';
      } else {
        echo '<table class="table table-hover table-responsive" id="myTableE">';
          echo '<thead class="bg-primary text-white">
            <tr>
              <th style="width:80%" class="p-3">Materia prima</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody>';
              $sqlres = 'SELECT * FROM pr_materiaprima WHERE pmp_estatus = "A"';
              $resultres = setq($sqlres);
              while($rowres = $resultres -> fetch_array()){
                if($datos[$rowres['pmp_id']]){
                  echo '<tr>
                    <td>
                      '.$rowres['pmp_nmb'].' - Material: '.$rowres['pmp_material'].' - Unidad: '.$dtipo[$rowres['pmp_tipo']].'<br>'.'
                    </td>
                    <td>
                      '.$datos[$rowres['pmp_id']].' '.$utipo[$rowres['pmp_tipo']].'
                    </td>
                    </tr>';
                }
              }
          echo '</tbody>';
        echo'</table>';
      }
      echo $imp;
      echo '</div>
    </div>
  </div>';

?>
<style>
  #myTableE {
    width: 100%;
    border-collapse: collapse;
  }

  #myTableE th, #myTableE td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
    /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
  }

  #myTableE tbody tr:nth-child(odd) td {
    background-color: #ffffff; /* Fondo blanco para las filas impares */
  }

  #myTableE tbody tr:nth-child(even) td {
    background-color: #f9f9f9; /* Fondo gris claro para las filas pares */
  }
  
  #myTableE{
    font-size: 13px; /* Cambia el tamaño de letra deseado */
  }
</style>