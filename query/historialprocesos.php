<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');

//BUSCAMOS TODAS LAS ORDENES DE PRODUCCIÓN DONDE HAYA INTERACTUADO NUESTRO OPERADOR
if(isset($_SESSION['uid'])){
    $tuser = 'U';
    $operador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else{
    $tuser = 'O';
    $operador = $_SESSION['eid'];
}
$sql = 'SELECT * FROM pr_procesoexe WHERE ppx_operador = "'.$operador.'" AND ppx_tuser = "'.$tuser.'" GROUP BY ppx_ordenp ORDER BY ppx_fecha DESC';
die($sql);
/* echo '<div class="container">
        <div class="row">        
            <div class="table table-responsive col-12 col-md-12 p-2">
                <table width="100%" class="table table-stripped table-bordered table-hover table-bordered border-primary">
                  <thead class="bg-primary text-white">
                    <tr>
                      <td width="40%">Descripción</td>
                      <td width="30%">Fecha</td>
                      <td width="30%">Usuario que generó el movimiento</td>
                    </tr>
                  </thead>
                  <tbody>';
                  */
                    /* $sql = 'SELECT * FROM crm_cotizaciones_historial WHERE cch_cotizacion = "'.$this->model->id.'"';
                    $result = setq($sql);
                    while($rowc = $result->fetch_array()){
                    echo '<tr>
                      <td width="40%">'.$rowc['cch_descripcion'].'</td>
                      <td width="30%" >'.fecha_formato($rowc['cch_fecha'], true, false).'</td>
                      <td width="25%" >'.$rowc['cch_user'].'</td>
                    </tr>';
                    } */
                  /* echo '</tbody>
                </table>
              </div>
              <center>
                <div class="col-12">
                  <button class="btn btn-danger" onclick="cerrarModal();"><i class="fas fa-times-circle"> </i> Cerrar </button>
                </div>
              </center>
            </div>
            </div>
            </div>
            '; */
/* 
    echo '
    <div class="card mt-3">
        <div class="card-body">
    <div class=" col-12 table-responsive">
        <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
        <thead class="bg-light-blue bg-darken-2 ">
        <tr>
            <th><b>Orden de producción</b></th>
            <th><b>Nombre</b></th>
            <th><b>Encargado</b></th>
            <th><b>Almacen</b></th>
            <th><b>Cliente</b></th>
            <th><b>Estatus</b></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
        </div>
    </div>'; */

/*     echo '
    <script>
    function mandar(){
      //var table = $("#myTable").DataTable();
      $("#myTable").DataTable().clear().draw();
      $("#myTable").DataTable().destroy();

      $("#myTable").DataTable( {
        paging: true,
        scrollY: 400,
        processing: true,
        serverside: true,
        ordering: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        pageLength: "50",
        responsivePriority: 1,
      });
    }
  </script>
  '; */
?>