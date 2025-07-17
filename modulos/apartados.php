<?php
//ini_set('display_errors','1');
$GLOBALS['menu'] = "apartados";

class apartados{
  var $model;
  var $view;

function __construct(){ // constructor
  $this->model = new modelapartados($obj); // crea modelo
}
function index() {
    $this->view = new viewapartados($this->model);
    $this->view->existencias();
}

}
class modelapartados{


}

class viewapartados{
  var $model;

function __construct($model) {
  $this->model = $model;
}

function existencias(){

  if (!isset($_POST['categoria'])) $_POST['categoria'] = NULL;
  if (!isset($_POST['articulo'])) $_POST['articulo'] = NULL;
  if (!isset($_REQUEST['almacen'])) $_REQUEST['almacen'] = NULL;
  $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
  
  $nuevo = ' <a target="_blank" href="formats/pdfexistencias.php">
              <button type="button" class="btn btn-sm btn-danger mb-1 mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
            </a>
            <a href="formats/xlsxexistencias.php">
              <button type="button" class="btn btn-sm btn-success mb-1 mr-1"><i class="fas fa-file-excel"></i> Excel</button>
            </a>';

    $filtro = '
    <form autocomplete="off" action="?modulo=apartados&accion=index" method="post" id="filtro" onsubmit="return checkSubmitenviar();" class="">
      <div class="mb-5">
        <label class="mr-sm-2">Producto:</label>
        <input type="text" name="articulo" class="form-control" value="'.$_POST['articulo'].'" onfocus="this.select();" placeholder="Escribe el nombre del artículo"/>
      </div>
      <div class="mb-5" style="max-width: 250px">
        <label for="">Categoria:</label>
        <select id="pref-orderby" class="form-control" name="categoria" placeholder="Categoria">
          <option value="">TODAS LAS CATEGORIAS</option>';

          $sql = 'SELECT * FROM categorias WHERE cat_estatus = "A"  ORDER BY cat_nmb';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if($_POST['categoria'] == $row['cat_id']) $sel = "selected"; else $sel = "";
            $filtro.='<option value="'.$row['cat_id'].'" '.$sel.'>'.$row['cat_nmb'].'</option>';
          }
          $filtro.='
        </select>
      </div>
      <div class="mb-5" style="max-width: 250px">
        <label for="">Almacén:</label>
        <select id="pref-orderby" class="form-control" name="almacen" placeholder="almacen">
          <option value="">TODOS LOS ALMACENES</option>';

          $sql = 'SELECT * FROM almacenes WHERE a_estatus = "A"  ORDER BY a_nmb';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if($_POST['almacen'] == $row['a_id']) $sel = "selected"; else $sel = "";
            $filtro.='<option value="'.$row['a_id'].'" '.$sel.'>'.$row['a_nmb'].'</option>';
          }
          $filtro.='
        </select>
      </div>
      <div class="mb-5">
      <label for="">Acciones:</label><br>
      <button type="submit"  class="btn btn-info">
      <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
    </button>
    <a href="?modulo=apartados&accion=index">
      <button type="button" class="btn btn-warning">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Limpiar
      </button> 
    </a>
    </div>
    </form>';
  toolbar("ARTÍCULOS APARTADOS",'',$filtro);

    $sql2 = '';
    if ($_POST['categoria']) {
      $sql2.='AND a_categoria = "' . $_POST['categoria'] . '" ';
    }
    if ($_POST['articulo']) {
      $sql2.='AND a_nmb LIKE "%' . $_POST['articulo'] . '%" OR a_modelo LIKE "%' . $_POST['articulo'] . '%" OR a_cb LIKE "%' . $_POST['articulo'] . '%" ';
    }

    $admin="";
    $sqlal = 'SELECT * FROM almacenes where a_estatus="A" ';
    if($_REQUEST['almacen']) $sqlal.=' AND a_id = "'.$_REQUEST['almacen'].'" ';
    $resultal = setq($sqlal) or die($sqlal);
    while ($rowal = $resultal->fetch_array()) {
      $datosla = existenciaxestatus($rowal['a_id'], "LA");
      //$datosla = json_decode($la); 
      $datosva = existenciaxestatus($rowal['a_id'], "VA");
      //$datosva = json_decode($va); 
      $datosvp = existenciaxestatus($rowal['a_id'], "VP");
      //$datosvp = json_decode($vp); 
      if ($rowal['a_vendible'] == 1) $ven = 'vendible'; 
      else $ven = 'No vendible';
      $sqlex = 'SELECT a_id id, a_nmb nmb, a_modelo modelo, cat_nmb categoria FROM articulos INNER JOIN categorias ON a_categoria = cat_id WHERE a_tipoprod != "M" AND a_inventariado = "1" ';
      if($_REQUEST['categoria']) $sqlex.=' AND cat_id = "'.$_REQUEST['categoria'].'"';
      if($_REQUEST['articulo']) $sqlex.=' AND (a_nmb LIKE "%'.$_REQUEST['articulo'].'%" OR a_cb LIKE "%'.$_REQUEST['articulo'].'%")';
      $sqlex .= 'ORDER BY a_nmb ASC';
      $resultex = setq($sqlex) or die($sqlex);

      /* $sqlcomp = 'SELECT SUM(e_cantidad) FROM existencias INNER JOIN articulos ON a_id = e_articulo
                  WHERE a_estatus = "A" '.$admin.' AND  e_almacen = "' . $rowal['a_id'] . '"
                  ' . $sql2 . '
                  GROUP BY a_id ORDER BY a_nmb';
      $resultcomp = setq($sqlcomp) or die($sqlcomp);
      $exi = $resultcomp->fetch_array(); */
      echo '
      <div>
      <div class="table-responsive">
        <table width="90%" class="mb-0 table table-hover" id="myTable">
          <thead class="tfill-blue">
            <tr>
              <th colspan="3">Productos en almacen ' . $rowal['a_nmb'] . ' "' . $ven . '"</th>
              <th colspan="6">Existencias</th>
            </tr>
            <tr>
              <th width="35%">Artículo</th>
              <th width="10%">Modelo</th>
              <th width="15%">Categoria</th>
              <!-- <th width="8%">Libre</th> -->
              <th width="8%">Apartado</th>
              <!-- <th width="8%">Físico</th> -->
              <th width="8%"></th>
              ';
            echo '</tr>
          </thead> 
          <tbody>'; 
              
              while ($row = $resultex->fetch_array()) {
                $dif = 0;
                $var = busca($row['id'], 'articulos_variantes', 'av_articulo', 'COUNT(*)');
                if($var > 0){ 
                  $sqlvar = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$row['id'].'"';
                  $resultvar = setq($sqlvar);
                  while($rowvar = $resultvar -> fetch_array()){

                    $articulo = $row['nmb'].' '.$rowvar['av_nmb'];
                    $modelo = $rowvar['av_modelo'];

                    $vendido_abonado = intval($datosva[$row['id']][$modelo]);
                    $vendido_pagado = intval($datosvp[$row['id']][$modelo]);

                    $apartados = $vendido_abonado + $vendido_pagado;

                    if($apartados > 0){

                    $existencia = existenciaModelo($row['id'], $rowal['a_id'], $rowvar['av_modelo']);
                    /* if(floatval($existencia) == 0) $libre_apartado = 0;
                    else $libre_apartado = $datosla[$row['id']][$modelo];  */
                    $libre = $existencia; // - $libre_apartado
                    if(floatval($libre) < 1) {
                      $dif = abs($libre);
                      $libre = 0;
                    }

                    $reponer = busca($row['id'], 'existencia_pendiente', 'xp_estatus = "N" AND xp_modelo = "'.$rowvar['av_modelo'].'" AND xp_almacen = "'.$rowal['a_id'].'" AND xp_articulo', 'xp_cantidad');
                    $reponer += $dif;
                    //die("Vendido abonado: ".$vendido_abonado. " y Vendido pagado: ".$vendido_pagado);
                    echo '<tr>
                          <th>' . $articulo . '</th>
                          <th>' . $modelo . '</th>
                          <td>' . $row['categoria']. '</td>
                          <td class="text-center bg-success">' . number_format($apartados, 0) . '</td>';
                    //echo '<td class="text-center"> '.number_format($vendidos, 0).' </td>';
                    //echo '<td class="text-center"> '.number_format($disponibles, 0).' </td> </td>';
                    echo '
                  <td>
                  <center>
                  <a data-fancybox="" data-type="ajax" data-src="popup/articuloapartado.php?id='.$row['id'].'&model='.$rowvar['av_modelo'].'" href="javascript:;">
                    <button type="button" class="btn btn-sm btn-info mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Ver todas las remisiones que tienen este artículo como apartado">
                      <i class="fa fa-eye"></i>
                    <!-- <span class="badge bg-danger">49</span> --></button>
                  </a>
                  </center>
                  </td>';
                    echo'</tr>';
                  }
                  }
                } else {
                  $articulo = $row['nmb'];
                  $modelo = "";
                  //echo 'datos LA: '.$datosla[$row['id']][$row['modelo']];
                  //echo 'articulo: '.$row['id'].' modelo: '.$row['modelo'];
                  
                  $existencia = existencia($row['id'], $rowal['a_id']);
                  /* if(floatval($existencia) == 0) $libre_apartado = 0;
                  else $libre_apartado = $datosla[$row['id']][$modelo]; */
                  $libre = $existencia; // - $libre_apartado
                  if(floatval($libre) < 1){ 
                    $dif = abs($libre);
                    $libre = 0;
                  }
                  
                  $vendido_abonado = $datosva[$row['id']][$modelo];
                  $vendido_pagado = $datosvp[$row['id']][$modelo];

                  $apartados = $vendido_abonado + $vendido_pagado;

                  if($apartados > 0){

                  $disponibles = $libre + $vendido_abonado - $dif + $vendido_pagado; //+ $libre_apartado 
                  $reponer = busca($row['id'], 'existencia_pendiente', 'xp_almacen = "'.$rowal['a_id'].'" AND xp_articulo', 'xp_cantidad');
                  $reponer += $dif;

                  //die("Vendido abonado: ".$vendido_abonado. " y Vendido pagado: ".$vendido_pagado);
                  
                  echo '<tr>
                        <th>' . $articulo . '</th>
                        <th>' . $modelo . '</th>
                        <td>' . $row['categoria'] . '</td>
                        <td class="text-center bg-success">' . number_format($apartados, 0) . '</td>';
                  //echo '<td class="text-center"> '.number_format($vendido_abonado+$vendido_pagado, 0).' </td>';
                  //echo '<td class="text-center"> '.number_format($disponibles, 0).' </td> </td>';
                  echo '
                  <td>
                  <center>
                  <a data-fancybox="" data-type="ajax" data-src="popup/articuloapartado.php?id='.$row['id'].'" href="javascript:;">
                    <button type="button" class="btn btn-sm btn-info mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Ver todas las remisiones que tienen este artículo como apartado">
                      <i class="fa fa-eye"></i>
                    <!-- <span class="badge bg-danger">49</span> --></button>
                  </a>
                  </center>
                  </td>';
                  echo'</tr>';
                }
                }
              }
            
          }
          echo '<tbody>
        </table>
        </div>';
}

}
?>