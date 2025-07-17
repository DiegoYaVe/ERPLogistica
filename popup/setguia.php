<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
?>
<script language="javascript">
function checksubmit(){
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
function cargar(){
  opener.location.reload();
  window.close();
}
function closewindow(){
   window.opener.location.reload();
   window.close();
}
</script>
<?php
/* ini_set('display_errors', 1); */
session_start();
include_once('../funciones.php');

$idremision = $_GET['id'];

/* die($idremision); */
$sqlcc = 'SELECT * FROM remisiones WHERE r_id = "'.$idremision.'"';
/* $sql = 'SELECT * FROM crm_cotizacionesd
              WHERE cdm_cotizacion = "'.$idremision.'" ORDER BY cdm_id ASC';*/
$resultcc = setq($sqlcc);
$rowcc = $resultcc->fetch_array();
echo '
<style>

.table {
  width: 100%;
  border-collapse: collapse;
}

.tamanio-letra {
  border: 1px solid black;
  padding: 8px;
  text-align: left;
  font-size: 11px !important;
}

/* Estilo para filas pares, excluyendo el encabezado y las filas con clase "sin-hover2" */
table#myTableA tr:nth-child(even):not(.sin-hover2) td,
table#miTableN tr:nth-child(even):not(.sin-hover2) td {
  background-color: #f2f2f2;
}

/* Estilo para filas impares, excluyendo el encabezado y las filas con clase "sin-hover2" */
table#myTableA tr:nth-child(odd):not(.sin-hover2) td,
table#miTableN tr:nth-child(odd):not(.sin-hover2) td {
  background-color: #ffffff;
}


.truncar-texto {
  border: 1px solid #ccc;
  padding: 10px;
  white-space: normal; /* Cambiamos nowrap a normal para permitir múltiples líneas */
  word-wrap: break-word; /* Rompemos las palabras largas para ajustarlas */
}

.section{
  //border: 2px solid #000; /* Contorno de 2 píxeles de ancho y color negro */
  //padding: 2px; /* Espacio interno alrededor del contenido */
  text-align: center; /* Alineación del contenido en el centro */
  display: flex; /* Utilizamos flexbox para disposición horizontal */
  justify-content: space-between; /* Espacio entre los paneles */
}

.section-dos{
  width: 10%; /* Ancho de cada panel (ajusta según tus necesidades) */
  display: flex;
  justify-content: center; /* Centrar horizontalmente */
  align-items: center; /* Centrar verticalmente */
  height: 100%; /* Asegurar que el div ocupe toda la altura disponible */

  //border: 1px solid #ccc; /* Contorno para cada panel (opcional) */
  padding: 10px;
}

.section-uno {
  width: 50%; /* Ancho de cada panel (ajusta según tus necesidades) */
  border: 1px solid #ccc; /* Contorno para cada panel (opcional) */
  padding: 10px;
}

.section-tres {
  width: 40%; /* Ancho de cada panel (ajusta según tus necesidades) */
  border: 1px solid #ccc; /* Contorno para cada panel (opcional) */
  padding: 10px;
}

.contenedor {
  display: flex;
  flex-direction: column;
  justify-content: center; /* Centra verticalmente */
  align-items: center; /* Centra horizontalmente */
  height: 100vh; /* Ajusta la altura según la ventana gráfica, si lo deseas */
}

button {
  margin-bottom: 10px;
}

button {
  display: block;
  margin-bottom: 5px; /* Agrega margen entre los botones si lo deseas */
}
</style>

';
echo'
<div class="container mt-1 ">
  <form action="?modulo=guias&accion=setenvios" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary"><h2><span style="color: #5490c6;">Detalles remisión</span></h2><br>';
      $fini = $rowcc['r_fliquidacion'];
      $almacen = busca($rowcc['r_almacen'], 'almacenes', 'a_id', 'a_nmb');
      $cliente = busca($rowcc['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');
      $apellidocl = busca($rowcc['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');

      $idcotiza = busca($idremision, 'crm_cotizaciones', 'cc_remision', 'cc_id');
      $foliocotiza = busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_folio');
      $direnvio = busca($idremision, 'crm_cotizaciones', 'cc_remision', 'cc_direnvio');
      $direccion = busca($direnvio, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
      $cp = busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_cp');
      $telefono = busca($idremision, 'crm_cotizaciones', 'cc_remision', 'cc_teldestino');
      $recibe = busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_recibe');
      $obs = busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_observaciones');

      $paq = busca($idremision, 'guias_articulos', 'ga_cotizacion', 'ga_paqueteria');
      $arttipoP = intval(busca($idremision, 'remisionesc', 'rc_tipoenvio = "P" AND rc_estatus = "N" AND rc_remision', 'COUNT(*)'));

      $encargado = busca($rowcc['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
      if(busca($idremision, 'remisiones', 'r_id', 'r_enviocliente') == "1") $envio = 'El <b> cliente </b> paga el envío';
      else $envio = 'La <b> empresa </b> paga el envío';
      echo '      
      <span>Folio remisión: '.$rowcc['r_folio'].'</span><br>
      <span>Folio cotización: '.$foliocotiza.'</span><br>
      <span>Asesor: '.$encargado.'</span><br>
      <span>Cliente: '.$cliente.' '.$apellidocl.'</span><br>';
      if(!empty($rowcc['r_observaciones'])){
        echo '<span>Descripción: '.$rowcc['r_observaciones'].'</span><br>';
      }

      echo'
      <span>Direccion: '.$direccion.'</span><br>';
      if($obs) echo '<span>Observaciones de la dirección: '.$obs.'</span><br>';
      if($recibe) echo '<span>Recibe la compra: '.$recibe.'</span><br>';
      echo '<span>Teléfono: '.$telefono.'</span><br>';
      if($arttipoP > 0){
        echo '<br><h5><span style="color: #5490c6;">ESTA REMISIÓN TIENE '.$arttipoP.' ARTICULOS POR DEFINIR ENVÍO</span></h5>';
      }
      echo '<!-- <h4><span style="color: #5490c6;">Importe total: $'.$rowcc['cc_importe'].'</span></h4><br> -->
      ';
      echo '<span>'.$envio.' </span><br>';
      echo '</div>';

    echo '
    <div class="col-12" style="padding-bottom: 10px;">
      <button type="button" onclick="mostrarGuia(0);" id="btnNuevo" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Asignar un conjunto de articulos a una guía"><i class="fa fa-plus"></i> Nuevo grupo</button>
      <button type="button" onclick="mostrarGuia(1);" id="btnNuevoSinGuia" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Asignar un conjunto de articulos a una guía" hidden><i class="fa fa-plus"></i> Nuevo grupo sin guía</button>
      <button type="button" onclick="ocultarGuia();" id="btnCancelar" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Cancelar la carga de los artículos a la guía" hidden><i class="fa fa-arrow-left"></i> Atrás</button>
      <button type="button" onclick="registrarGuia(1);" id="btnAplicar" class="btn btn-sm btn-success mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Finalizar la carga de los artículos a la guía" hidden><i class="fas fa-check"></i> Aplicar</button>
      <button type="button" onclick="mostrarAllGuias();" id="btnVerGuias" class="btn btn-sm btn-secondary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Ver todas las guías y sus articulos asignadas a esta remisión"><i class="fas fa-eye"></i> Mostrar guías</button>
      <button type="button" onclick="mostrarPanelGuias();" id="btnAtras" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Ver todas las guías y sus articulos asignadas a esta remisión" hidden><i class="fa fa-arrow-left"></i> Atrás</button>
      <button type="button" onclick="finalizarCarga();" id="btnFin" class="btn btn-sm btn-info mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Finalizar la carga de artículos para esta guía. Si lo hace ya no podrá cargar más artículos a esta guía." hidden><i class="fas fa-check-circle"></i> Finalizar guía</button>
    </div>
      ';
      echo '
      <dir class="row">
      <div class="col-4">
        <label id="txtGuiaL" hidden for="txtGuia">NÚMERO DE GUÍA</label>
      </div> 
      <div class="col-2">
        <label id="txtenviol" hidden for="txtenvio">COSTO DE LA GUÍA</label>
      </div>  

      <div class="col-3">
        <label id="paqueteriaTL" hidden>TIPO DE ENTREGA</label>
      </div>
      </div>

    <div class="row">
    <div class="col-4">
        <input type="text" id="txtGuia" name="txtguia" class="form-control" placeholder="Ingrese el número de guía" hidden>     
        <input type="hidden" id="idguia">
    </div>

    <div class="col-2">
        <input type="number" id="txtenvio" name="txtenvio" class="form-control" placeholder="Ingrese el costo de la guía" hidden>     
        <input type="hidden" id="costoenvio">
    </div>

    <div class="col-3">
        <!-- <label id="paqueteriaTL" hidden>TIPO DE ENTREGA</label> -->
        <select class="form-control" id="paqueteriaT" name="paqueteriaT" onchange="formaDeEnvio();" hidden>';
        $sqldom = 'SELECT DISTINCT(rc_paqueteria) AS p_id, p_nmb FROM remisionesc INNER JOIN paqueterias ON p_id = rc_paqueteria WHERE rc_remision = "'.$idremision.'" AND rc_paqueteria != 0 AND p_estatus = "A" AND p_adomicilio = 1';

        /* $sqldom = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_adomicilio = 1'; */
        $resultdom = setq($sqldom);
        if($resultdom->num_rows < 1){
          echo '<option value="">SIN RESULTADOS</option>';
        } else{
        while($rowdom = $resultdom -> fetch_array()){
          if($paq == $rowdom['p_id']){
            $indicador = ' selected';
          } else{
            $indicador = '';
          }
          echo '<option value="'.$rowdom['p_id'].'" '.$indicador.'>'.$rowdom['p_nmb'].' - DOMICILIO</option>';
        }
        /* echo '<option value="O">OCURRE</option>'; */
        }
        echo '</select>
    </div>

    <div class="col-2" id="divedit" hidden>
        <button type="button" class="btn btn-sm btn-primary" id="btnEdit" class="form-control"><i class="fas fa-edit"></i>Editar</button>
    </div>

    <div class="col-3" id="divsave" hidden>
        <button type="button" class="btn btn-sm btn-primary" id="btnSave" class="form-control"><i class="fas fa-save"></i> Guardar</button>
        <button type="button" class="btn btn-sm btn-danger" id="btnC" class="form-control"><i class="fas fa-window-close"></i> Cancelar</button>
    </div>
    </div>


    <!--
    <div class="col-3" style="padding-bottom: 5px;">
      <label id="paqueteriaOL" hidden>PAQUETERIA OCURRE</label>
        <select class="form-control" id="paqueteriaO" name="paqueteriaO" onchange="paqueteriaOcurre();" hidden>';
        $sqlp = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_ocurre = 1';
          $resultp = setq($sqlp);
          if($resultp->num_rows < 1){
            echo '<option value="">SIN RESULTADOS</option>';
          } else{
          while($rowp = $resultp -> fetch_array()){
            if($paq == $rowp['p_id']){
              $indicador = ' selected';
            } else{
              $indicador = '';
            }
            echo '<option value="'.$rowp['p_id'].'" '.$indicador.'>'.$rowp['p_nmb'].'</option>';
          }
          }
        echo '
        </select>
    </div>

    <div class="col-3" style="padding-bottom: 5px;">
        <label id="sucursalL" hidden>SUCURSAL OCURRE</label>
        <select class="form-control" id="sucursal" name="sucursal" hidden>';
        $sql = 'SELECT * FROM paqueterias_sucursales INNER JOIN paqueterias ON p_id = ps_paqueteria WHERE ps_plaza IN (SELECT DISTINCT(ps_plaza) FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT pc_sucursal FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre="1" AND p_estatus = "A" AND p_ocurre = 1;';
        $result = setq($sql);
        if($result->num_rows < 1){
          echo '<option value="">SIN RESULTADOS</option>';
        } else{
        while($row = $result -> fetch_array()){
          if($sucu == $row['ps_id']){
            $indicadorS = ' selected';
          } else{
            $indicadorS = '';
          }
          echo '<option value="'.$row['ps_id'].'" '.$indicadorS.'>'.$row['ps_sucursal'].' - '.$row['ps_nmb'].'</option>';
        }
        }
        echo '
        </select>
    </div> -->

    <div class="col-2" style="padding-bottom: 10px;">
        <button type="button" onclick="mostrarGuia();" id="btnEditar" class="btn btn-primary mb-1 ml-1" data-toggle="tooltip" data-placement="top" title="Asignar el número de guía" hidden><i class="fas fa-edit"></i>Editar</button>
    </div>

    <input type="hidden" id="tipoGuia">
    
      ';
    $tablaAsignados = '
    <table class="table table-hover" id="myTableA">
    <thead class="thead-active bg-primary text-white">
    <tr class="tamanio-letra">
      <th class="tamanio-letra"></th>
      <th class="tamanio-letra font-letras truncar-texto">&nbsp;&nbsp;Producto</th>
      <th class="tamanio-letra font-letras truncar-texto">Paquetería</th>
      <th class="tamanio-letra font-letras truncar-texto">Fecha envío programada</th>
      <th class="tamanio-letra font-letras truncar-texto">Peso</th>
    </tr>
    </thead>
    <tbody>
    <tr style="background: #ededed" class="tamanio-letra sin-hover2">
      <td class="tamanio-letra" colspan="5"><center>No hay elementos para mostrar</center></td>
    </tr>
    </tbody>
    </table>
    ';

    $tablaNoAsignados = '
    <table class="table table-hover" id="myTableN">
    <thead class="thead-active bg-primary text-white">
    <tr class="tamanio-letra">
      <th class="tamanio-letra"></th>
      <th class="tamanio-letra font-letras truncar-texto">&nbsp;&nbsp;Producto</th>
      <th class="tamanio-letra font-letras truncar-texto">Paquetería</th>
      <th class="tamanio-letra font-letras truncar-texto">Fecha envío programada</th>
      <th class="tamanio-letra font-letras truncar-texto">Peso</th>
      <th class="tamanio-letra font-letras truncar-texto">Observaciones&nbsp;&nbsp;&nbsp;&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
    </table>
    ';

    $botones = '
      <div>
        <button type="button" onclick="asignarGuía()"; class="btn btn-sm btn-success"><i class="fas fa-chevron-right"></i></button>
      </div>
      <div>
        <button type="button" onclick="quitarGuía()"; class="btn btn-sm btn-success"><i class="fas fa-chevron-left"></i></button>
      </div>
    ';

// INICIO DEL PANEL DE CARGA DE GUIAS
    echo '
    <!-- Panel de secciones -->
    <div class="section col-12" id="panelGuias">
      <div class="section-uno col-12">
      <div class="col-12">
        <span>Artículos no asignados</span>
      </div>
        '.$tablaNoAsignados.'
      </div>

      <div class="section-dos contenedor col-12">
        '.$botones.'
      </div>

      <div class="section-tres col-12">
      <div class="col-12">
        <span>Artículos asignados</span>
      </div>
        '.$tablaAsignados.'
      </div>
    </div>';
    //FIN DEL PANEL DE CARGA DE GUIAS

    //INICIO DEL LA LISTA DE GUIAS DE LA REMISIÓN
  ?>
      <div class="card">
      <div class="card-body" id="listaGuias" hidden>
      <div class="col-12 table-responsive">
        <center>
          <table width="100%" class="mb-0 table table-hover table-striped" id="myTableGuias">
        <thead class="bg-light-blue bg-darken-2 ">
          <tr>
            <th><b>No. guía</b></th>
            <th><b>Generó</b></th>
            <th><b>Paqueteria</b></th>
            <th><b>Asignados</b></th>
            <th><b>Estatus</b></th>
            <th><b>Fecha creación</b></th>
            <th><b>Fecha final</b></th>
            <th><b></b></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
        </div>
      </div>
      </div>
  <?php
      echo '
      <script>
      function mandar(){
        var cotizacion = "'.$idremision.'";

        //var table = $("#myTableGuias").DataTable();
        $("#myTableGuias").DataTable().clear().draw();
        $("#myTableGuias").DataTable().destroy();
  
        $("#myTableGuias").DataTable( {
          paging: true,
          scrollY: 400,
          processing: true,
          serverside: true,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatableguiasxremision.php",
            type: "POST",
            datatype: "json",
            data:{ 
              cotizacion : cotizacion
            }
          },
          pageLength: "50",
          responsivePriority: 1,
          columnDefs: [
            {
                targets: 7, // Índice de la séptima columna (0 basado en índices)
                width: "20%",
            }
        ],
        });
      }
      
    </script>';
    //FIN DEL LA LISTA DE GUIAS DE LA REMISIÓN
?>
<script>
function cargarDatosN(bandera, pqt){
  if(pqt){
    var paqueteriaT =document.getElementById("paqueteriaT").value;
    var data = {
      bandera: bandera,
      idremision: "<?php echo $idremision; ?>",
      idcotiza: "<?php echo $idcotiza; ?>",
      direnvio: "<?php echo $direnvio; ?>",
      noasignados: "0",
      paqueteria: paqueteriaT
    };
  } else{
    var data = {
      bandera: bandera,
      idremision: "<?php echo $idremision; ?>",
      idcotiza: "<?php echo $idcotiza; ?>",
      direnvio: "<?php echo $direnvio; ?>",
      noasignados: "0"
    };
  }
  
  $.ajax({
      url: 'query/tablasguias.php',
      method: 'POST',
      dataType: 'json',
      data: data,
      success: function(data) {
          $('#myTableN tbody').html(data.tablaNoAsignados);
      },
      error: function() {
          alert('Hubo un error en la consulta AJAX');
      }
    });
}
  cargarDatosN(1, false);

  function cargarDatosA(guia, bandera){
  var data = {
      guia: guia,
      bandera: bandera,
      idremision: "<?php echo $idremision; ?>",
      idcotiza: "<?php echo $idcotiza; ?>",
      direnvio: "<?php echo $direnvio; ?>",
      asignadospreviamente: "0"
    };
  $.ajax({
    url: 'query/tablasguias.php',
    method: 'POST',
    dataType: 'json',
    data: data,
    success: function(data) {
        $('#myTableA tbody').html(data.tablaAsignados);
    },
    error: function() {
      alert('Error 203');
    }
  });
}
 function mostrarGuia(tipo) {
  var texto = '';
  var txtGuia = document.getElementById("txtGuia");
  var tipoGuia = document.getElementById("tipoGuia");
  var txtGuiaL = document.getElementById("txtGuiaL");
  var txtenviol = document.getElementById("txtenviol");
  var txtenvio = document.getElementById("txtenvio");
  var btnNuevo = document.getElementById("btnNuevo");
  var btnAplicar = document.getElementById("btnAplicar");
  var btnCancelar = document.getElementById("btnCancelar");
  var paqueteriaT = document.getElementById("paqueteriaT");  
  var paqueteriaTL = document.getElementById("paqueteriaTL");
  var btnNuevoSinGuia = document.getElementById("btnNuevoSinGuia");

  txtGuia.removeAttribute("hidden");
  if(tipo == 0){ //Grupo con guía
    txtGuia.removeAttribute("readonly");
    txtenvio.removeAttribute("readonly");
    txtenvio.value = "0";
    tipoGuia.value = "0";
  } else{ //Grupo guia vacia/Sin guía
    tipoGuia.value = "1";
  }
  txtGuia.removeAttribute("hidden");
  txtGuiaL.removeAttribute("hidden");
  txtenviol.removeAttribute("hidden");
  txtenvio.removeAttribute("hidden");

  btnNuevo.setAttribute("hidden", true);
  btnNuevoSinGuia.setAttribute("hidden", true);

  btnAplicar.removeAttribute("hidden");
  btnCancelar.removeAttribute("hidden");
  paqueteriaT.removeAttribute("hidden");
  paqueteriaT.setAttribute("required", true);
  paqueteriaTL.removeAttribute("hidden");
  txtGuia.focus();
  
}

function ocultarGuia(){
  var txtGuia = document.getElementById("txtGuia");
  var txtenviol = document.getElementById("txtenviol");
  var txtenvio = document.getElementById("txtenvio");
  var tipoGuia = document.getElementById("tipoGuia");
  //var btnEditar = document.getElementById("btnEditar");
  var btnCancelar = document.getElementById("btnCancelar");
  var btnNuevo = document.getElementById("btnNuevo");
  var btnAplicar = document.getElementById("btnAplicar");
  var paqueteriaT = document.getElementById("paqueteriaT");
  /* var paqueteriaO = document.getElementById("paqueteriaO");
  var sucursal = document.getElementById("sucursal"); */
  var btnNuevoSinGuia = document.getElementById("btnNuevoSinGuia");
  var idguia = document.getElementById("idguia");
  var btnFin = document.getElementById("btnFin");
  
  var paqueteriaTL = document.getElementById("paqueteriaTL");
  var divedit = document.getElementById("divedit");
  /* var paqueteriaOL = document.getElementById("paqueteriaOL");
  var sucursalL = document.getElementById("sucursalL"); */

  //txtGuia.setAttribute("readonly", true);
  txtGuia.setAttribute("hidden", true);
  tipoGuia.value = "";
  txtGuiaL.setAttribute("hidden", true);
  txtenvio.setAttribute("hidden", true);
  txtenviol.setAttribute("hidden", true);
  
  txtGuia.setAttribute("hidden", true);
  tipoGuia.value = "";
  txtGuiaL.setAttribute("hidden", true);
  //btnEditar.setAttribute("hidden", true);
  btnCancelar.setAttribute("hidden", true);    
  btnNuevo.removeAttribute("hidden");
  //btnNuevoSinGuia.removeAttribute("hidden");
  btnAplicar.setAttribute("hidden", true);

  paqueteriaT.setAttribute("hidden", true);
  paqueteriaT.removeAttribute("required");
  paqueteriaT.removeAttribute("disabled");

  paqueteriaTL.setAttribute("hidden", true);
  btnFin.setAttribute("hidden", true);

  divedit.setAttribute("hidden", true);

  cargarDatosN(1, false);
  habilitarCheckboxes(true);
  txtGuia.value = "";
  idguia.value = "";
  $("#idguia").val("");
  txtGuia.removeAttribute("readonly");
  paqueteriaT.removeAttribute("disabled");
}

  function habilitarCheckboxes(valor) {
  // Obtén todos los elementos con la clase "all-check"
  var checkboxes = document.querySelectorAll(".all-check");

  // Itera a través de los checkboxes y habilítalos
  checkboxes.forEach(function(checkbox) {
    checkbox.disabled = valor;
  });

  $('#myTableA tbody').html('<tr style="background: #ededed" class="sin-hover2"><td colspan="5"><center>No hay elementos para mostrar</center></td></tr>');
  }

  function asignarGuía(){
  var bandera = 0;
  var guia = document.getElementById("idguia").value;
  //Determinamos el tipo de guia
  var paqueteriaT = document.getElementById("paqueteriaT").value;
  var paqueteria = 0;
  paqueteria = paqueteriaT;
  var checkboxes = obtenerIDs("myTableN");
  $.ajax({
  url: 'query/definirguias.php',
  method: 'POST',      
  data: { 
    checkboxes: checkboxes, 
    tipo: "In", 
    guia: guia,
    paqueteria: paqueteria
  },
  success: function(data) {
    cargarDatosA(guia, bandera);
    cargarDatosN(bandera, true);
    if(data == 1){
      Swal.fire({
        title: 'Atención',
        text: 'En uno o varios artículos el tipo de envío no coincide la paqueteria configurada',
        icon: 'error',
        allowOutsideClick: false
      });
    }
  },
  error: function() {
    alert('Error 201');
  }
  });
  }

  function quitarGuía(){ 
  var guia = document.getElementById("idguia").value;
  var checkboxes = obtenerIDs("myTableA");
  var bandera = 0;
  $.ajax({
  url: 'query/definirguias.php',
  method: 'POST',      
  data: { 
    checkboxes: checkboxes, 
    tipo: "Del", 
    guia: guia
  },
  success: function(data) {
    if(data == 0){
      cargarDatosA(guia, bandera);
      cargarDatosN(bandera, true);
    }
  },
  error: function() {
    alert('Error 202');
  }
  });
  }

  function obtenerIDs(idTabla) {
  var table = document.getElementById(idTabla);
  var checkboxes = table.querySelectorAll('input[type="checkbox"]:checked');
  var ids = [];

  checkboxes.forEach(function(checkbox) {
      ids.push(checkbox.id);
  });

  return ids.join(", ");
  }

  function mostrarAllGuias() {
  // Elementos HTML
  var panelGuias = document.getElementById("panelGuias");
  var listaGuias = document.getElementById("listaGuias");

  var paqueteriaT = document.getElementById("paqueteriaT");
  var paqueteriaTL = document.getElementById("paqueteriaTL");
  var txtGuiaL = document.getElementById("txtGuiaL");
  var txtenvio = document.getElementById("txtenvio");
  var txtenviol = document.getElementById("txtenviol");

  var btnVerGuias = document.getElementById("btnVerGuias");
  var txtGuia = document.getElementById("txtGuia");
  var btnCancelar = document.getElementById("btnCancelar");
  var btnNuevo = document.getElementById("btnNuevo");
  var btnNuevoSinGuia = document.getElementById("btnNuevoSinGuia");
  var btnAplicar = document.getElementById("btnAplicar");
  var btnAtras = document.getElementById("btnAtras");
  var divedit = document.getElementById("divedit");

  // Ocultar elementos
  panelGuias.setAttribute("hidden", true);
  btnVerGuias.setAttribute("hidden", true);
  txtGuia.setAttribute("hidden", true);
  btnCancelar.setAttribute("hidden", true);
  btnNuevo.setAttribute("hidden", true);
  btnNuevoSinGuia.setAttribute("hidden", true);
  btnAplicar.setAttribute("hidden", true);
  divedit.setAttribute("hidden", true);

  paqueteriaT.setAttribute("hidden", true);  
  paqueteriaTL.setAttribute("hidden", true);
  txtGuiaL.setAttribute("hidden", true);
  txtenviol.setAttribute("hidden", true);
  txtenvio.setAttribute("hidden", true);
  // Mostrar elementos
  btnAtras.removeAttribute("hidden");
  //Mostramos el panel de listas de las guías
  listaGuias.removeAttribute("hidden");
  mandar();
  }


  function mostrarPanelGuias() {
  // Elementos HTML
  var panelGuias = document.getElementById("panelGuias");
  var listaGuias = document.getElementById("listaGuias");
  var btnNuevoSinGuia = document.getElementById("btnNuevoSinGuia");
  var paqueteriaT = document.getElementById("paqueteriaT");
  var paqueteriaTL = document.getElementById("paqueteriaTL");
  var txtGuia = document.getElementById("txtGuia");
  var txtGuiaL = document.getElementById("txtGuiaL");
  var txtenvio = document.getElementById("txtenvio");
  var txtenviol = document.getElementById("txtenviol");
  var idguia = document.getElementById("idguia");
  var btnVerGuias = document.getElementById("btnVerGuias");
  var txtGuia = document.getElementById("txtGuia");
  var btnCancelar = document.getElementById("btnCancelar");
  var btnNuevo = document.getElementById("btnNuevo");
  var btnAplicar = document.getElementById("btnAplicar");
  var divedit = document.getElementById("divedit");
  //var btnEditar = document.getElementById("btnEditar");
  var btnAtras = document.getElementById("btnAtras");

  // Mostrar panelGuias y btnVerGuias
  panelGuias.removeAttribute("hidden");
  btnVerGuias.removeAttribute("hidden");

  // Verificar el valor de txtGuia
  if (txtGuia.value !== "") {
  // Configuración cuando txtGuia no está vacío
  //txtGuia.setAttribute("readonly", true);
  btnCancelar.removeAttribute("hidden");
  btnNuevo.setAttribute("hidden", true);
  btnNuevoSinGuia.setAttribute("hidden", true);
  btnAplicar.removeAttribute("hidden");
  txtGuia.removeAttribute("hidden");
  divedit.removeAttribute("hidden");
  } else {
  // Configuración cuando txtGuia está vacío
  txtGuia.setAttribute("hidden", true);
  btnCancelar.setAttribute("hidden", true);
  btnNuevo.removeAttribute("hidden");
  //btnNuevoSinGuia.removeAttribute("hidden");
  btnAplicar.setAttribute("hidden", true);
  txtGuia.setAttribute("hidden", true);
  divedit.setAttribute("hidden", true);
  }

  if(idguia.value != ""){
    paqueteriaT.removeAttribute("hidden");
    paqueteriaTL.removeAttribute("hidden");
    txtGuia.removeAttribute("hidden");
    txtGuiaL.removeAttribute("hidden");
    txtenvio.removeAttribute("hidden");
    txtenviol.removeAttribute("hidden");
    btnAplicar.setAttribute("hidden", true);
  }

  // Ocultar btnAtras
  btnAtras.setAttribute("hidden", true);
  listaGuias.setAttribute("hidden", true);
  }



function paqueteriaOcurre() {
  var idPaqueteria = document.getElementById('paqueteriaO').value;
  var data = {
    idPaqueteria: idPaqueteria,
    cp: "<?php echo $cp; ?>"
  };
    $.ajax({
    url: 'query/selectsucursales.php',
    method: 'POST',
    dataType: 'json',
    data: data, // Los datos que quieres enviar
    success: function (response) {
      // La función que se ejecuta cuando la consulta AJAX es exitosa
      var select = $('#sucursal');

      // Limpia las opciones actuales en el select
      select.empty();

      if (response.length === 0 || response === 1 || response === 2) {
        // Si no hay resultados o el valor es 1 o 2, agrega una opción "Sin resultados"
        select.append($('<option></option>')
          .attr('value', '')
          .text('SIN RESULTADOS'));
      } else {
        // Si hay resultados, llena el select con las opciones obtenidas de la consulta
        $.each(response, function (key, value) {
          select.append($('<option></option>')
            .attr('value', value.id)
            .text(value.nombre));
        });
      }
    }
  });
}

function verificaValores(){
  var formadeenvio =document.getElementById("paqueteriaT");
  var txtGuia =document.getElementById("txtGuia");
  var txtenvio =document.getElementById("txtenvio");
  var tipoGuia = document.getElementById("tipoGuia");

  if (txtGuia.value === "" && tipoGuia.value == "0"){
    return 'EL CAMPO "NÚMERO DE GUÍA" ESTÁ VACÍO';
  }
  /* if (txtenvio.value === "" || parseFloat(txtenvio.value) <= 0){
    return 'EL COSTO DE LA GUÍA ES INVALIDO';
  } */
  if (formadeenvio.value === ""){
    return 'EL CAMPO "FORMA DE ENVÍO" ESTÁ VACÍO';
  }

  return true;
}

function registrarGuia(tipo){
  var btnAplicar =document.getElementById("btnAplicar");
  var formadeenvio = document.getElementById("paqueteriaT");
  var formadeenvioL = document.getElementById("paqueteriaTL");
  var divedit = document.getElementById("divedit");
  var btnFin = document.getElementById("btnFin");
  var remision = "<?php echo $idremision; ?>";
  var txtGuia = document.getElementById("txtGuia");
  var txtenvio = document.getElementById("txtenvio");
  var guia = txtGuia.value;
  var tipoGuia = document.getElementById("tipoGuia").value;
  var idguia = document.getElementById("idguia");
  var paqueteria = 0;
  paqueteria = formadeenvio.value;

  var regreso = verificaValores();
  if(regreso != true){
    alert(regreso);
  } else{
    $.ajax({
      url: 'query/registrarguia.php',
      method: 'POST',
      dataType: 'json',
      data: {
        tipo: tipo,
        remision: remision, 
        guia: guia,  
        paqueteria: paqueteria,         
        tipoGuia: tipoGuia,
        costo: txtenvio.value
      },
      success: function(data) {
        if(data.respuesta != 3){ // Es posible hacer el registro nuevo
          if(data.respuesta == 5){
            Swal.fire({ 
            title: 'Atención',
            text: 'Todas las guias asignadas ya están en uso. No es posible hacer uso de una nueva.',
            icon: 'error',
            allowOutsideClick: false
          });
          } else{
            idguia.value = data.idguia;
            formadeenvio.value = data.paqueteria;
            txtenvio.value = data.costo;
            formadeenvio.removeAttribute("hidden");
            formadeenvioL.removeAttribute("hidden");
            /* formadeenvio.setAttribute("disabled", true); */
            btnAplicar.setAttribute("hidden", true);
          if(data.respuesta == 4){
              cargarDatosN(1, true);
              cargarDatosA(data.idguia, 1)
              btnFin.setAttribute("hidden", true);
              divedit.setAttribute("hidden", true);
              formadeenvio.setAttribute("disabled", true);
              txtGuia.setAttribute("disabled", true);
              txtenvio.setAttribute("disabled", true);
            } else{
              cargarDatosN(0, true);
              cargarDatosA(data.idguia, 0)
              btnFin.removeAttribute("hidden");
              /* document.getElementById("guiasreg").innerHTML = "("+data.guiasreg+" asignadas)"; */
              divedit.removeAttribute("hidden");
              formadeenvio.setAttribute("disabled", true);
              txtGuia.setAttribute("readonly", true);
              txtenvio.setAttribute("readonly", true);
            }
          }
        } else { //No es posible asignar esa guia por que ya esta en uso o ya fue embarcada
          formadeenvio.removeAttribute("disabled")
          btnAplicar.removeAttribute("disabled")
          btnFin.setAttribute("hidden", true);
          Swal.fire({
            title: 'Atención',
            /* text: 'No es posible asignar esa guia por que ya esta en uso o ya fue embarcada', */
            text: 'Ya hay una guía registrada con ese nombre. Intente de nuevo.',
            icon: 'error',
            allowOutsideClick: false
          });

        }
      },
      error: function() {
          alert('Error 222');
      }
    });
  }
}

function cargarGuiaAlPanel(guia, tipo){
  // tipo 0 = No hacer inserción
  var txtGuia = document.getElementById("txtGuia");
  var btnAtras = document.getElementById("btnAtras");
  var txtGuiaL = document.getElementById("txtGuiaL");
  var txtenvio = document.getElementById("txtenvio");
  var txtenviol = document.getElementById("txtenviol");
  var btnAplicar = document.getElementById("btnAplicar");
  var panelGuias = document.getElementById("panelGuias");
  var listaGuias = document.getElementById("listaGuias");

  var paqueteriaT = document.getElementById("paqueteriaT");
  var paqueteriaTL = document.getElementById("paqueteriaTL");

  txtGuia.value = guia;
  btnAtras.click();
  txtGuia.removeAttribute("hidden");
  txtGuiaL.removeAttribute("hidden");
  txtenvio.removeAttribute("hidden");
  txtenviol.removeAttribute("hidden");
  listaGuias.setAttribute("hidden", true);
  panelGuias.removeAttribute("hidden");
  paqueteriaTL.removeAttribute("hidden");
  paqueteriaT.removeAttribute("hidden");
  registrarGuia(tipo);
  
}

function finalizarCarga(){
  var idguia = document.getElementById("idguia").value; // Obtener el valor del elemento
  var btnCancelar = document.getElementById("btnCancelar");

  verificaTablaA()
  .then(function(found) {
  if (found) {
  Swal.fire({
  title: 'Atención',
  text: "¿Estás seguro de finalizar la carga de artículos para esta guía?",
  icon: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  cancelButtonText: 'Cancelar',
  confirmButtonText: 'Estoy seguro'
}).then((result) => {
  if (result.isConfirmed) {
    $.ajax({
    url: 'query/finalizarcarga.php',
    method: 'POST',      
    data: { 
      idguia: idguia
    },
    success: function(data) {
      if(data == 0){      
        cargarDatosN(1, false); //Deshabilitamos los check de la tabla de no asignados
        habilitarCheckboxes(true);
        txtGuia.value = "";
        idguia.value = ""; 
        btnCancelar.click();
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        })

        //Mandamos la notificación PUSH a logística de que hay una nueva guía lista para ser embarcada
        $.ajax({
              url: 'task/notificacionpushgrupos.php',
              method: 'POST',
              data: {
                      'tipo': 'GUIAPARAEMBARCAR',
                      'grupos': '"LOGISTIC"',
                      'idguia': idguia,
                      'titulo': 'NUEVA GUÍA LISTA PARA SE EMBARCADA'
                    },
            }).done(function(data){
              Toast.fire({
                icon: 'success',
                title: 'Guía finalizada con éxito'
              })
            });     
        
      } else if(data == 2){
        Swal.fire({
        title: 'Error',
        text: 'Para finalizar la guía es necesario cargar imágenes de evidencia.',
        icon: 'info',
        allowOutsideClick: false,
        showCancelButton: true,
        confirmButtonText: 'OK',
        }).then((result) => {
        if (result.isConfirmed) {
          mostrarAllGuias();
          setTimeout(function() {
            document.getElementById("evidencia" + idguia).click();
          }, 1500); // 2000 milisegundos (2 segundos)
          /* document.getElementById("evidencia"+idguia).click(); */
        }
        });

      } else if(data == 3){
        cargarDatosN(1, false); //Deshabilitamos los check de la tabla de no asignados
        habilitarCheckboxes(true);
        txtGuia.value = "";
        idguia.value = ""; 
        btnCancelar.click();
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        })

         //Mandamos la notificación PUSH a logística de que hay una nueva guía lista para ser embarcada
         $.ajax({
              url: 'task/notificacionpushgrupos.php',
              method: 'POST',
              data: {
                      'tipo': 'GUIAPARAEMBARCAR',
                      'grupos': '"LOGISTIC"',
                      'idguia': idguia,
                      'titulo': 'NUEVA GUÍA LISTA PARA SE EMBARCADA'
                    },
            }).done(function(data){
              Toast.fire({
                icon: 'success',
                title: 'Guía finalizada con éxito'
              })
            }); 

        // Agrega una redirección después de 3 segundos (3000 milisegundos)
        setTimeout(function() {
          window.location.href = '?modulo=embarcamiento&accion=index';
        }, 2000);
      } else{
        alert("Error 503");
      }
    },
    error: function() {
      alert('Error 202');
    }
  });
  }
  })
  } else{
    Swal.fire({
      title: 'Error',
      text: 'No es posible finalizar la guía ya que no contiene artículos',
      icon: 'error',
      allowOutsideClick: false
    });
  }
})
  .catch(function(error) {
    console.error(error); // Manejar errores de la promesa
  })
}

function verificaTablaA() {
  return new Promise(function(resolve, reject) {
    var guia = document.getElementById("idguia").value;
    $.ajax({
      url: 'query/consultararticulosguia.php',
      method: 'POST',
      data: {
        idguia: guia
      },
      success: function(data) {
        if (data == 0) {
          resolve(false); // La promesa se resuelve como false si no se encuentra ningún elemento
        } else {
          resolve(true); // La promesa se resuelve como true si se encuentra algún elemento
        }
      },
      error: function() {
        reject('Error 702'); // Rechaza la promesa en caso de error
      }
    });
  });
}

// Obtén una referencia al elemento en el que deseas detectar la tecla "Enter"
var inputElement = document.getElementById("txtGuia");

// Agrega un event listener para el evento "keydown" en el elemento
inputElement.addEventListener("keydown", function(event) {
  // Verifica si la tecla presionada es "Enter" (código de tecla 13)
  if (event.keyCode === 13) {
  event.preventDefault();
  // Obtén una referencia al elemento con el ID "btnAplicar"
  var botonAplicar = document.getElementById("btnAplicar");

  // Verifica si la propiedad "hidden" está establecida en el elemento
  if (!botonAplicar.hidden) {
    btnAplicar.click();
  }    
  }
});


document.addEventListener("DOMContentLoaded", function() {
    var divedit = document.getElementById("divedit");
    var divsave = document.getElementById("divsave");
    var idguia = document.getElementById("idguia");

    var btnEdit = document.getElementById("btnEdit");
    var btnSave = document.getElementById("btnSave");
    var btnC = document.getElementById("btnC");
    var txtGuia = document.getElementById("txtGuia");
    var txtenvio = document.getElementById("txtenvio");
    var paqueteriaT = document.getElementById("paqueteriaT");
});


btnEdit.addEventListener("click", function() {
  txtGuia.removeAttribute("readonly");
  txtenvio.removeAttribute("readonly");
  paqueteriaT.removeAttribute("disabled");
  divedit.setAttribute("hidden", true);
  divsave.removeAttribute("hidden");
});

btnC.addEventListener("click", function() {
  txtGuia.setAttribute("readonly", true);
  txtenvio.setAttribute("readonly", true);
  paqueteriaT.setAttribute("disabled", true);
  divsave.setAttribute("hidden", true);
  divedit.removeAttribute("hidden");
});

btnSave.addEventListener("click", function() {
  var nmb = txtGuia.value;
  var paqueteria = paqueteriaT.value;
  var guia = idguia.value;
  var costo = txtenvio.value;

  $.ajax({
      url: 'query/editguia.php',
      method: 'POST',
      /* dataType: 'json', */
      data: {
        idguia: guia, 
        nmb: nmb, 
        paqueteria: paqueteria,
        costo: costo
      },
      success: function(data) {
        if(data == 0){
          alert("Error 701");
        } else if(data == 1){
          divsave.setAttribute("hidden", true);
          divedit.removeAttribute("hidden");
          txtGuia.setAttribute("readonly", true);
          txtenvio.setAttribute("readonly", true);
          paqueteriaT.setAttribute("disabled", true);
          cargarDatosA(guia, 0);
          cargarDatosN(0, true);
        } else if(data == 3){
          Swal.fire({
            title: 'Atención',
            text: 'Para poder editar el "Tipo de entrega" es necesario quitar los artículos que se encuentran en la guía debido a que la paquetería es diferente',
            icon: 'error',
            allowOutsideClick: false
          });
        } else{
          Swal.fire({
            title: 'Atención',
            text: 'Ya hay un registro perteneciente a esa guía',
            icon: 'error',
            allowOutsideClick: false
          });
        }
      },
      error: function() {
          alert('Error 702');
      }
    });
});


function deleteGuia(guia){
  var idguia = document.getElementById("idguia").value;
  var btnCancelar = document.getElementById("btnCancelar");
  var btnAtras = document.getElementById("btnAtras");
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  })
  Swal.fire({
  title: 'Atención',
  text: "¿Estás seguro de eliminar esta guía?. Los artículos asociados serán retirados de la guía.",
  icon: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Estoy seguro',
  cancelButtonText: 'Cancelar'
}).then((result) => {
  if (result.isConfirmed) {

    $.ajax({
      url: 'query/eliminarguia.php',
      method: 'POST',
      data: {
        id: guia
      },
      success: function(data) {
        if(data == 1){
          if(guia == idguia){
            btnAtras.click();
            btnCancelar.click();
          }
          mandar();
          Toast.fire({
            icon: 'success',
            title: 'La guía se eliminó satisfactoriamente'
          })
        } else{
          Toast.fire({
            icon: 'error',
            title: 'Error 704. Ocurrió un erro al eliminar la guía'
          })
          /* alert("Error 704"); */
        }
      },
      error: function() {
          alert('Error 709');
      }
    });
  }
})
}
    </script>
    