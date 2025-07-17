<?php
//ini_set('display_errors', 1);
class saldos{

function __construct(){
    $this->model = new modelsaldos($obj);
}
function index(){
    if(!isset($_GET['remision'])) $_GET['remision'] = NULL;
    if(!isset($_REQUEST['tablero'])) $_REQUEST['tablero'] = NULL;
    if(!isset($_REQUEST['cliente'])) $_REQUEST['cliente'] = NULL;
    if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d', strtotime('-30 days'));
    if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d', strtotime('last day of this month'));
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
    $this->model->result($_REQUEST['remision'],$_REQUEST['tablero'],$_REQUEST['cliente'],$_REQUEST['fini'],$_REQUEST['ffin'], $_REQUEST['estatus']);
    $this->view = new viewlsaldos($this->model);
    $this->view->browse($_REQUEST['remision'],$_REQUEST['tablero'],$_REQUEST['cliente'],$_REQUEST['fini'],$_REQUEST['ffin'], $_REQUEST['estatus']);
}
function setsaldo(){
  //foreachdie();
  $saldo = $_GET['id'];
  $fecha = $_POST['vencimiento'];
  $monto = $_POST['monto'];
  $this->model->select($saldo);
  $saldoempresa = $_POST['saldoempresa'];
  $nmb = busca($this->model->cliente, 'crm_clientes', 'c_id', 'c_nmb');
  $ape = busca($this->model->cliente, 'crm_clientes', 'c_id', 'c_apellidos');
  $nmbcliente = $nmb. ' '.$ape;
  include_once('cxcobrar.php');
  $cxc = new modelcxcobrar();
  $cxc -> insertcxcobrar($this->model->cliente, $this->model->remision, "S", $monto, date('Y-m-d'), "Saldo del cliente ".$nmbcliente);
  $sqlupd = 'UPDATE penalizaciones SET p_estatus = "P", p_saldoempresa = "'.$saldoempresa.'", p_fcaduca = "'.$fecha.'", p_monto = "'.$monto.'" WHERE p_id = "'.$saldo.'"';
  setq($sqlupd);
  redirect('?modulo=saldos&accion=index');
}

}

class modelsaldos{
  function result($remision,$tablero,$cliente,$fini, $ffin, $estatus){ //filtro
    $sql = 'SELECT * FROM penalizaciones INNER JOIN remisiones ON r_id = p_remision INNER JOIN crm_tableros ON ct_id = p_tablero INNER JOIN crm_clientes ON c_id = p_cliente WHERE DATE(p_fgen) >= "'.$fini.'" AND DATE(p_fgen) <= "'.$ffin.'"';
    if($remision) $sql.= ' AND r_folio LIKE "%'.trim($remision).'%"';
    if($cliente) $sql.= ' AND (c_nmb LIKE "%'.trim($cliente).'%" OR c_apellidos LIKE "%'.trim($cliente).'%")';
    if($tablero) $sql.= ' AND ct_nmb LIKE "%'.trim($tablero).'%"';
    if($estatus) $sql.= ' AND p_estatus ="'.$estatus.'"';
    $sql.=' ORDER BY p_fcaduca DESC';
    $this->result = setq($sql);
    $this->resultt = setq($sql);
  }
  function select($id){
      $sql = 'SELECT * FROM penalizaciones WHERE p_id="'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['p_id'];
      $this->tablero = $row['p_tablero'];
      $this->cliente = $row['p_cliente'];
      $this->remision = $row['p_remision'];
      $this->monto = $row['p_monto'];
      $this->fgen = $row['p_fgen'];
      $this->ugen = $row['p_ugen'];
      $this->fcaduca = $row['p_fcaduca'];
      $this->estatus = $row['p_estatus'];
  }
}

class viewlsaldos {
    var $model;

function __construct($model) {
    $this->model = $model;
    $this->model->aest = array("A" => "Activo","I" => "Inactivo");
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
function browse($remision, $tablero, $cliente, $fini, $ffin, $estatus) {
   //Sección: E1 Encabezado - Botones de acción
    $esta = "";
    $esti = "";
    $estt = "";
    if(!isset($estatus) || $estatus == "T") $estt = "selected";
    elseif($estatus == "N") $estn = "selected";
    elseif($estatus == "P") $estp = "selected";
    elseif($estatus == "A") $esta = "selected";
    elseif($estatus == "C") $estc = "selected";

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
      /* $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=1&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>'; */
      
      
    $filtrar = '
      <form class="form-inline" role="form" method="post" action="?modulo=saldos&accion=index" id="filtro"> 
        <div class="mb-5">
          <label for="">Filtrar por remision:</label>
          <input type="text" class="form-control" id="remision" name="remision" value="'.$remision.'" placeholder="Buscar por folio de remisión">
        </div>
        <div class="mb-5">
          <label for="">Filtrar por tablero:</label>
          <input type="text" class="form-control" id="tablero" name="tablero" value="'.$tablero.'" placeholder="Buscar por nombre del tablero">
        </div>
        <div class="mb-5">
          <label for="">Filtrar por cliente:</label>
          <input type="text" class="form-control" id="cliente" name="cliente" value="'.$cliente.'" placeholder="Buscar por nombre del cliente">
        </div>
        <div class="mb-5">
          <label for="tipom">Desde</label>
          <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
        </div>
        <div class="mb-5">
          <label for="tipom">Hasta</label>
          <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
        </div>
        <div class="mb-5">
          <label for="tipom">Estatus</label>
          <select type="date" name="estatus" id="estatus" class="form-control">
            <option value="T" '.$estt.'>TODOS </option>
            <option value="N" '.$estn.'>POR DEFINIR</option>
            <option value="P" '.$estp.'>POR USARSE</option>
            <option value="A" '.$esta.'>APLICADO</option>
            <option value="C" '.$estc.'>VENCIDO</option>
          </select>
        </div>
        
        <div class="mb-5">
          <label for="">Acciones:</label><br>
        <button type="submit" class="btn btn-info">
          <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
        </button>
        <a href="?modulo=categorias&accion=index"><button type="button" class="btn btn-warning">
          <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
        </button></a>
        </div>
      </form>';

        toolbar("SALDOS","", $filtrar);

  echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table" id="myTable">
                <thead class="bg-light-blue bg-darken-2">
                  <tr>
                    <th width="12%">Remisión</th>
                    <th width="13%">Tablero</th>
                    <th width="13%">Cliente</th>
                    <th width="12%">Monto</th>
                    <th width="12%">Usuario</th>
                    <th width="12%">Fecha que caduca</th>
                    <th width="13%">Estatus</th>
                    <th width="13%"></th>
                  </tr>
                </thead>'; 
    while ($row = $this->model->result->fetch_array()) {
        if(!$row['r_fcaduca']) $fecha = 'Por definir';
        else $fecha = fecha_formato($row['r_fcaduca'], true, true);
        if($row['p_estatus'] == "N"){
          $nmb = 'Por definir';
          $bg = 'class="alert bg-warning"';
        } else if($row['p_estatus'] == "P"){
          $nmb = 'Por usarse';
          $bg = 'class="alert bg-primary"';
        } else if($row['p_estatus'] == "F"){
          $nmb = 'Aplicado';
          $bg = 'class="alert bg-success"';
        } else {
          $nmb = 'Vencido';
          $bg = 'class="alert bg-danger"';
        }
        echo '<tr>
          <td>'.$row['r_folio'].'</td>
          <td>'.$row['ct_nmb'].'</td>
          <td>'.$row['c_nmb'].' '.$row['c_apellidos'].'</td>
          <td>'.$row['p_monto'].'</td>
          <td>'.busca($row['p_ugen'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>
          <td>'.$fecha.'</td>
          <td '.$bg.'>'.$nmb.'</td>';
        
          echo '<td><a data-toggle="tooltip" data-placement="top" title data-original-title="Aplicar saldo" data-fancybox data-type="ajax" data-src="popup/aplicarsaldo.php?id='.$row['p_id'].'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-eye" style="color: #ffffff;"></i>
            </button>
          </a></td>';
        echo '</tr>
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
}
?>