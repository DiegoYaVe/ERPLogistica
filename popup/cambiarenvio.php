<?php 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado

?>
<script language="javascript">
  function checksubmit() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
  }
  function cargar() {
    opener.location.reload();
    window.close();
  }
  function closewindow() {
    window.opener.location.reload();
    window.close();
  }
</script>
<?php
ini_set('display_errors', 0);
session_start();
include_once('../funciones.php');

$idremision = $_GET['id'];
/* die($idremision); */

$sqlcc = 'SELECT * FROM remisiones WHERE r_id = "' . $idremision . '"';
/* $sql = 'SELECT * FROM remisionesd
              WHERE rd_remision = "'.$idremision.'" ORDER BY rd_id ASC';*/
$resultcc = setq($sqlcc);
$rowcc = $resultcc->fetch_array();
$min = 0;
if($rowcc['r_precioenvio'] == 0 && $rowcc['r_descuento'] > 0) $min = 1;
$preciosend = $rowcc['r_precioenvio'];
$cotizacion = busca($idremision, 'crm_cotizaciones', 'cc_remision', 'cc_id');
$direccion = busca($cotizacion, 'crm_cotizaciones', 'cc_id', 'cc_direnvio');
$cp = busca($direccion, 'crm_direcciones', 'cd_id', 'cd_cp');
$cob = busca($cp, 'paqueterias_cobertura', 'pc_paqueteria = "1" AND pc_cp', 'pc_tipo');
$sqldom = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_adomicilio = 1';
$resultdom = setq($sqldom);
if ($resultdom->num_rows < 1) {
  $reado = ' disabled';
} else {
  $reado = '';
}

$sqlo2 = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_ocurre = 1';
$resulto2 = setq($sqlo2);
if ($resulto2->num_rows < 1) {
  $reado2 = ' disabled';
} else {
  $reado2 = '';
}
echo '
<style>
  #myTableG {
    width: 100%;
    border-collapse: collapse;
  }

  #myTableG th, #myTableG td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
    /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
  }

  #myTableG tbody tr:nth-child(odd) td {
    /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
  }

  #myTableG tbody tr:nth-child(even) td {
    /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
  }
  
  #myTableG {
    font-size: 12px; /* Cambia el tamaño de letra deseado */
  }
</style>
';

echo '
<div class="container mt-1 ">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary"><h2><span style="color: #5490c6;">Detalles remisión</span></h2><br>';
echo '<div class="row">';
echo '<div class="col-8">';
if($direccion == "0"){
  $bandera = "2";
} else {
  $sqlget = 'SELECT cd_cp, cd_colonia, cd_municipio, cd_estado, cd_calle, cd_nume, cd_pais FROM crm_direcciones WHERE cd_id ="'.$direccion.'"';
  $resultget = setq($sqlget);
  list($codp, $colonia, $municipio, $estadoh, $calle, $nume, $pais) = $resultget->fetch_array();
  if($pais == "146"){
    if($codp == "" || $colonia == "" || $municipio == "" || $estadoh == "" || $calle == "" || $nume == ""){
      $bandera = "1";
    }
  } else {
    if($calle == "") $bandera = "1";
  }
}
echo '<input id="checkdir" name="checkdir" value="'.$bandera.'" type="hidden">';

$client = $rowcc['r_cliente'];

$cliente = busca($client, 'crm_clientes', 'c_id', 'c_nmb');
$cliente .= " ".busca($client, 'crm_clientes', 'c_id', 'c_apellidos');

echo '      
      <span>Folio: ' . $rowcc['r_folio'] . '</span><br>
      <span>Cliente: ' . $cliente. '</span><br>';
if (!empty($rowcc['r_descripcion'])) {
  echo '<span>Descripción: ' . $rowcc['r_descripcion'] . '</span><br>';
}
$telefono = busca($client,'crm_clientes', 'c_id', 'c_telefono2');
$country = intval(busca($direccion, 'crm_direcciones', 'cd_id', 'cd_pais'));
$dirsend = '';
$flagcountry = 0;
if ($country != 146 && $country != 0) {
  $dirsend = busca($direccion, 'crm_direcciones', 'cd_id', 'cd_calle');
  $flagcountry = 1; //El país es diferente de México
} else {
  $dirsend = busca($direccion, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
  if(!$dirsend) $dirsend = 'SIN DIRECCIÓN REGISTRADA';
}

$pais = busca($country, 'paises', 'p_id', 'p_nmb');
if(!$pais) $pais= '';
else $pais = ', ' .$pais;
echo '
      <span>Direccion: ' . $dirsend .  $pais . '</span><br>
      <span>Teléfono: ' . $telefono . '</span><br>
      ';
if ($flagcountry == 1) {
  echo '<br><h5><span style="color: #5490c6;">ESTE ENVÍO ES AL EXTRANJERO</span></h5>';
}
echo '</div>';
echo '<div class="col-4" style="vertical-align: top;">      
      <div>
      <h2><span style="color: #5490c6;">Importe total</span></h2><br>
      <h4><span style="color: #5490c6;">$' . number_format($rowcc['r_total'], 2) . '</span></h4><br>
      </div>
      </div>
      </div>
      </div>';

//INICA FORMULARIO DE DIRECCIÓN
/* if(empty($direnvio) || $direnvio == 0){ */
  echo '
    <div class="container mt-1 col-12" id="camposdir">
      <form id="setcotiza" action="?modulo=formaenvio&accion=insertdireccion&id='.$idremision.'" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">';
      echo '
        <div class="row nowrap">';    
          echo '
          <div class="col-12 col-md-12 alert alert-primary" hidden id="titulodir">Ingrese una dirección del cliente</div>';
          $dir = busca($rowcc['r_cliente'], 'crm_direcciones', 'cd_cliente', 'COUNT(*)');
          if($dir != 0){
            $seldir = '';
            $formdir = "hidden";
          } else {
            $seldir = 'selected';
            $formdir = "";
          }
          echo '
          <div>
          <div class="row" id="selectdir">
            <div class="col-12 col-md-6">
              <div class="mb-5">
                <label for"cp">Seleccionar dirección</label>
                <select id="direcciones" name="direcciones" onchange="cambiardir();" class="form-control"   '.$discheck.'>
                  <option value="YYY" disabled> Seleccionar una dirección </option>';
                  $i = 0;
                  $sqldir = 'SELECT * FROM crm_direcciones WHERE cd_cliente = "'.$rowcc['r_cliente'].'"';
                  $result = setq($sqldir);
                  while($row = $result -> fetch_array()){
                    if($direnvio == $row['cd_id'] || $row['cd_predeterminada'] == "1") {
                      $seld = 'selected';
                    }else{
                      $seld = '';
                    }
                    echo '<option value="'.$row['cd_id'].'" '.$seld.' >'.$row['cd_calle'].' '.$row['cd_nume'].' '.$row['cd_numi'].' '.$row['cd_colonia'].' '.$row['cd_municipio'].' '.busca($row['cd_estado'], 'estado', 'e_id', 'e_nmb').' '.$row['cd_cp'].'</option>';
                  }
                echo '
                  <option value="XXX" '.$seldir.'> Nueva dirección</option>
                </select> 
              </div>
            </div>';
            if($direnvio){
              $sqlget = 'SELECT cd_cp, cd_colonia, cd_municipio, cd_estado, cd_calle, cd_nume, cd_numi FROM crm_direcciones WHERE cd_id ="'.$direnvio.'"';
              $resultget = setq($sqlget);
              list($cp, $colonia, $municipio, $estadoh, $calle, $nume, $numi) = $resultget->fetch_array();
              $estado =busca($estadoh, 'estado', 'e_id', 'e_nmb');
            } else {
              $cp="";
              $colonia="";
              $municipio="";
              $estadoh="";
              $estado="";
              $calle="";
              $nume="";
              $numi="";
            }                 
            echo '<div class="col-6 col-md-6">
              <div class="mb-5">
                <label for"correo">Observaciones de envío</label>
                <input type="text" name="observadir" maxlenght="100" id="observadir" value="'.$observadir.'" class="form-control" placeholder="Indicaciones" />
                <input type="hidden" name="cliente" value="'.$rowcc['r_cliente'].'"/>
                <input type="hidden" name="remision" value="'.$_GET['id'].'"/>
              </div>
            </div>
          </div>
          ';
          echo '<div class="row" id="formdir">
            <input type="hidden" id="countdir">
            <div class="col-12 col-md-3">
              <div class="mb-5">
                <label >Código postal</label>
                <input type="text" value="'.$cp.'" class="form-control" onchange="colonia();" id="cp" name="cp"  >
                <div id="nameError" class="error-message"></div>
              </div>
            </div>
            <div class="col-12 col-md-5">
              <div class="mb-5">
                <label >Colonia</label>
                <select id="col" name="col" value="'.$colonia.'" class="form-control"  >
                  <option value="XXX" disabled selected>Seleccione la colonia</option>
                </select>
                <input type="hidden" value="'.$colonia.'" name="coldesc" id="coldesc">
              </div>
            </div> 
            <div class="col-12 col-md-4">
              <div class="mb-5">
                <label >Estado</label>
                <input type="text" value="'.$estado.'" class="form-control" id="estado" name="estado"  >
                <input type="hidden" value="'.$estadoh.'" class="form-control" id="estadoh" name="estadoh" >
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="mb-5">
                <label >Municipio</label>
                <select id="municipio" value = "'.$municipio.'" name="municipio" class="form-control"  >
                  <option value="XXX" disabled selected>Seleccione el municipio</option>
                </select>
                <input type="hidden" value="'.$municipio.'" name="munidesc" id="munidesc">
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="mb-5">
                <label >Calle</label>
                <input type="text" value="'.$calle.'" class="form-control" id="calle" name="calle"  >
              </div>
            </div>
            <div class="col-12 col-md-2">
              <div class="mb-5">
                <label >Número exterior</label>
                <input type="text" value="'.$nume.'" class="form-control" id="exterior" name="exterior"  >
              </div>
            </div>
            <div class="col-12 col-md-2">
              <div class="mb-5">
                <label >Número interior</label>
                <input type="text" value="'.$numi.'" class="form-control" id="interior" name="interior" >
              </div>
            </div>
          </div>
          </div>';
        echo '<div class="col-12 col-md-12 mb-6" id="btnsave">
          <center><button type="submit" class="btn btn-primary" id="guardar" ><i class="fa fa-save"></i> Guardar</button></center>
        </div>
      </div>
      </form>
    </div>
        ';
        ?>
        <script>
function cambiardescuento(num){
  var pordiv = document.getElementById('porcentajediv');
  var montodiv = document.getElementById('montodiv');
  var btnporcentaje = document.getElementById('btnporcentaje');
  var btnmonto = document.getElementById('btnmonto');
  var descuento = document.getElementById('descuento');
  var monto = document.getElementById('impdescuento');

  if(num == 0){
    btnporcentaje.hidden = true;
    btnmonto.hidden = false;
    pordiv.hidden = false;
    montodiv.hidden = true;
    monto.value="0";
  } else {
    btnporcentaje.hidden = false;
    btnmonto.hidden = true;
    pordiv.hidden = true;
    montodiv.hidden = false;
    monto.value="0";
  }
}
function colonia(){
  var selcol = document.getElementById("col");
  var coldesc = document.getElementById("coldesc").value;
  var estado = document.getElementById("estado");
  var estadoh = document.getElementById("estadoh");
  var cp = document.getElementById("cp");
  var municipio = document.getElementById("municipio");
  var munidesc = document.getElementById("munidesc").value;
  var nameError = document.getElementById("nameError");

  for (let i = selcol.options.length - 1; i > 0; i--) {
    selcol.remove(i);
  }
  selcol.value ="XXX";
  for (let i = municipio.options.length - 1; i > 0; i--) {
    municipio.remove(i);
  }
  municipio.value ="XXX";
  estado.value = "";
  estado.disabled = false;
  estadoh.value = "";
  if(cp.value.length == 5){
    $.ajax({
      type: "POST",
      url: "query/getcolonias.php",
      data: {"cp": cp.value},
      success: function(data) {
        
        var datax = JSON.parse(data);
        if(data == '{"cobertura":[null]}'){
          nameError.innerHTML = "El código postal ingresado no existe.";
        } else {
          nameError.innerHTML = "";
        }
        for(var i = 0; i<datax['colonias'].length; i++){
          const nuevaOpcion = document.createElement('option');
          nuevaOpcion.value = datax['colonias'][i]; 
          nuevaOpcion.text = datax['colonias'][i];
          selcol.append(nuevaOpcion);
        }
        for(var i = 0; i<datax['municipios'].length; i++){
          const nuevaOpcion = document.createElement('option');
          nuevaOpcion.value = datax['municipios'][i]; 
          nuevaOpcion.text = datax['municipios'][i]; 
          municipio.append(nuevaOpcion);
          if(datax['municipios'][i] == munidesc){
            nuevaOpcion.selected = true;
          } 
        }
        if(coldesc != ""){
          selcol.value = coldesc;
        } else {
          selcol.value = datax['colonias'][0];
        }
        if(munidesc != ""){
          municipio.value = munidesc;
        } else {
          municipio.value = datax['municipios'][0];
        }
        
        estado.value = datax['estados'][0];
        estadoh.value = datax['estadosid'][0];
        estado.disabled = true;
      }
    });
  }
}
colonia();
function cambiardir(){
  var seldir = document.getElementById("direcciones");
  var selcol = document.getElementById("col");
  var estado = document.getElementById("estado");
  var estadoh = document.getElementById("estadoh");
  var cp = document.getElementById("cp");
  var municipio = document.getElementById("municipio");
  var nume = document.getElementById("exterior");
  var numi = document.getElementById("interior");
  var nameError = document.getElementById("nameError");
  var munidesc = document.getElementById("munidesc");
  var coldesc = document.getElementById("coldesc");
  var calle = document.getElementById("calle");
  if(seldir.value == "XXX"){
    for (let i = selcol.options.length - 1; i > 0; i--) {
      selcol.remove(i);
    }
    selcol.value ="XXX";
    for (let i = municipio.options.length - 1; i > 0; i--) {
      municipio.remove(i);
    }
    municipio.value ="XXX";
    estado.value="";
    estadoh.value="";
    cp.value="";
    municipio.value="";
    estado.disabled = false;
    cp.focus();
  } else {
    $.ajax({
      type: "POST",
      url: "query/getdireccion.php",
      data: {"direccion": seldir.value},
      success: function(data) {
        var datax = JSON.parse(data);
        console.log(datax);
        munidesc.value = datax['municipio'];
        coldesc.value = datax['colonia'];
        cp.value = datax['cp'];
        calle.value = datax['calle'];
        numi.value = datax['numi'];
        nume.value = datax['nume'];
        colonia();
      }
    });
  }
}  
cambiardir();
function guardarform(){
  var form = document.getElementById("setcotiza");
  var elements = form.elements;

  for (var i = 0; i < elements.length; i++) {
    if (!elements[i].checkValidity()) {
      alert("Error al capturar sus datos, verifique su información para continuar.");
      elements[i].focus();
      event.preventDefault(); 
      return;
    }
  }

  form.submit();
}

function setdireccion(tipo = true){
  /* var check = document.getElementById('setdir'); */
  var campos = document.getElementById('camposdir');
  var seldir = document.getElementById("direcciones");
  var titulodir = document.getElementById("titulodir");
  var btnsave = document.getElementById("btnsave");

  campos.hidden = tipo;
  titulodir.hidden = tipo;
  btnsave.hidden = tipo;
}
setdireccion();
</script>
<?php
  echo '
  
                ';
      /* } */
//FINALIZA FORMULARIO DE DIRECCION

echo '
      <div class="row">
        <div class="col-12" style="display: flex; justify-content: end;">
          <b><i><span style="justify-content: left; display: flex; color: black;">* Artículo perteneciente a un paquete &nbsp;&nbsp;&nbsp;&nbsp; ** Artículo que es complemento</span></i></b><br>
        </div>
      </div>
      ';

//INICIA EL LISTADO DE PRODUCTOS
echo '<form action="?modulo=formaenvio&accion=cambiarenvio&id='.$idremision.'" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">
<div class="row" style="background: white;">
  <div class="card-body">
  <center>
    <div class="table-responsive col-12">
      <table class="table table-hover" id="myTableG">
      <thead class="thead-active bg-primary text-white">
        <tr>
          <th width="25%">Producto</th>
          <th width="8%">Peso</th>
          <th colspan="6" width="67%">Tipo de envío</th>
        </tr>
      </thead>
      <tbody>';
$k = 0;
if ($flagcountry == 0) {
  $cob = busca(busca($direccion, 'crm_direcciones', 'cd_id', 'cd_cp'), 'paqueterias_cobertura', 'pc_paqueteria = "1" AND pc_cp', 'pc_tipo');
} else {
  $cob = 2;
}

$sql = 'SELECT * FROM remisionesd INNER JOIN articulos ON rd_articulo = a_id WHERE rd_remision = "' . $idremision . '"';
$result = setq($sql);

function selectsucursal($k, $articulotipo, $cp, $cob, $padre, $reado, $reado2)
{

  if ($padre == NULL) {
    $clase = '';
    $clase2 = '';
  } else {
    $clase = ' sel' . $padre;
    $clase2 = ' selP' . $padre;
  }

  if ($articulotipo == "O") {
    $hid2 = 'hidden';
    $hid = '';
    if (!empty($reado2)) {
      $hid = 'hidden';
    }
  } else {

    if ($articulotipo == "P" || $articulotipo == "C") {
      $hid2 = 'hidden';
    } else {
      $hid2 = '';
    }
    $hid = 'hidden';
    if (!empty($reado)) {
      $hid2 = 'hidden';
    }

  }


  $paq = busca($k, 'remisionesc', 'rc_id', 'rc_paqueteria');
  $sucu = busca($k, 'remisionesc', 'rc_id', 'rc_sucursal');
  $indicador = '';
  $indicadorS = '';

  $selectdom =
    ' <select style="font-size: 12px;" class="form-control ' . $clase2 . '"  name="paqueteriaDom' . $k . '" id="paqueteriaDom' . $k . '" ' . $hid2 . '>';
  /* $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT DISTINCT(pc_sucursal) FROM paqueterias_cobertura WHERE pc_plaza IN (SELECT pc_plaza FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre = "1"'; */
  $sqldom = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_adomicilio = 1';
  $resultdom = setq($sqldom);
  if ($resultdom->num_rows < 1) {
    $selectdom .= '<option value="">SIN RESULTADOS</option>';
  } else {
    while ($rowdom = $resultdom->fetch_array()) {
      if ($paq == $rowdom['p_id']) {
        $indicador = ' selected';
      } else {
        $indicador = '';
      }
      $selectdom .= '<option value="' . $rowdom['p_id'] . '" ' . $indicador . '>' . $rowdom['p_nmb'] . '</option>';
    }
  }
  $selectdom .= '</select>';

  $k0 = "'" . $k . "'";
  $selectp =
    ' <select style="font-size: 12px;" class="form-control ' . $clase . '" onchange="paqueteriaOcurre(' . $k0 . ');" name="paqueteriaOcurre' . $k . '" id="paqueteriaOcurre' . $k . '" ' . $hid . '>';
  /* $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT DISTINCT(pc_sucursal) FROM paqueterias_cobertura WHERE pc_plaza IN (SELECT pc_plaza FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre = "1"'; */
  $sqlp = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_ocurre = 1';
  $resultp = setq($sqlp);
  if ($resultp->num_rows < 1) {
    $selectp .= '<option value="">SIN RESULTADOS</option>';
  } else {
    while ($rowp = $resultp->fetch_array()) {
      if ($paq == $rowp['p_id']) {
        $indicador = ' selected';
      } else {
        $indicador = '';
      }
      $selectp .= '<option value="' . $rowp['p_id'] . '" ' . $indicador . '>' . $rowp['p_nmb'] . '</option>';
    }
  }
  $selectp .= '</select>';

  $select =
    ' <td style="width: 200px;">';
  
  $select .= $selectp;
  $select .= '
            <select style="font-size: 12px;" class="form-control ' . $clase . '"  name="sucursalOcurre' . $k . '" id="sucursalOcurre' . $k . '" ' . $hid . '>';
  /* $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT DISTINCT(pc_sucursal) FROM paqueterias_cobertura WHERE pc_plaza IN (SELECT pc_plaza FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre = "1"'; */
  $sql = 'SELECT * FROM paqueterias_sucursales INNER JOIN paqueterias ON p_id = ps_paqueteria WHERE ps_plaza IN (SELECT DISTINCT(ps_plaza) FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT pc_sucursal FROM paqueterias_cobertura WHERE pc_cp = "' . $cp . '")) AND ps_ocurre="1" AND p_estatus = "A" AND p_ocurre = 1;';
  $result = setq($sql);
  if ($result->num_rows < 1) {
    $select .= '<option value="">SIN RESULTADOS</option>';
  } else {
    while ($row = $result->fetch_array()) {
      if ($sucu == $row['ps_id']) {
        $indicadorS = ' selected';
      } else {
        $indicadorS = '';
      }
      $select .= '<option value="' . $row['ps_id'] . '" ' . $indicadorS . '>' . $row['ps_sucursal'] . ' - ' . $row['ps_nmb'] . '</option>';
    }
  }
  $select .= '</select>';
  
  $select .= $selectdom;
  $select .= '</td>
          ';

  /* $select = ""; */

  return $select;
}

function imprimircheckboxes($k, $articulotipo, $cob, $reado, $reado2, $padre = NULL)
{
  /* $cob = ''; */
  if ($padre == NULL) {
    $clase = '';
    $pad = 0;
  } else {
    $clase = ' select' . $padre;
    $pad = $padre;
  }

  $color = ' style="background: #c8c8c8;"';
  if ($articulotipo == "D") {
    $estatusD = 'checked';
    $colorfondoD = $color;
  } else {
    $colorfondoD = '';
    $estatusD = '';
  }
  if ($articulotipo == "C") {
    $estatusC = 'checked';
    $colorfondoC = $color;
  } else {
    $estatusC = '';
    $colorfondoC = '';
  }
  if ($articulotipo == "O") {
    $estatusO = 'checked';
    $colorfondoO = $color;
  } else {
    $estatusO = '';
    $colorfondoO = '';
  }
  if ($articulotipo == "P") {
    $estatusP = 'checked';
    $colorfondoP = $color;
  } else {
    $estatusP = '';
    $colorfondoP = '';
  }

  if (!empty($reado) && $articulotipo == "D") {
    $estatusP = 'checked';
    $colorfondoP = $color;
    $colorfondoD = '';
    $estatusD = '';
  }

  if (!empty($reado2) && $articulotipo == "O") {
    $estatusP = 'checked';
    $colorfondoP = $color;
    $estatusO = '';
    $colorfondoO = '';
  }

  $fenvio = busca($k, 'remisionesc', 'rc_id', 'rc_fenvio');
  $i = 0;
  $k1 = "'" . $k . $i . "'";
  $k2 = "'" . $k . ($i + 1) . "'";
  $k3 = "'" . $k . ($i + 2) . "'";
  $k4 = "'" . $k . ($i + 3) . "'";
  $k0 = "'" . $k . "'";
  $pad = "'" . $pad . "'";
  $retorno = '
    <td' . $colorfondoD . ' class="select-all' . $padre . '" id="td' . $k . $i . '">
        <input class="form-check-input' . $clase . '" onclick="selectOne(this, ' . $k0 . ', ' . $k1 . ', ' . $pad . ', ' . $cob . ');" type="checkbox" name="tipo' . $k . '" id="domicilio' . $k . '" value="D" ' . $estatusD . ' ' . $reado . '>
        <label class="form-check-label" for="domicilio">A domicilio</label>
    </td>&nbsp;&nbsp;
    <td' . $colorfondoC . ' class="select-all' . $padre . '" id="td' . $k . ($i + 1) . '">
        <input class="form-check-input' . $clase . '" onclick="selectOne(this, ' . $k0 . ', ' . $k2 . ', ' . $pad . ', ' . $cob . ');" type="checkbox" name="tipo' . $k . '" id="recoge' . $k . '" value="C" ' . $estatusC . '>
        <label class="form-check-label" for="recoge">Recoge cliente</label>
    </td>&nbsp;&nbsp;
    <td' . $colorfondoP . ' class="select-all' . $padre . '" id="td' . $k . ($i + 2) . '">
        <input class="form-check-input' . $clase . '" onclick="selectOne(this, ' . $k0 . ', ' . $k3 . ', ' . $pad . ', ' . $cob . ');" type="checkbox" name="tipo' . $k . '" id="pordefinir' . $k . '" value="P" ' . $estatusP . '>
        <label class="form-check-label" for="pordefinir">Por definir</label>
    </td>&nbsp;&nbsp;
    <td' . $colorfondoO . ' class="select-all' . $padre . '" id="td' . $k . ($i + 3) . '">
        <input class="form-check-input' . $clase . '" onclick="selectOne(this, ' . $k0 . ', ' . $k4 . ', ' . $pad . ', ' . $cob . ');" type="checkbox" name="tipo' . $k . '" id="ocurre' . $k . '" value="O" ' . $estatusO . ' ' . $reado2 . '>
        <label class="form-check-label" for="ocurre">Ocurre</label>    
    </td>
        ';
  $retorno .= '
          <td>
            <input style="font-size: 12px;" class="form-control" type="date" name="fechaEnvio' . $k . '" id="fechaEnvio' . $k . '" value="' . $fenvio . '" >
          </td>';

  return $retorno;
}


function imprimircheckboxesCombos($k, $cob, $reado, $reado2)
{
  /* $cob = ''; */
  return '
                <td class="sin-hover">
                    <input class="form-check-input" onclick="selectAll(this, ' . $k . ', 0, ' . $cob . ');" type="checkbox" name="tipoAll' . $k . '" id="domicilioAll' . $k . '" value="D" ' . $reado . '>
                    <label class="form-check-label" for="domicilioAll' . $k . '">Todos</label>
                </td>&nbsp;&nbsp;

                <td class="sin-hover">
                    <input class="form-check-input" onclick="selectAll(this, ' . $k . ', 1, ' . $cob . ');" type="checkbox" name="tipoAll' . $k . '" id="recogeAll' . $k . '" value="C">
                    <label class="form-check-label" for="recogeAll' . $k . '">Todos</label>
                </td>&nbsp;&nbsp;
                <td class="sin-hover">
                    <input class="form-check-input" onclick="selectAll(this, ' . $k . ', 2, ' . $cob . ');" type="checkbox" name="tipoAll' . $k . '" id="pordefinirAll' . $k . '" value="P">
                    <label class="form-check-label" for="pordefinirAll' . $k . '">Todos</label>
                </td>&nbsp;&nbsp;
                <td class="sin-hover">
                    <input class="form-check-input" onclick="selectAll(this, ' . $k . ', 3, ' . $cob . ');" type="checkbox" name="tipoAll' . $k . '" id="ocurreAll' . $k . '" value="O" ' . $reado2 . '>
                    <label class="form-check-label" for="ocurreAll' . $k . '">Todos</label>
                </td>
                <td colspan="2" style="width: 200px;" class="sin-hover">
                </td>';
}

$query0 = 'SELECT DISTINCT rc_remisiond AS rc_remisiond FROM remisionesc 
                  WHERE rc_remision = "' . $idremision . '" AND rc_ligado is NULL
                  ORDER BY rc_remisiond ASC;';
$result0 = setq($query0);
$k = 0;
$consultas = '';
$resp = 0;
while ($row0 = $result0->fetch_array()) {
  //Buscamos el aticulo dentro los detalles de la remisión para determinar posteriormente su tipo (Combo o no)
  $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, rd_modelo, rd_cantidad, rd_id, rd_articulo 
                    FROM remisionesd 
                    INNER JOIN articulos ON a_id = rd_articulo 
                    WHERE rd_id = "' . $row0['rc_remisiond'] . '"';
  $resultcmb = setq($sqlcmb);
  list($aid, $anmb, $tipoprod, $modelo, $cantidad, $cdmid, $cdmarticulo) = $resultcmb->fetch_array();

  if ($tipoprod == "M") {
    for ($j = 1; $j <= intval($cantidad); $j++) {

      $queryd = 'SELECT rc_articulo, rc_modelo, rc_id, rc_remision, rc_remisiond, 
                      rc_articulo, rc_modelo, rc_numero, rc_tipoenvio, rc_paqueteria, rc_sucursal, 
                      rc_combo, rc_estatus, rc_embarque, rc_ligado
                      FROM remisionesc
                      WHERE rc_remision = "' . $idremision . '" AND rc_remisiond = "' . $row0['rc_remisiond'] . '" AND rc_combo = "' . $j . '"';
                      /* $queryd .= ' GROUP BY rc_articulo, rc_modelo;'; */
      $resultd = setq($queryd);
      /* echo $queryd;
      echo '<br>';
      echo '<br>'; */

      if ($resultd->num_rows > 0) {
        echo '<tr style="background: #FFEE85 !important;" ><td class="sin-hover" colspan="2">PAQUETE - ' . $anmb . ' ' . $j . ' </td>' . imprimircheckboxesCombos($aid . $j, $cob, $reado, $reado2) . '</tr>';
      }

      $res = 0;
      while ($rowcb = $resultd->fetch_array()) {
        $var = intval(busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['rc_modelo'] . '" AND av_articulo', 'COUNT(*)'));
        $artname = busca($rowcb['rc_articulo'], "articulos", "a_id", "a_nmb");
        if ($var > 0) {
          $artname .= " " . busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['rc_modelo'] . '" AND av_articulo', 'av_nmb');
        }
        $artname .= " ".$rowcb['rc_numero'];

        $p1 = $rowcb['rc_id'];
        $p2 = $rowcb['rc_tipoenvio'];
        $peso = busca($rowcb['rc_articulo'], 'articulos', 'a_id', 'a_peso');
        $ligados = $rowcb['rc_ligado'];
        if (!empty($ligados)) {
          //Inicia listado de artículos ligados de este artículo que es un paquete con cantidad de uno
          $sqligados = 'SELECT rc_articulo, rc_modelo, rc_numero FROM remisionesc WHERE rc_id = "' . $ligados . '"';
          $resultligado = setq($sqligados);
          $rowligado = $resultligado->fetch_array();
          $artnameligado = busca($rowligado['rc_articulo'], "articulos", "a_id", "a_nmb");
          $var = intval(busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_amodelo'] . '" AND av_articulo', 'COUNT(*)'));
          if ($var > 0) {
            $artnameligado .= " " . busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'av_nmb');
          }
          $artnameg = $artname." DEL ".$artnameligado . " " . $rowligado['rc_numero'];
          $peso = busca($rowligado['rc_articulo'], 'articulos', 'a_id', 'a_peso');
          echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp; ** ' . $artnameg . ' </td><td>' . $peso . ' KG</td>' . imprimircheckboxes($p1, $p2, $cob, $reado, $reado2, $aid . $j) . ' ' . selectsucursal($p1, $p2, $cp, $cob, $aid . $j, $reado, $reado2) . '</tr>';
          $numarticulos++;

          // Finaliza listado de artículos ligados de este artículo que es un paquete con cantidad de uno
        } else{
          echo '<tr><td>&nbsp;&nbsp; * ' . $artname . '</td><td>' . $peso . ' KG</td>' . imprimircheckboxes($p1, $p2, $cob, $reado, $reado2, $aid . $j) . ' ' . selectsucursal($p1, $p2, $cp, $cob, $aid . $j, $reado, $reado2) . '</tr>';
          $numarticulos++;
        }
      }
      if ($resultd->num_rows > 0) {
        echo '<tr style="background: #FFEE85" ><td class="sin-hover" colspan="8"></td></tr>';
      }
    }
  } else {
    for ($i = 0; $i < intval($cantidad); $i++) {
      /* $artname = $anmb.' '.($i + 1); */
      $cdmarticulo2 = $cdmarticulo;
      $modelo2 = $modelo;
      $artname = $anmb;
      $var = intval(busca($cdmarticulo2, 'articulos_variantes', 'av_modelo = "' . $modelo2 . '" AND av_articulo', 'COUNT(*)'));

      if ($var > 0) {
        $artname .= " " . busca($cdmarticulo2, 'articulos_variantes', 'av_modelo = "' . $modelo2 . '" AND av_articulo', 'av_nmb');
      }
      $artname .= ' ' . ($i + 1);

      $p1 = busca($cdmarticulo2, "remisionesc", "rc_remision = '" . $idremision . "' AND rc_remisiond = '" . $cdmid . "' AND rc_modelo = '" . $modelo2 . "' AND rc_numero = '" . ($i + 1) . "' AND rc_articulo", "rc_id");
      $p2 = busca($cdmarticulo2, "remisionesc", "rc_remision = '" . $idremision . "' AND rc_remisiond = '" . $cdmid . "' AND rc_modelo = '" . $modelo2 . "' AND rc_numero = '" . ($i + 1) . "' AND rc_articulo", "rc_tipoenvio");
      $peso = busca($cdmarticulo2, 'articulos', 'a_id', 'a_peso');
      /* 
      $ligado = busca2($p1, 'remisionesc', 'rc_ligado', 'rc_id');
      echo $ligado;
      echo '<br>';
      echo '<br>'; */
      $ligado = busca($p1, 'remisionesc', 'rc_ligado', 'rc_id');
      $numero = busca($p1, 'remisionesc', 'rc_id', 'rc_numero');

      //Inicia listado de artículos ligados de este artículo que no es un paquete
      echo '<tr><td>' . $artname . '</td><td>' . $peso . ' KG</td>' . imprimircheckboxes($p1, $p2, $cob, $reado, $reado2) . ' ' . selectsucursal($p1, $p2, $cp, $cob, NULL, $reado, $reado2) . '</tr>';
      $numarticulos++;
      if (!empty($ligado)) {
        //Inicia listado de artículos ligados de este artículo que es un paquete con cantidad de uno
        $sqligados = 'SELECT rc_id, rc_tipoenvio, rc_articulo, rc_modelo, rc_numero FROM remisionesc WHERE rc_ligado = "' . $p1 . '"';
        $resultligado = setq($sqligados);
        while($rowligado = $resultligado->fetch_array()){
        $p12 = $rowligado['rc_id'];
        $p22 = $rowligado['rc_tipoenvio'];
        $artnameligado = busca($rowligado['rc_articulo'], "articulos", "a_id", "a_nmb");
        $var = intval(busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_amodelo'] . '" AND av_articulo', 'COUNT(*)'));
        if ($var > 0) {
          $artnameligado .= " " . busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'av_nmb');
        }
        $artnameg = $artnameligado . " " . $rowligado['rc_numero'] . " DEL " . $artname;
        $peso = busca($rowligado['rc_articulo'], 'articulos', 'a_id', 'a_peso');
        echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp; ** ' . $artnameg . ' </td><td>' . $peso . ' KG</td>' . imprimircheckboxes($p12, $p22, $cob, $reado, $reado2, $aid . $j) . ' ' . selectsucursal($p12, $p22, $cp, $cob, $aid . $j, $reado, $reado2) . '</tr>';
        $numarticulos++;
        }

        // Finaliza listado de artículos ligados de este artículo que es un paquete con cantidad de uno
      }
      // Finaliza listado de artículos ligados de este artículo que no es un paquete

      $k++;
      $resp = 0;
    }
  }
}
echo '</tbody>
      </table>
      <input type="hidden" id="cant" name ="cant" value="' . $k-- . '">
    </div>
  </center>
  </div>';
?>
<script>

  function checkIguales(k) {
    var checkboxes = document.querySelectorAll('.select' + k + ':checked');
    var val = "";
    var checkedValues = [];

    for (var i = 0; i < checkboxes.length; i++) {
      checkedValues.push(checkboxes[i].value);
      val = checkboxes[i].value;
    }

    var validacion = areAllValuesEqual(checkedValues);
    return { result: validacion, value: val };
  }

  function areAllValuesEqual(valuesArray) {
    for (var i = 1; i < valuesArray.length; i++) {
      if (valuesArray[i] !== valuesArray[0]) {
        return false;
      }
    }
    return true;
  }




  function checkSelect(k) {
    // Obtén todos los radio buttons con el mismo nombre
    var radioButtons = document.getElementsByName('tipoAll' + k);

    var valorSeleccionado;

    // Recorre los radio buttons para encontrar el seleccionado
    for (var i = 0; i < radioButtons.length; i++) {
      if (radioButtons[i].checked) {
        valorSeleccionado = radioButtons[i].value;
        break; // Detén el bucle una vez que encuentres el seleccionado
      }
    }

    // Si se encontró un radio button seleccionado, muestra su valor
    if (valorSeleccionado !== undefined) {
      return valorSeleccionado;
    } else {
      return false;
    }

  }

  function checkSelectID(k) {
    // Obtén todos los radio buttons con el mismo nombre
    var radioButtons = document.getElementsByName('tipoAll' + k);

    var idSeleccionado;

    // Recorre los radio buttons para encontrar el seleccionado
    for (var i = 0; i < radioButtons.length; i++) {
      if (radioButtons[i].checked) {
        idSeleccionado = radioButtons[i].id;
        break; // Detén el bucle una vez que encuentres el seleccionado
      }
    }

    // Si se encontró un radio button seleccionado, devuelve su id
    if (idSeleccionado !== undefined) {
      return idSeleccionado;
    } else {
      return false;
    }
  }


  function limpiarCheck(id, cob) {
    var limite = 4;
    
    for (var i = 0; i < limite; i++) {
      document.getElementById("td" + id + i).style.background = '';
    }

  }

  function selectOne(checkbox, k, td, padre) {   
    var select = document.getElementById('sucursalOcurre' + k);
    var paqueteria = document.getElementById('paqueteriaOcurre' + k);
    var paqueteriaDom = document.getElementById('paqueteriaDom' + k);
    /* var selecttd = document.getElementById('sucursaltd'+k);
    var paqueteriatd = document.getElementById('paqueteriatd'+k); */
    var checkdir = document.getElementById('checkdir').value;
    var form = document.getElementById('updcotiza');
    
    if(checkbox.value == "D" || checkbox.value == "E" || checkbox.value == "O") {
        if(checkdir == "2" && checkbox.value == "O"){
        //select.removeAttribute("required");
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se ha seleccionado una dirección de destino, configurela antes de continuar',
        }).then((result) => {
            if (result.isConfirmed || result.isDenied) {
                Swal.close();
                setdireccion(false);
            }
        });
      } else if((checkdir == "1" || checkdir == "2") && (checkbox.value == "D" || checkbox.value == "E")){
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se ha seleccionado una dirección de destino, configurela antes de continuar',
        }).then((result) => {
          if (result.isConfirmed || result.isDenied) {
              Swal.close();
              setdireccion(false);
          }
        });
      } else if(checkbox.value == "O"){
        select.hidden = false;
        //select.setAttribute("required", "required");
        //selecttd.hidden = false;
        paqueteria.hidden = false;
        paqueteriaDom.hidden = true;
        //paqueteriatd.hidden = true;
      }else{
        select.hidden = true;
        //select.removeAttribute("required");
        //selecttd.hidden = true;
        paqueteria.hidden = true;
        paqueteriaDom.hidden = false;
        //paqueteriatd.hidden = false;
      }
      
    } else {
      select.hidden = true;
      //selecttd.hidden = true;
      //select.removeAttribute("required");
      paqueteria.hidden = true;
      paqueteriaDom.hidden = true;
      //paqueteriatd.hidden = true;
    }

    limpiarCheck(k);
    var i = 0;
    var checkboxes = document.getElementsByName("tipo"+k);
    checkboxes.forEach(function(cb) {
        if (cb !== checkbox) {
            if(cb.checked){
              cb.checked = false;
              i++;
            }
        }
    });
    if(i == 0){
      checkbox.checked = true;
    }
    var tdElement = document.getElementById("td"+td);
    tdElement.style.background = '#c8c8c8';
    
    if(padre != false || padre != 0){
      var val = checkSelect(padre); //Value del checkbox padre del combo
      var valId = checkSelectID(padre); //ID del checkbox padre del combo
      if(checkbox.value != val && val != false){
        document.getElementById(valId).checked = false;
      }
      var resultObject = checkIguales(padre);

      if (resultObject.result) {          
          if(resultObject.value == "C"){
            document.getElementById("recogeAll"+padre).checked = true;
          } else if(resultObject.value == "D"){
            document.getElementById("domicilioAll"+padre).checked = true;
          } else if(resultObject.value == "E"){
            document.getElementById("especialAll"+padre).checked = true;
          } else {
            // Si es O            
            document.getElementById("ocurreAll"+padre).checked = true;
          }
      }
    }
  }

  function selectOneAll(checkbox, k) {
    var i = 0;
    var checkboxes = document.getElementsByName("tipoAll" + k);
    checkboxes.forEach(function (cb) {
      if (cb !== checkbox) {
        if (cb.checked) {
          cb.checked = false;
          i++;
        }
      }
    });
    if (i == 0) {
      checkbox.checked = true;
    }
  }

  function selectAll(idHTML, idArt, num, cob) {
    // Obtén todos los elementos de tipo checkbox dentro del contenedor con la clase
    const checkboxes = document.querySelectorAll('.select' + idArt);
    const selects = document.querySelectorAll('.sel' + idArt);
    const selects2 = document.querySelectorAll('.selP' + idArt);
    const numberOfCheckboxes = checkboxes.length;
    //console.log('selects: ', selects);
    if (idHTML.value != "O") {
      selects.forEach(select => {
        select.hidden = true;
      });
      if (idHTML.value == "C" || idHTML.value == "P") {
        selects2.forEach(select2 => {
          select2.hidden = true;
        });
      } else {
        selects2.forEach(select2 => {
          select2.hidden = false;
        });
      }
    } else {
      selects.forEach(select => {
        select.hidden = false;
      });
      selects2.forEach(select2 => {
        select2.hidden = true;
      });
    }
    selectOneAll(idHTML, idArt);
    var elemento = document.getElementById(idHTML.id).value;
    var p0 = 0;
    var p1 = 0;
    var p2 = 0;
    /* var p3 = 0; */

    if (num == 0) {
      marca = 0;
      p0 = 1;
      p1 = 2;
      p2 = 3;
      /* p3 = 4; */
    }
    if (num == 1) {
      marca = 1;
      p0 = 0;
      p1 = 2;
      p2 = 3;
      /* p3 = 4; */
    }
    if (num == 2) {
      marca = 2;
      p0 = 0;
      p1 = 1;
      p2 = 3;
      /* p3 = 4;   */
    }

    if (num == 3) {
      marca = 3;
      p0 = 0;
      p1 = 1;
      p2 = 2;
      /* p3 = 4;    */
    }

    if (num == 3) {
      marca = 3;
      p0 = 0;
      p1 = 1;
      p2 = 2;
      /* p3 = 4;   */
    }
    
    var i = 0;
    var j = 0;
    checkboxes.forEach(checkbox => {
      ind = checkbox.name.substring(4); // Empieza en el índice 4 y toma el resto del texto   
      var cant;
      cant = 3;
      if (i == cant) {
        document.getElementById("td" + ind + marca).style.background = '#c8c8c8';
        document.getElementById("td" + ind + p0).style.background = '';
        document.getElementById("td" + ind + p1).style.background = '';
        /* document.getElementById("td"+ind+p2).style.background = ''; */
        document.getElementById("td" + ind + p2).style.background = '';
        j++;
        i = 0;
      }

      if (checkbox.value === elemento) {
        checkbox.checked = true; // Marca el checkbox        
      } else {
        checkbox.checked = false; // Marca el checkbox
      }
      i++;
    });
  }
</script>

<?php

//TERMINA EL LISTADO DE PRODUCTOS
echo '
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
if ($flagcountry == 1) {
  $moneda = 'USD$';
  $tamanio = 6;
  $text = " (USD)";
} else {
  $moneda = 'MXN$';
  $tamanio = 12;
  $text = "";
}
echo '
  <div class="row">
  <div class="col-' . $tamanio . ' col-md-' . $tamanio . '">
      <h2><label for="precio">Costo extra del envío' . $text . '</label></h2>
      <div class="input-group mb-3">
          <div class="input-group-prepend">
              <span class="input-group-text">' . $moneda . '</span>
          </div>
          <input hidden type="text" name="id" id="id" value="' . $_GET['id'] . '"/>
          <input type="number" name="precio" min="'.$min.'" step="0.01" id="precio" class="form-control" placeholder="Costo del envío de la remisión" required="required" onfocus="this.select();" />
      </div>
  </div>';
if ($flagcountry == 1) {
  echo '
  <div class="col-6 col-md-6">
      <h2><label for="precio">Tipo de cambio</label></h2>
      <div class="input-group mb-3">
          <div class="input-group-prepend">
              <span class="input-group-text">MXN$</span>
          </div>
          <input type="number" name="tcambio" min="0" step="0.01" id="tcambio" class="form-control" placeholder="Tipo de cambio de la moneda" required="required" onfocus="this.select();" />
      </div>
  </div>';
}
echo '</div>';


echo '<div class="row"> 

      <div class="col-12 col-md-12 text-center mb-4">
        <button type="submit" class="btn btn-primary mr-2" id="guardar">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </form>
  
  <form action="?modulo=remisionessolicitud&accion=regresoaventas" id="formulario" method="post" hidden>
    <input type="text" value="' . $_GET['id'] . '" name="idremision">
    <input type="text" id="ccmotivo" name="ccmotivo">
  </form>


</div>';
?>
<script>
  var ccmotivo = document.getElementById("ccmotivo");
  var formulario = document.getElementById("formulario");
  async function motivoRegreso() {
    Swal.fire({
      title: 'Ingresa un texto',
      input: 'textarea',
      inputPlaceholder: 'Escribe aquí...',
      showCancelButton: true,
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Guardar',
      preConfirm: (texto) => {
        ccmotivo.value = texto;
        formulario.submit();
      }
    });
  }

  function paqueteriaOcurre(k) {
    var idPaqueteria = document.getElementById('paqueteriaOcurre' + k).value;
    var data = {
      idPaqueteria: idPaqueteria,
      cp: "<?php echo $cp; ?>"
    };
    $.ajax({
      url: 'query/selectsucursales.php',
      method: 'POST',
      dataType: 'json',
      data: data, // Los datos que quieres enviar
      success: function (data) {
        // La función que se ejecuta cuando la consulta AJAX es exitosa
        var select = $('#sucursalOcurre' + k);

        // Limpia las opciones actuales en el select
        select.empty();

        if (data.length === 0 || data === 1 || data === 2) {
          // Si no hay resultados o el valor es 1 o 2, agrega una opción "Sin resultados"
          select.append($('<option></option>')
            .attr('value', '')
            .text('SIN RESULTADOS'));
        } else {
          // Si hay resultados, llena el select con las opciones obtenidas de la consulta
          $.each(data, function (key, value) {
            select.append($('<option></option>')
              .attr('value', value.id)
              .text(value.nombre));
          });
        }
      }
    });
  }


</script>