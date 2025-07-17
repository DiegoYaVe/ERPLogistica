<?php
class autorizar{

function __construct(){
    $this->model = new modelautorizar($obj);
}
function index(){
    
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "N";
    else if($_REQUEST['estatus'] == "T") $_REQUEST['estatus']  = NULL;

    if(!isset($_REQUEST['folio']) || trim($_REQUEST['folio']) == "") $_REQUEST['folio'] = NULL;

    $this->model->result($_REQUEST['estatus'], $_REQUEST['folio']);
    $this->view = new viewlautorizar($this->model);
    $this->view->browse($_REQUEST['estatus'], $_REQUEST['folio']);
}


function update(){
  $this->model->update($_GET['id']);
  $sql = 'UPDATE remisiones SET r_estatus = "N" WHERE r_id = "'.$_GET['id'].'"';
  setq($sql);

  redirect('?modulo=autorizar&accion=index');
}

}

class modelautorizar{
  function result($estatus, $folio){ //filtro
    $sql = 'SELECT * FROM remision_faltantes INNER JOIN remisiones ON r_id = rf_remision WHERE r_estatus NOT IN ("F", "C", "FE", "A")';
    if($estatus != NULL) $sql .= ' AND rf_estatus ="'.$estatus.'"';
    if($folio != NULL || $folio != "") $sql .= ' AND r_folio LIKE  "%'.$folio.'%"';
    $sql .= ' GROUP BY rf_remision';
    $this->result = setq($sql);
    $this->resultt = setq($sql);
  }



  function update($id){
      $sql = 'UPDATE remision_faltantes SET
              rf_estatus = "F",
              rf_uconfirma = "'.$_SESSION['uid'].'",
              rf_fconfirma = "'.date('Y-m-d H:i:s').'"
              WHERE rf_remision = "'.$id.'"';
      setq($sql);
  }
}

class viewlautorizar {
    var $model;

function __construct($model) {

    $this->model = $model;
    $this->model->aest = array("N" => "Por aprobar","F" => "Aprobado");
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
function browse($estatus, $folio) {
   //Sección: E1 Encabezado - Botones de acción
    
    $estp = "";
    $esta = "";
    $estt = ""; 
    if(!isset($estatus) || $estatus == "T") $estt = "selected";
    elseif($estatus == "F") $esta = "selected";
    else $estp = "selected";

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
    
    $filtrar = '
              <form class="form-inline" role="form" method="post" action="?modulo=autorizar&accion=index" id="filtro">
                <div class="mb-5">
                  <label for="">Filtrar por remision:</label>
                  <input onkeyup="ceropage();" type="text" class="form-control" id="pref-search" name="folio" value="'.$folio.'" placeholder="Buscar por folio de remision">
                  
                </div>
                <div class="mb-5" style="max-width: 250px">
                  <label for="">Buscar por estatus:</label>
                  <select id="estatusfiltro" class="form-control" name="estatus">
                    <option value="N" '.$estp.'>Por aprobar</option>
                    <option value="F" '.$esta.'>Aprobados</option>
                    <option value="T" '.$estt.'>Todos</option>
                  </select>
                </div>

                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                <button type="submit" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=autorizar&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';

        toolbar("Aprobar envío","", $filtrar);

    echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th width="20%">Remisión</th>
                    <th width="20%">Cantidad de articulos</th>
                    <th width="20%">Usuario</th>
                    <th width="20%">Fecha de confirmación</th>
                    <th width="20%">Estatus</th>
                    <th>Acción </th>
                  </tr>
                </thead>'; 
    while ($row = $this->model->result->fetch_array()) {
        $sum = busca($row['r_id'], 'remision_faltantes', 'rf_estatus != "F" AND rf_remision', 'SUM(rf_cantidad)');
        if($row['rf_estatus'] == "N") {
          $colorf = 'class="alert no-border" style="background: #F4D03F"';
          $estatus = "Por aprobar";
        }else {
          $estatus = "Aprobado";
          $colorf = 'class="alert no-border text-white" style="background: #48C9B0"';
        }
        if($row['rf_uconfirma'] == "") {
          $usuario = 'Vendedor: '.busca($row['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ",u_apellidos)');
          $fecha = "Por confirmar";
        } else {
          $usuario = 'Confirmó: '.busca($row['rf_uconfirma'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ",u_apellidos)');
          $fecha = $row['rf_fconfirma'];
        }
        echo '<tr>
        <td>'.$row['r_folio'].'</td>
        <td>'.$sum.'</td>
        <td>'.$usuario.'</td>
        <td>'.$fecha.'</td>
        <td '.$colorf.' role="alert">'.$estatus.'</td>
        <td>';
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" data-fancybox data-type="ajax" data-src="popup/autorizararticulo.php?id='.$row['r_id'].'&rand='.rand().'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
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
            pageLength: "50",
        });
      });
      </script>';
}
}
?>