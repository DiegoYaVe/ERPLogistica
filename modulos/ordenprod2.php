<?php
ini_set('display_errors', 1);
class ordenprod2{

function __construct(){
    $this->model = new modelordenprod2($obj);
}
function index(){
    //foreachdie();
    
    if(!isset($_REQUEST['folio'])) $_REQUEST['folio'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "";
    if(!isset($_REQUEST['forecast'])) $_REQUEST['forecast'] = NULL;

    if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-30 days'));
    if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d', strtotime('+30 days'));

    $this->model->result($_REQUEST['folio'],$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'], $_REQUEST['forecast']);
    $this->view = new viewordenprod2($this->model);
    $this->view->browse($_REQUEST['folio'],$_REQUEST['estatus'],$_REQUEST['fini'], $_REQUEST['ffin'], $_REQUEST['forecast']);
}

function show(){
  $id = $_GET['id'];
  $this->model->select($id);
  $this->model->resultprocesos($id);
  $this->view = new viewordenprod2($this->model);
  $this->view->show();
}

function insert(){
  /* foreachdie(); */
  $folioop = getmax('po_folio','pr_ordenprod');
  $acrof = busca("1",'empresas','e_id','e_siglas').'-ORDP';
  if($folioop == "1"){
    $folioop = $acrof.str_pad($folioop,6,"0",STR_PAD_LEFT);
  }  
  if($_POST['tipo'] != "O") $_POST['texto'] = "";

  $this->model->setdata('', $folioop, $_POST['articulo'], $_POST['modelo'], $_POST['cantidad'], $_POST['tipo'], $_POST['texto'], date('Y-m-d H:i:s'), $_SESSION['uid'], "N", '', $_POST['encargado'], $_POST['cortador'], $_POST['fentrega'], $_POST['notasalida'], $_POST['almacen']);
  $this->model->insert();

  $id = getmax('po_id', 'pr_ordenprod', false, false);

  $this->model->ligar($id, $_POST['forecastd'], $_POST['cantidad']);

  $this->model->insertarprocesos($id,$_POST['articulo'],$solicitud='1',$tiposoli='P',$_POST['encargado']);

  redirect('?modulo=ordenprod2&accion=show&id='.$id);
}
function update(){
  $id = $_REQUEST['id'];

  $this->model->setdata($id, $_POST['folio'], $_POST['articulo'], $_POST['modelo'], $_POST['cantidad'], $_POST['tipo'], $_POST['texto'], date('Y-m-d H:i:s'), $_SESSION['uid'], "N", '', $_POST['encargado'], $_POST['cortador'], $_POST['fentrega'], $_POST['notasalida'], $_POST['almacen']);
  $this->model->update();

  $sql = 'UPDATE pr_forecastdorden SET pfo_cantidad = "'.$_POST['cantidad'].'" WHERE pfo_ordenprod = "'.$id.'"';
  setq($sql);

  redirect('?modulo=ordenprod2&accion=show&id='.$id);
}

function activarop(){
  $id= $_GET['id'];
  $sql = 'UPDATE pr_ordenprod SET po_estatus = "P" WHERE po_id ="'.$id.'"';
  setq($sql);
  redirect('?modulo=ordenprod2&accion=show&id='.$id);
}

function verarticulos(){
  $id = $_GET['idp']; //Id del la orden de producción
  $proceso = $_GET['proceso']; //Id del la orden de producción
  $this->model->select($id);
  $this->model->resultartop($id, $proceso);
  $this->view = new viewordenprod2($this->model);
  $this->view->articulosop();
}

}

class modelordenprod2{
function insertarprocesos($ordenp,$articulo,$solicitud,$tiposoli,$supervisor){
  $sql = 'SELECT * FROM articulos_produccion WHERE apr_articulo = "'.$articulo.'" ';
  $result = setq($sql);
  while($row = $result->fetch_array()){
    $sqlin = 'INSERT INTO pr_procesosop SET
              pp_ordenp = "'.$ordenp.'",
              pp_solicitud = "'.$solicitud.'",
              pp_tiposolicitud = "'.$tiposoli.'",
              pp_estatus = "N",
              pp_proceso = "'.$row['apr_proceso'].'",
              pp_fase = "'.$row['apr_fase'].'",
              pp_supervisor = "'.$supervisor.'",
              pp_orden = "'.$row['apr_orden'].'",
              pp_tipoproceso = "'.$row['apr_tipo'].'",
              pp_planeado = "1",
              pp_bloqueo = "'.$row['apr_obligatorio'].'",
              pp_obs = ""';
    setq($sqlin);
    
    /* pp_fini = "'.$fini.'",
    pp_ffin = "'.$ffin.'",
    pp_operador = "'.$operador.'",
    pp_cantini = "'.$cantini.'",
    pp_cantfini = "'.$cantfin.'", */

  }
}
function resultprocesos($ordenp){
  $sql = 'SELECT * FROM pr_procesos WHERE pp_ordenp = "'.$ordenp.'" ';
  $this->result = setq($sql);

}
function result($nmb,$estatus,$fini, $ffin, $forecast){ //filtro
  $bloque = 50;
  $sql = 'SELECT * FROM pr_ordenprod  WHERE po_fentrega >= "'.$fini.'" AND po_fentrega <= "'.$ffin.'"';
  if($nmb) $sql.= ' AND po_folio LIKE "%'.trim($nmb).'%"';
  if($estatus) $sql.= ' AND po_estatus = "'.$estatus.'"';
  
  if($forecast) {
    $sql.= ' AND po_id IN (SELECT pfo_ordenprod FROM pr_forecastdorden WHERE pfo_forecastd IN (SELECT prfd_id FROM pr_forecastd INNER JOIN pr_forecast ON prf_id = prfd_forecast WHERE prf_nmb LIKE "%'.trim($forecast).'%"))';
  }
  $sql.=' ORDER BY po_folio ASC ';
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM pr_ordenprod WHERE po_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['po_id'];
    $this->folio = $row['po_folio'];
    $this->articulo = $row['po_articulo'];
    $this->modelo = $row['po_modelo'];
    $this->cantidad = $row['po_cantidad'];
    $this->tipo = $row['po_tipo'];
    $this->obstipo = $row['po_obstipo'];
    $this->fgen = $row['po_fgen'];
    $this->ugen = $row['po_ugen'];
    $this->estatus = $row['po_estatus'];
    $this->finventario = $row['po_finventario'];
    $this->encargado = $row['po_encargado'];
    $this->cortador = $row['po_cortador'];
    $this->fentrega = $row['po_fentrega'];
    $this->notasalida = $row['po_notasalida'];
    
}

function setdata($id,$folio, $articulo, $modelo, $cantidad, $tipo, $obstipo, $fgen, $ugen, $estatus, $finventario, $encargado, $cortador, $fentrega, $notasalida, $almacen){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->folio = $folio;
    $this->articulo = $articulo;
    $this->modelo = $modelo;
    $this->cantidad = $cantidad;
    $this->tipo = $tipo;
    $this->obstipo = $obstipo;
    $this->fgen = $fgen;
    $this->ugen = $ugen;
    $this->estatus = $estatus;
    $this->finventario = $finventario;
    $this->encargado = $encargado;
    $this->cortador = $cortador;
    $this->fentrega = $fentrega;
    $this->notasalida = $notasalida;
    $this->almacen = $almacen;
}
function insert(){
    $sql = 'INSERT INTO pr_ordenprod SET
            po_id = "'.$this->id.'",
            po_folio = "'.$this->folio.'",
            po_articulo = "'.$this->articulo.'",
            po_modelo = "'.$this->modelo.'",
            po_cantidad = "'.$this->cantidad.'",
            po_tipo = "'.$this->tipo.'",
            po_obstipo = "'.$this->obstipo.'",
            po_fgen = "'.$this->fgen.'",
            po_ugen = "'.$this->ugen.'",
            po_estatus = "'.$this->estatus.'",
            po_finventario = "'.$this->finventario.'",
            po_encargado = "'.$this->encargado.'",
            po_cortador = "'.$this->cortador.'",
            po_fentrega = "'.$this->fentrega.'",
            po_almacen = "'.$this->almacen.'",
            po_notasalida = "'.$this->notasalida.'"';
    setq($sql);

    $this->model->id = getmax('po_id', 'pr_ordenprod', false, false);
}
function update(){
  $sql = 'UPDATE pr_ordenprod SET
        po_cantidad = "'.$this->cantidad.'",
        po_tipo = "'.$this->tipo.'",
        po_obstipo = "'.$this->obstipo.'",
        po_estatus = "'.$this->estatus.'",
        po_finventario = "'.$this->finventario.'",
        po_encargado = "'.$this->encargado.'",
        po_cortador = "'.$this->cortador.'",
        po_fentrega = "'.$this->fentrega.'",
        po_almacen = "'.$this->almacen.'",
        po_notasalida = "'.$this->notasalida.'" 
        WHERE po_id = "'.$this->id.'"';
  setq($sql);

}

function ligar($orden, $forecastd, $cantidad){
  $sql = 'INSERT INTO pr_forecastdorden SET pfo_forecastd = "'.$forecastd.'", pfo_ordenprod = "'.$orden.'", pfo_cantidad = "'.$cantidad.'"';
  setq($sql);
}

function comprueba($idop){
  $datos = array();
  $datos['error'] = 'OK';
  $sqlop = 'SELECT po_articulo, po_modelo, po_cantidad, po_almacen FROM pr_ordenprod WHERE po_id = "'.$idop.'"';
  $resultop = setq($sqlop);
  list($articulo, $modelo, $cantidad, $almacen) = $resultop -> fetch_array();
  $sqlmp = 'SELECT * FROM articulos_composicion WHERE ac_articulo = "'.$articulo.'" AND ac_modelo = "'.$modelo.'"';
  $resultmp = setq($sqlmp);
  while($rowmp = $resultmp -> fetch_array()){
    $suma = array();
    $existencia = existenciamp($rowmp['ac_mp'], $almacen);  
    $tipo = busca($rowmp['ac_mp'], 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
    if(!$suma[$rowmp['ac_mp']]) $suma[$rowmp['ac_mp']] = 0;
    if($tipo == "A"){
      //$suma[$rowmp['ac_mp']]
    } else if($tipo == "V"){
      
    }if($tipo == "P"){
      
    }
  }
}

function resultartop($ordenp, $proceso){
  if(isset($_GET['tipo'])){
    if($_GET['tipo'] == "I"){
      $estatus = '"N","A","F"';
    } else if($_GET['tipo'] == "P"){
      $estatus = '"A"';
    } else{
      $estatus = '"A","F"';
    }
    $sqlf = ' AND pra_estpa IN ('.$estatus.')';
  } else{
    $sqlf = '';
  }
  $sql = 'SELECT * FROM pr_articulosop WHERE pra_op = "'.$ordenp.'" AND pra_pa = "'.$proceso.'"'.$sqlf;
  $this->result = setq($sql);

}

}

class viewordenprod2 {
    var $model;

function __construct($model) {

    $this->model = $model;
    $this->aest = array("N" => "Nueva","P" => "En ejecución", "F" => "Finalizado", "C" => "Cancelada");
    $this->fest = array("N" => "bg-warning","P" => "bg-primary", "F" => "bg-success", "C" => "bg-danger");
?>
<script language="JavaScript">
function checkSubmitedit() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
}
</script>
<?php
}
function browse($folio,$estatus,$fini, $ffin, $forecast) {
   //Sección: E1 Encabezado - Botones de acción
    if(!isset($estatus) || $estatus == "") $estt = "selected";
    elseif($estatus == "N") $estn = "selected";
    elseif($estatus == "P") $estp = "selected";
    elseif($estatus == "P") $estf = "selected";
    else $estc = "selected";

    //Sección: E2 Encabezado - Filtros
    ?>
    <script>
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 78) {
          var btn = document.getElementById("nuevo");
          btn.click();
          e.preventDefault();
        }
      });
  
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 76) {
          var btn = document.getElementById("filtrar");
          btn.click();
          $("#pref-search").focus();
          e.preventDefault();
        }
      });
  
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 82) {
          window.location.reload();
        }
      });
    </script>
    <?php
    
      /* $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setcategoria.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>'; */
      /* $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=1&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>'; */
      /* $boton = '<button class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Las categorias o familias de produtos, permiten realizar una división en el tratamiento y estadístico de los productos" data-trigger="click" >
        <i class="fas fa-question-circle"></i>
      </button>'; */
      
    $filtrar = '
              <form class="form-inline" role="form" method="post" action="?modulo=ordenprod2&accion=index" id="filtro">
                <div class="mb-5">
                  <label for="">Filtrar por folio:</label>
                  <input type="text" class="form-control" id="pref-search" name="folio" value="'.$folio.'" placeholder="Buscar por folio">
                </div>
                <div class="mb-5">
                  <label for="">Filtrar por Forecast:</label>
                  <input type="text" class="form-control" id="pref-search" name="forecast" value="'.$forecast.'" placeholder="Buscar por nombre del forecast">
                </div>
                <div class="mb-5">
                  <label for="">Desde:</label>
                  <input type="date" class="form-control" id="pref-search" name="fini" value="'.$fini.'" placeholder="Desde">
                </div>
                <div class="mb-5">
                  <label for="">Hasta:</label>
                  <input type="date" class="form-control" id="pref-search" name="ffin" value="'.$ffin.'" placeholder="Hasta">
                </div>
                <div class="mb-5">
                  <label for="tipom">Estatus</label>
                  <select class="form-control" name="estatus" id="estatus" >
                    <option value="" '.$estt.'>Todos</option>
                    <option value="N" '.$estn.'>Nuevas</option>
                    <option value="A" '.$estp.'>En proceso</option>
                    <option value="P" '.$estf.'>Finalizadas</option>
                    <option value="C" '.$estc.'>Cancelados</option>
                  </select>
                </div>
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                <button type="submit" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=ordenprod2&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';

        toolbar($_GET['modulo'],"", $filtrar);

  echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table table-striped" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th width="13%">Folio</th>
                    <th width="22%">Articulo</th>
                    <th width="13%">Cantidad</th>
                    <th width="13%">Fecha de entrega</th>
                    <th width="13%">Encargado</th>
                    <th width="13%">Estatus</th>
                    <th width="13%"></th>
                  </tr>
                </thead>'; 
    while ($row = $this->model->result->fetch_array()) {
      $nmb = busca($row['po_articulo'], 'articulos', 'a_id', 'a_nmb');
      if($row['po_modelo'] != "") $nmb .= ' '.busca($row['po_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['po_modelo'].'" AND av_articulo', 'av_nmb');
        echo '<tr>
        <td>'.$row['po_folio'].'</td>
        <td>'.$nmb.'</td>
        <td>'.$row['po_cantidad'].'</td>
        <td>'.fecha_formato($row['po_fentrega'], false, false).'</td>
        <td>'.busca($row['po_encargado'], 'pr_empleados', 'pe_id', 'CONCAT(pe_nmb)').'</td>
        <td class="alert '.$this->fest[$row['po_estatus']].'">'.$this->aest[$row['po_estatus']].'</td>
        <td>';
        /*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ; */
          echo '
          <a href="?modulo=ordenprod2&accion=show&id='.$row['po_id'].'">
            <button type="button" class="btn btn-info">
              <i class="fas fa-eye" style="color: #ffffff;"></i>
            </button>
          </a>';

          echo '
          <a href="?modulo=ordenprod2&accion=verarticulos&id='.$row['po_id'].'" target="_blank">
            <button type="button" class="btn btn-success">
              <i class="far fa-newspaper" style="color: #ffffff;"></i>
            </button>
          </a>';

        echo '</td>
        ';
    }
    echo '</table>
    
    <script>
    $(document).ready(function () {
      var windowHeight = $(window).height();
      
        $("#myTable").DataTable( {
            paging: true,
            scrollY: windowHeight * 0.5,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            pageLength: 50,
        });
      });
      </script>';
  
}

function show(){
    $leyenda = 'Mapa de procesos de la orden de producción '.$this->model->folio." ";
    $botones = '';
    $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setordenprod2.php?ordenprod2='.$this->model->id.'&rand='.rand().'" href="javascript:;">
      <button type="button" class="btn btn-secondary btn-sm"><i class="fa fa-pen"></i> Editar orden</button>
    </a>';
    if($this->model->estatus == "N"){
      $botones .= '<a href="?modulo=ordenprod2&accion=activarop&id='.$this->model->id.'">
        <button type="button" class="btn btn-success btn-sm"><i class="fas fa-play-circle"></i> Activar</button>
      </a>';
    } else {
      $botones .= '
        <button type="button" class="btn btn-info btn-sm"><i class="fas fa-leaf"></i> Hoja viajera</button>
      ';
    }
    $atras = '<a href="?modulo=ordenprod2&accion=index">
      <button type="button" class="btn btn-warning btn-sm"><i class="fa fa-arrow-left"></i> Atrás</button>
    </a>';

    $artname = busca($this->model->articulo, "articulos", "a_id", "a_nmb");
    $var = busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "'.$this->model->modelo.'" AND av_articulo', 'COUNT(*)');
    if($var > 0) {
      $artname .= " ".busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "'.$this->model->modelo.'" AND av_articulo', 'av_nmb');
    }

    if($this->model->tipo == "L"){
      $artipo = "LISO";
    } else if ($this->model->tipo == "I"){
      $artipo = "IMPRESO";
    } else{
      $artipo = $this->model->obstipo;
    }

    $rolpe = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_rol');
    $rol = busca($rolpe, 'rolesprod', 'rp_id', 'rp_nmb');
    $encargado = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_nmb')." - ".$rol;

    $cortador = busca($this->model->cortador, 'pr_empleados', 'pe_id', 'pe_nmb');

    if($this->model->estatus == "N"){
      $estatus = "Nuevo";
    } else if($this->model->estatus == "P"){
      $estatus = "En proceso";
    } else{
      $estatus = "Finalizado";
    }
    
    toolbar('ORDEN DE PRODUCCIÓN ',$atras,'',$botones);
    echo '
    <style>
    /* Agrega estilos personalizados aquí si es necesario */
    #miDiv {
    display: none;
    }

    /* ESTILOS DEL LISTADO DE PROCESOS DE LA FASE */
    .c-timeline__item {
    position: relative;
    display: flex;
    gap: 1.5rem;
    //outline: solid 1px;

    &:last-child {
      .c-timeline__content {
      &:before {
          display: none;
      }
      }
    }
    }

    ol, ul {
      padding-left: unset !important;
    }
    .c-timeline__content {
    flex: 1;
    position: relative;
    order: 1;
    padding-left: 1.5rem;
    padding-bottom: 3rem;

    &:before {
      content: "";
      position: absolute;
      right: 100%;
      top: 0;
      height: 100%;
      width: 2px;
      background-color: lightgrey;
    }

    &:after {
      content: "";
      position: absolute;
      left: calc(0px - 11px);
      top: 0;
      width: 20px;
      height: 20px;
      background-color: #fff;
      z-index: 1;
      border: 2px solid lightgrey;
      border-radius: 50%;
    }
    }

    .c-timeline__title {
    font-weight: bold;
    margin-bottom: 0.5rem;
    }

    .c-timeline__desc {
    color: grey;
    }

    time {
    text-align: end;
    flex: 0 0 100px;
    min-width: 0;
    overflow-wrap: break-word;
    padding-bottom: 1rem;
    }

    /*** Non-demo CSS ***/

    *,
    *:before,
    *:after {
    box-sizing: border-box;
    }

    table, th, td {
      border: 1px solid #ddd !important; /* Borde gris tenue */
    }
    /* ESTILOS DEL LISTADO DE PROCESOS DE LA FASE */
    
    .c-timeline__content.custom-color-a::after {
      background-color: #2ccd2b; /* Color de fondo del pseudo-elemento */
    }
  
    .c-timeline__content.custom-color-b::before {
      background-color: #2ccd2b; /* Color de fondo del pseudo-elemento */
    }
    </style>';

    echo '<div class="row mt-8">    
    <div class="col-md-4 col-12">
    <table id="myTableG" class="table table-hover">
        <thead class="thead-active text-black">
            <tr>
                <th colspan="2" class="thead-active bg-primary text-white">
                    <center>Detalles del proceso</center>
                </th>
            </tr>
        </thead>
        <tbody>
            <!-- <tr class="thead-active bg-primary text-white">
                <td>Concepto</td>
                <td>Descripción</td>
            </tr> -->

            <tr>
                <td>Orden de producción:</td>
                <td>'.$this->model->folio.'</td>
            </tr>
            <tr>
                <td>Generó:</td>
                <td>'.$this->model->ugen.'</td>
            </tr>
            <tr>
                <td>Encargado:</td>
                <td>'.$encargado.'</td>
            </tr>
            <tr>
                <td>Cortador:</td>
                <td>'.$cortador.'</td>
            </tr>
            <tr>
                <td>Artículo:</td>
                <td>'.$artname.'</td>
            </tr>
            <tr>
                <td>Tipo de artículo:</td>
                <td>'.$artipo.'</td>
            </tr>
            <tr>
                <td>Cantidad:</td>
                <td>'.$this->model->cantidad.'</td>
            </tr>
            <tr>
                <td>Estatus:</td>
                <td>'.$estatus.'</td>
            </tr>
            <tr>
                <td>Fecha del la orden:</td>
                <td>'.fecha_formato($this->model->fgen, true, false).'</td>
            </tr>
            <tr>
                <td>Fecha estimada de entrega:</td>
                <td>'.fecha_formato($this->model->fentrega, false, false).'</td>
            </tr>
            <tr>
                <td>Retroalimentación:</td>
                <td>'.$this->model->notasalida.'</td>
            </tr>

        </tbody>
    </table>
    </div>

    <div class="col-md-8 col-12">';

    // INICIA EL CICLO DE FASES QUE CONTIENE LA ORDEN DE PRODUCCIÓN
    $pactivo = '';
    $j = 5;
    //for($i = 1; $i <= 10; $i++){
    
      if($i == $j){
        $pactivo = $j;
        $disnone = 'block';
      } else{
        $disnone = 'none';
      }

      $back = 'style="background: darkgray;"';
      $back = '';
    echo '
    <div style="padding-bottom: 1px;">
      <button id="miBoton'.$i.'" onclick="selectfase('.$i.')" '.$back.' class="btn btn-primary btn-block col-12 col-md-12 "><h3 class="text-white">Fase '.$i.': Corte</h3></button>
    </div>
    <div id="miDiv'.$i.'" style="display: '.$disnone.';">
      <div class="mt-6">
      <ol class="c-timeline">
          <li class="c-timeline__item">
          <div class="c-timeline__content custom-color-a custom-color-b">
              <h3 class="c-timeline__title">Trazado</h3>
              <p class="c-timeline__desc">Finalizado</p>
          </div>
          <time>10:03</time>
          </li>
          <li class="c-timeline__item">
          <div class="c-timeline__content custom-color-a">
              <h3 class="c-timeline__title">Corte</h3>
              <p class="c-timeline__desc">En proceso</p>
          </div>
          <time></time>
          </li>
          <li class="c-timeline__item">
          <div class="c-timeline__content">
              <h3 class="c-timeline__title">Resbaladilla</h3>
              <p class="c-timeline__desc">Pendiente</p>
          </div>
          <time></time>
          </li>
      </ol>
      </div>
    </div>';
  //}
    // FINALIZA EL CICLO DE FASES QUE CONTIENE LA ORDEN DE PRODUCCIÓN

    echo '</div>
    </div>';
  

    echo '
    <script>
    $(document).ready(function() {
    $("#miBoton").click(function() {
    $("#miDiv"+"'.$pactivo.'").slideToggle(); //Desplegaremos la fase del proceso que se encuentra activo
    });
    });

    function selectfase(k){
      var miDiv = document.getElementById("miDiv"+k);
      //var miBoton = document.getElementById("miBoton"+k);
      
      $("#miDiv"+k).slideToggle();

    }
    </script>
    ';
    }

    function articulosop(){
      $nregistros = $this->model->result->num_rows;
      $artname = busca($this->model->articulo, "articulos", "a_id", "a_nmb");
      $var = busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "'.$this->model->modelo.'" AND av_articulo', 'COUNT(*)');
      if($var > 0) {
        $artname .= " ".busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "'.$this->model->modelo.'" AND av_articulo', 'av_nmb');
      }
  
      if($this->model->tipo == "L"){
        $artipo = "LISO";
      } else if ($this->model->tipo == "I"){
        $artipo = "IMPRESO";
      } else{
        $artipo = $this->model->obstipo;
      }
  
      $rolpe = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_rol');
      $rol = busca($rolpe, 'rolesprod', 'rp_id', 'rp_nmb');
      $encargado = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_nmb')." - ".$rol;
  
      $cortador = busca($this->model->cortador, 'pr_empleados', 'pe_id', 'pe_nmb');
  
      if($this->model->estatus == "N"){
        $estatus = "Nuevo";
      } else if($this->model->estatus == "P"){
        $estatus = "En proceso";
      } else{
        $estatus = "Finalizado";
      }

      echo '
      <style>
      table, th, td {
        border: 1px solid #ddd; /* Borde gris tenue */
      }

      .text-end{
        text-align:right!important
      }

      .text-bold-400 {
        font-weight : 400;
      }

      .align-self-center{
        -ms-flex-item-align:center!important;
        align-self:center!important
      }

      .justify-content-between{
        -webkit-box-pack:justify!important;
        -ms-flex-pack:justify!important;
        justify-content:space-between!important
      }

      .px-md-1{
        padding-right:.25rem!important;
        padding-left:.25rem!important
      }

      .castle-success{
        color: #50cd89;
      }

      .castle-danger{
        color: #f1416c;
      }

      .castle-warning{
        color: #ffc700;
      }

      .castle-primary{
        color: #41b1f1;
      }
      
      </style>
      ';

      echo '
      <div class="row mt-8">
        <div class="col-12 col-md-2">
        <span style="font-weight: bold;">Seleccionar todos: <input class="form-check-input" type="checkbox" onchange="selectAll(1);" id="checkAll" name="selall"></span>
        </div>
        <!-- 
        <div class="col-12 col-md-2">
        <span style="font-weight: bold;">Deseleccionar todos: <input class="form-check-input" type="checkbox" onchange="selectAll(0);" id="descheckAll" name="selall"></span>
        </div>
        -->
      </div>
      <div class="row">
      <div class="col-12 col-md-4">
        <span style="font-style: italic; font-size: 11px;">Número de artículos: '.$nregistros.'</span>
      </div>
      <div class="col-12 col-md-8" style="text-align: right;">
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Sin iniciar: <input class="form-check-input" type="checkbox" style="border: none; background: #888;" checked disabled></span>
        &nbsp;&nbsp;
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Iniciado: <input class="form-check-input" type="checkbox" style="border: none; background: #ffc700;" checked disabled></span>
        &nbsp;&nbsp;
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Entre fases (Finalizado): <input class="form-check-input" type="checkbox" style="border: none; background: #f1416c;" checked disabled></span>
        &nbsp;&nbsp;
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Finalizado: <input class="form-check-input" type="checkbox" style="border: none; background: #50cd89;" checked disabled></span>
        &nbsp;&nbsp;
        &nbsp;&nbsp;
      </div>
      ';

      //INICIA TABLA DE TODOS LOS ARTÍCULOS QUE HAY DE LA ORDEN DE PRODUCCIÓN
      $anchocol = 10;
      echo'
      <div class="row mt-2">
        <div class="col-12 col-md-12">
          <center>
          <table class="col-12 col-md-12 table-responsive" style="background: white;">
            <thead>
              <th class="thead-active bg-primary text-white" colspan="'.$anchocol.'"><center>Listado de artículos</center></th>
            </thead>
            <tbody>
            
            <tr>';

            
            if($nregistros == 0){
              echo '
              <td colspan="'.$anchocol.'"><center>No hay artículos para mostrar</center></td>
              ';
            } else{
              $i = 0;
              while($row = $this->model->result->fetch_array()){
                if($i%$anchocol == 0){
                  echo '
                  </tr>
                  <tr>';
                }
                /* echo '
              <td><center><button class="btn btn-sm btn-secondary col-12 col-md-12">RED DE PROTECCIÓN TRAMPOLIN NARANJA '.($i +1).'</button></center></td>
              '; */
              if(!empty($row['pra_estpa']) && !empty($row['pra_estsp'])){
                if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "N"){
                  $clase = 'castle-danger'; //Artículo entre fases
                  $txticono = 'Artículo entre fases';
                } else if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "F"){
                  $clase = 'castle-success'; //Artículo finalizado
                  $txticono = 'Artículo finalizado';
                } else if($row['pra_estpa'] == "A"){
                  $clase = 'castle-warning'; //En ejecución
                  $txticono = 'Artículo en ejecución';
                } else if($row['pra_estpa'] == "N"){
                  $clase = 'castle-origin';
                  $txticono = 'Artículo listo para iniciar el primer proceso';
                }
              } else{
                $clase = 'castle-origin';
                $txticono = 'Artículo listo para iniciar el primer proceso';
              }

              if(isset($_GET['tipo']) && $_GET['tipo'] == "I" && ($row['pra_estpa'] == "A" || $row['pra_estpa'] == "F")){
                $disa = "disabled checked";
              } else{
                $disa = "";
              }

              if(isset($_GET['tipo']) && $_GET['tipo'] == "F" && $row['pra_estpa'] == "F"){
                $disa = "disabled checked";
              }

              echo '
              <td>
              <center>
              <div>
                <i class="fab fa-fort-awesome fs-1 fa-2x '.$clase.'"" onclick="pressIcono('.$row['pra_id'].');" id="icono'.$row['pra_id'].'"></i>
                <input hidden class="form-check-input check-all" type="checkbox" id="check'.$row['pra_id'].'" name="boletos'.$row['pra_id'].'" value="'.$row['pra_id'].'" '.$disa.'>
                <input type="hidden" id="class'.$row['pra_id'].'" value="'.$clase.'">
              </div>
              <div>
                <span style="font-style: italic; font-size: 11px; font-weight: bold;">'.$row['pra_cb'].'<span>
              </div>
              </center>
              </td>';
              $i++;
              }
            }
            echo '
            </tr>
            </tbody>
          </table>
    </center>
        <div>
      </div>';
      //FINALIZA TABLA DE TODOS LOS ARTÍCULOS QUE HAY DE LA ORDEN DE PRODUCCIÓN

      echo '
      <script>

      function selectAll(k){
        var response = false;
        var cb1 = document.getElementById("checkAll");
        //var cb2 = document.getElementById("descheckAll");
        var icono = "";
        var classorigen = "";
        
        if(k == 1){
          if(!cb1.checked){
            response = false;
          } else{
            response = true;
          }
          //cb2.checked = false;
        } else{
          cb1.checked = false;
        }
        var elementosCheckAll = document.querySelectorAll(".check-all");

        // Recorre los elementos y marca cada uno como "checked"
        elementosCheckAll.forEach(function(elemento) {
          if(!elemento.hasAttribute("disabled")){
            elemento.checked = response;
            icono = document.getElementById("icono"+elemento.value);
            classorigen = document.getElementById("class"+elemento.value).value;
            console.log("Elemto origen: "+elemento.value);
            if(elemento.checked){
              icono.classList.remove("castle-origin");
              icono.classList.remove("castle-danger");
              icono.classList.remove("castle-warning");
              icono.classList.remove("castle-success");
              icono.classList.add("castle-primary");
            }else{
              icono.classList.remove("castle-primary");
              icono.classList.add(classorigen); 
            }
          }
        });
      }

        function pressIcono(k){
          // Obtén el icono y el checkbox por su ID
          var checkbox = document.getElementById("check"+k);
          var icono = document.getElementById("icono"+k);
          var classorigen = document.getElementById("class"+k).value;

            // Cambia el estado "checked" del checkbox
            if(!checkbox.hasAttribute("disabled")){
              checkbox.checked = !checkbox.checked;
              if(checkbox.checked){
                console.log("Chequeado");
                icono.classList.remove("castle-origin");
                icono.classList.remove("castle-danger");
                icono.classList.remove("castle-warning");
                icono.classList.remove("castle-success");
                icono.classList.add("castle-primary");
              }else{
                console.log("NO chequeado");
                icono.classList.remove("castle-primary");
                icono.classList.add(classorigen); 
              }
            }
        }
      </script>
      ';
      
    }

}
?>