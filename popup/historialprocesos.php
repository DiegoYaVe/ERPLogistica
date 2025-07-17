<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
ini_set('display_errors',0);
include_once('../funciones.php');
?>
<link rel="canonical" href="https://preview.keenthemes.com/metronic8" />
<link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />

<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="//code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
.menu-sub-1{
    display:none;
    padding:0;
    margin:0;
    list-style:none;
    flex-direction:column;
    position: fixed;
}

.menu-sub-dropdown-1{
    display:none;
    border-radius:.475rem;
    background-color:#fff;
    box-shadow:0 0 50px 0 rgba(82,63,105,.1);
    z-index:105;
}

.menu-sub-2{
    display:block;
    padding:0;
    margin:0;
    list-style:none;
    flex-direction:column;
    position: fixed;
}

.menu-sub-dropdown-2{
    display:block;
    border-radius:.475rem;
    background-color:#fff;
    box-shadow:0 0 50px 0 rgba(82,63,105,.1);
    z-index:105;
}
</style>
<?php

//BUSCAMOS TODAS LAS ORDENES DE PRODUCCIÓN DONDE HAYA INTERACTUADO NUESTRO OPERADOR
if(isset($_SESSION['uid'])){
    $tuser = 'U';
    $operador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else{
    $tuser = 'O';
    $operador = $_SESSION['eid'];
}

$sql = 'SELECT ppx_ordenp FROM pr_procesoexe WHERE ppx_operador = "'.$operador.'" AND ppx_tuser = "'.$tuser.'" GROUP BY ppx_ordenp ORDER BY ppx_fecha DESC';
$result = setq($sql);

$fechahoy = date("Y-m-d");
$fecha = date("Y-m-d", strtotime($fechahoy . " -20 days"));


$filtro = '
    <form class="form-inline" role="form" method="post" action="?modulo=almacenes&accion=index" id="filtro">
    <div class="mb-5">
      <label for="">Fecha inicial:</label>
      <input type="date" class="form-control" id="fini" name="fini" value="'.$fecha.'" placeholder="Fecha inicial">
      <input hidden type="date" class="form-control" id="fini2" name="fini2" value="'.$fecha.'" placeholder="Fecha inicial">
    </div>
    <div class="mb-5">
      <label for="">Fecha final:</label>
      <input type="date" class="form-control" id="ffin" name="ffin" value="'.$fechahoy.'" placeholder="Fecha final">
      <input hidden type="date" class="form-control" id="ffin2" name="ffin2" value="'.$fechahoy.'" placeholder="Fecha final"> 
    </div>

    <div class="mb-5">
      <label for="">Acciones:</label><br>
      <button type="button" onclick="mandar(0)" class="btn btn-info">
      <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
    </button>
    <!-- <a href="?modulo=almacenes&accion=index"><button type="button" class="btn btn-sm btn-warning"> -->
    <button type="button" id="btnClean" onclick="mandar(1)" class="btn btn-warning">
      <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
    </button><!-- </a> -->
    </div>
    </form>';

    $botonfiltro = '
    <button type="button" id="btnFiltro" onclick="desplegar();" class="btn btn-flex btn-info btn-active-primary fw-bolder" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
    <span class="svg-icon svg-icon-5 svg-icon-gray-500 me-1">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M19.0759 3H4.72777C3.95892 3 3.47768 3.83148 3.86067 4.49814L8.56967 12.6949C9.17923 13.7559 9.5 14.9582 9.5 16.1819V19.5072C9.5 20.2189 10.2223 20.7028 10.8805 20.432L13.8805 19.1977C14.2553 19.0435 14.5 18.6783 14.5 18.273V13.8372C14.5 12.8089 14.8171 11.8056 15.408 10.964L19.8943 4.57465C20.3596 3.912 19.8856 3 19.0759 3Z" fill="black" />
      </svg>
    </span> Filtrar
    </button>
    <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_61484bf44d957">
      <div class="px-7 py-5">
        <div class="fs-5 text-dark fw-bolder">Filtrar Por</div>
      </div>
      <div class="separator border-gray-200"></div>
      <div class="px-7 py-5">
        ' . $filtro . '
      </div>
      </div>';

    echo '
    <div class="container">
        <div class="card-body">
        <div class="col-12 col-md-12" id="divfiltro">'
        .$botonfiltro.'
        </div>';

        echo '
        <input type="hidden" id="contenedor" value="0">
        <div class="col-12 col-md-12 table-responsive" id="detalleop">
        <center>
        <table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
        <thead class="bg-light-blue bg-darken-2 ">
        <tr>
            <th><b>Orden de producción</b></th>
            <th><b>Artículo</b></th>
            <th><b>Fecha creación</b></th>
            <th><b>Fecha final</b></th>
            <th><b>Estatus</b></th>
            <th></th>
        </tr>
        </thead>
        <tbody>';

        echo '
        </tbody>
        </table>
        </div>
        <div class="col-12 col-md-12" id="detallehp">
        </div>
        </div>
    </div>';
?>
<script>

var detallop = document.getElementById("detalleop");
var detallehp = document.getElementById("detallehp");
var btnFiltro = document.getElementById("btnFiltro");

var dataTableInstance; // Variable para almacenar la instancia DataTable

function mandar1(id) {      
  var fini2 = document.getElementById("fini2");
  var ffin2 = document.getElementById("ffin2");
  var fini = document.getElementById("fini");
  var ffin = document.getElementById("ffin");

  if (id == 1) {
    fini.value = fini2.value;
    ffin.value = ffin2.value;
  }

  // Destruir la instancia DataTable si ya existe
  if (dataTableInstance) {
    dataTableInstance.destroy();
  }

  // Crear una nueva instancia de DataTable y almacenar la referencia
  dataTableInstance = $("#myTable").DataTable({
    paging: true,
    scrollY: 400,
    processing: true,
    serverSide: true,
    ordering: false,
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
    },
    ajax: {
      url: "../query/datatablehistorialprocesos.php",
      type: "POST",
      datatype: "json",
      data:{ 
        "fini" : fini.value,
        "ffin" : ffin.value
      }
    },
    pageLength: 50, // No necesitas usar una cadena para el número
    responsivePriority: 1,
  });
  desplegar();
}

function mandar1(id) {      
  var fini2 = document.getElementById("fini2");
  var ffin2 = document.getElementById("ffin2");
  var fini = document.getElementById("fini");
  var ffin = document.getElementById("ffin");

  if (id == 1) {
    fini.value = fini2.value;
    ffin.value = ffin2.value;
  }

  // Destruir la instancia DataTable si ya existe
  if (dataTableInstance) {
    dataTableInstance.clear().draw();
    dataTableInstance.destroy();
  }

  // Crear una nueva instancia de DataTable y almacenar la referencia
  dataTableInstance = $("#myTable").DataTable({
    paging: true,
    scrollY: 400,
    processing: true,
    /* serverSide: true, */
    ordering: false,
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
    },
    ajax: {
      url: "../query/datatablehistorialprocesos.php",
      type: "POST",
      datatype: "json",
      data:{ 
        "fini" : fini.value,
        "ffin" : ffin.value
      }
    },
    pageLength: 50, // No necesitas usar una cadena para el número
    responsivePriority: 1,
  });
}

mandar1(1);



function verprocesos(k){
  var ordenp = k;

  $.ajax({
    url: '../query/detallehp.php',
    method: 'POST',
    data: {
            'ordenp': ordenp,
            'operador': "<?php echo $operador; ?>"
          },
  }).done(function(data){
    detallehp.innerHTML = data;
  });   

  detallop.setAttribute("hidden", true);
  detallehp.removeAttribute("hidden");
  btnFiltro.setAttribute("hidden", true);

}

function iratras(k){
  var ordenp = k;

  detallehp.setAttribute("hidden", true);
  detallop.removeAttribute("hidden");
  btnFiltro.removeAttribute("hidden");

}

function cerrarModal(){
  var closeButton = $('.fancybox-button');
  closeButton.click();
}

function desplegar(){
  var divid = document.getElementById("kt_menu_61484bf44d957");
  var contenedor = document.getElementById("contenedor");
  if(contenedor.value == 0){
    divid.classList.remove("menu-sub-1");
    divid.classList.remove("menu-sub-dropdown-1");
    divid.classList.add("menu-sub-2");
    divid.classList.add("menu-sub-dropdown-2");
    contenedor.value = 1;
  } else{
    divid.classList.add("menu-sub-1");
    divid.classList.add("menu-sub-dropdown-1");
    divid.classList.remove("menu-sub-2");
    divid.classList.remove("menu-sub-dropdown-2");
    contenedor.value = 0;
  }
}
  </script>