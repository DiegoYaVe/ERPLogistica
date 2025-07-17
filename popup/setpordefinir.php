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
ini_set('display_errors', 0);
session_start();
include_once('../funciones.php');

$idremision = $_GET['id'];
if($_GET['tipo'] == "2"){
  $link = '?modulo=remisiones&accion=setprodpordefinir';
  $link2 = '?modulo=remisiones&accion=insertdireccion';
} else{
  $link = '?modulo=guias&accion=setprodpordefinir';
  $link2 = '?modulo=guias&accion=insertdireccion';
}

/* die($idremision); */
$sqlcc = 'SELECT * FROM remisiones WHERE r_id = "'.$idremision.'"';
/* $sql = 'SELECT * FROM remisionesd
              WHERE rd_remision = "'.$idremision.'" ORDER BY rd__id ASC';*/
$resultcc = setq($sqlcc);
$rowcc = $resultcc->fetch_array();
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
</style>

';
echo'
<div class="container mt-1 ">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary"><h2><span style="color: #5490c6;">Detalles remisión</span></h2><br>';
      $fini = $rowcc['r_fliquidacion'];  
      $almacen = busca($rowcc['r_almacen'], 'almacenes', 'a_id', 'a_nmb');
      /* $cliente = busca($rowcc['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb'); */

      $cliente = busca($rowcc['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');
      $cliente .= " ".busca($rowcc['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');

      $idcotiza = busca($idremision, 'crm_cotizaciones', 'cc_remision', 'cc_id');
      $direnvio = busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_direnvio');
      //if($_SESSION['uid'] == "ADMIN") busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_direnvio');
      if($direnvio == 0){
        $valord = 0;// No hay una direccion registrada
        $direccion = "NO CAPTURADA";
      } else{
        $valord = 1;// SI hay una direccion registrada
        $cp = busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_cp');
        $direccion = busca($direnvio, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
      }
      $telefono = busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_teldestino');
      echo '      
      <span>Folio: '.$rowcc['r_folio'].'</span><br>
      <span>Cliente: '.$cliente.'</span><br>';
      if(!empty($rowcc['r_observaciones'])){
        echo '<span>Descripción: '.$rowcc['r_observaciones'].'</span><br>';
      }
      echo'
      <span>Direccion: '.$direccion.'</span><br>
      <span>Teléfono: '.$telefono.'</span><br>
      <!-- <h4><span style="color: #5490c6;">Importe total: $'.$rowcc['r_importe'].'</span></h4><br> -->
      <input hidden type="text" id="valord" value="'.$valord.'">
      ';
      echo '</div>';


      //INICA FORMULARIO DE DIRECCIÓN
      /* if(empty($direnvio) || $direnvio == 0){ */
        echo '
    <div class="container mt-1 col-12">
    <form id="setcotiza" action="'.$link2.'" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">';
    echo '<div class="row nowrap">';    
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
        <div id="camposdir">
        <div class="row" id="selectdir">
          <div class="col-12 col-md-6">
            <div class="mb-5">
              <label for"cp">Seleccionar dirección</label>
              <select id="direcciones" name="direcciones" onchange="cambiardir();" class="form-control"  '.$disall.' '.$discheck.'>
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
              <input type="text" value="'.$cp.'" class="form-control" onchange="colonia();" id="cp" name="cp"  '.$disall.'>
              <div id="nameError" class="error-message"></div>
            </div>
          </div>
          <div class="col-12 col-md-5">
            <div class="mb-5">
              <label >Colonia</label>
              <select id="col" name="col" value="'.$colonia.'" class="form-control"  '.$disall.'>
                <option value="XXX" disabled selected>Seleccione la colonia</option>
              </select>
              <input type="hidden" value="'.$colonia.'" name="coldesc" id="coldesc">
            </div>
          </div> 
          <div class="col-12 col-md-4">
            <div class="mb-5">
              <label >Estado</label>
              <input type="text" value="'.$estado.'" class="form-control" id="estado" name="estado"  '.$disall.'>
              <input type="hidden" value="'.$estadoh.'" class="form-control" id="estadoh" name="estadoh" >
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="mb-5">
              <label >Municipio</label>
              <select id="municipio" value = "'.$municipio.'" name="municipio" class="form-control"  '.$disall.'>
                <option value="XXX" disabled selected>Seleccione el municipio</option>
              </select>
              <input type="hidden" value="'.$municipio.'" name="munidesc" id="munidesc">
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="mb-5">
              <label >Calle</label>
              <input type="text" value="'.$calle.'" class="form-control" id="calle" name="calle"  '.$disall.'>
            </div>
          </div>
          <div class="col-12 col-md-2">
            <div class="mb-5">
              <label >Número exterior</label>
              <input type="text" value="'.$nume.'" class="form-control" id="exterior" name="exterior"  '.$disall.'>
            </div>
          </div>
          <div class="col-12 col-md-2">
            <div class="mb-5">
              <label >Número interior</label>
              <input type="text" value="'.$numi.'" class="form-control" id="interior" name="interior" '.$disall.'>
            </div>
          </div>
        </div>
        </div>';
      echo '<div class="col-12 col-md-12" id="btnsave">
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
function guardarform(){
/*   var form = document.getElementById("setcotiza");
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
*/
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
        </script>
        <?php
                echo '
                <script>
                  setdireccion(true); //Quitamos el atributo hidden
                </script>
                ';
      /* } */
      //FINALIZA FORMULARIO DE DIRECCION

      //INICIA EL LISTADO DE PRODUCTOS
  echo '
  <form action="'.$link.'" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">
  <div class="row mt-5" style="background: white;">
  <div class="col-12 d-flex" style="justify-content: end !important;">
    <button type="button" class="btn btn-info" onclick="setdireccion(false);"><i class="fas fa-house-user"></i>Cambiar dirección</button>
  </div>
  <div class="card-body">
  <center>
    <div class="table-responsive col-12">
    <b><i><span id="totalreg" style="justify-content: left; display: flex; color: black;"></span></i></b><br>
        <table class="table table-hover" id="myTableG">
          <thead class="thead-active bg-primary text-white">
            <tr>
              <th width="8%">&nbsp;&nbsp;Remisión</th>
              <th width="13%">Cliente</th>
              <th width="23%">&nbsp;&nbsp;Producto</th>
              <th width="5%"></th>
              <th width="21%">Tipo de envio</th>
              <!-- <th width="8%"></th> -->
              <th width="15%">Guias</th>
              <th width="15%">Observaciones</th>
            </tr>
          </thead>
          <tbody>';
            $k=0;
            $cob = busca(busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_cp'), 'paqueterias_cobertura', 'pc_paqueteria = "1" AND pc_cp', 'pc_tipo');
            if($cob == "0") {
              $selo = "checked";
              $selesp = "";
            } else {
              $selo = "";
              $selesp = "checked";
            }
            $sql = 'SELECT * FROM remisionesd INNER JOIN articulos ON rd__articulo = a_id WHERE rd_remision = "'.$idcotiza.'"';
            $result = setq($sql);

            function imprimircheckboxes($k, $articulotipo, $idremision, $producto, $peso, $cp) {
              
              /* $color = ' style="background: #c8c8c8;"'; */                         
              $obs = busca($k, 'remisionesc', 'rc_id', 'rc_observacion');
              $folio = busca($idremision, 'remisiones', 'r_id', 'r_folio');
              $cliente = busca($idremision, 'remisiones INNER JOIN crm_clientes ON c_id = r_cliente', 'r_id', 'c_nmb');
              $cliente .= " ".busca($idremision, 'remisiones INNER JOIN crm_clientes ON c_id = r_cliente', 'r_id', 'c_apellidos');
              

              $check = !empty($background) ? ' checked' : '';
              $disa = !empty($background) ? ' disabled' : '';

              if(intval($peso) > 60){
                $parametros = $k.', '.$k.'2';
              } else{
                $parametros = $k;
              }
              
              $rtn .= 
                    '<td>
                      <span>'.$folio.'</span>
                    </td>
                    <td>
                      <span>'.$cliente.'</span>
                    </td>
                    ';
              $rtn .= $producto;
              $rtn .= '
                    <td>
                    <center>
                        <input class="form-check-input" onclick="selectOne('.$parametros.')" type="checkbox" name="select'.$k.'" id="select'.$k.'" '.$check.' '.$disa.'>
                    </center>
                    </td>     
                    ';
                  $rtn .= '
                  <td>  
                    <div>
                    <select class="form-control sel-dir" id="tipoenvio'.$k.'" name="tipoenvio'.$k.'" onchange="formaDeEnvio('.$k.');" disabled>';
                    $rtn .= '<option value="C">RECOGE CLIENTE</option>';
                    $sqldom = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_adomicilio = 1';
                    $resultdom = setq($sqldom);
                    while($rowdom = $resultdom -> fetch_array()){
                      $rtn .= '<option value="'.$rowdom['p_id'].'D">'.$rowdom['p_nmb'].' - DOMICILIO</option>';
                    }

                    $sqloc = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_ocurre = 1';
                    $resultoc = setq($sqloc);
                    while($rowoc = $resultoc -> fetch_array()){
                      $rtn .= '<option value="'.$rowoc['p_id'].'O">'.$rowoc['p_nmb'].' - OCURRE</option>';
                    }
                    
                    if($resultoc->num_rows < 1 && $resultdom->num_rows < 1){
                      $rtn .= '<option value="">SIN RESULTADOS</option>';
                    }

                    $rtn .= '
                    </select>   
                    </div>
                    </td>
                  <td>
                  <div style="padding-top: 5px;">
                  </select>    
                  <select class="form-control" id="paqueteria'.$k.'" name="paqueteria'.$k.'" onchange="paqueteriaOcurre('.$k.');" hidden>';
                  $sqlp = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_ocurre = 1';
                    $resultp = setq($sqlp);
                    if($resultp->num_rows < 1){
                      $rtn .= '<option value="">SIN RESULTADOS</option>';
                    } else{
                    while($rowp = $resultp -> fetch_array()){
                      $rtn .= '<option value="'.$rowp['p_id'].'">'.$rowp['p_nmb'].'</option>';
                    }
                    }
                  $rtn .= '
                  </select>
                  <!-- 
                  <select class="form-control" id="sucursal'.$k.'" name="sucursal'.$k.'" hidden>';
                  $sql = 'SELECT * FROM paqueterias_sucursales INNER JOIN paqueterias ON p_id = ps_paqueteria WHERE ps_plaza IN (SELECT DISTINCT(ps_plaza) FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT pc_sucursal FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre="1" AND p_estatus = "A" AND p_ocurre = 1;';
                  $result = setq($sql);
                  if($result->num_rows < 1){
                    $rtn .= '<option value="">SIN RESULTADOS</option>';
                  } else{
                  while($row = $result -> fetch_array()){
                    $rtn .= '<option value="'.$row['ps_id'].'">'.$row['ps_sucursal'].' - '.$row['ps_nmb'].'</option>';
                  }
                  }
                  $rtn .= '
                  </select>
                  -->';
                  $rtn .= '
                    </div>    
                    </td>
                    <td>
                    <textarea class="form-control" id="obs'.$k.'" name="obs'.$k.'" readonly></textarea>
                    </td>';
              return $rtn;
            }

            $query0 = 'SELECT DISTINCT rc_remisiond AS rc_remisiond FROM remisionesc 
                      WHERE rc_remision = "'.$idremision.'" 
                      ORDER BY rc_remisiond ASC;';
            $result0 = setq($query0);
            $k = 0;
            $numarticulos = 0;
            $resp = 0;
            echo '<input type="hidden" value="'.$idcotiza.'" name="idcotiza">';
            echo '<input type="hidden" value="'.$idremision.'" name="idremision">';
            while($row0 = $result0->fetch_array()){
              //Buscamos el aticulo dentro los detalles de la cotización para determinar posteriormente su tipo (Combo o no)
              $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, rd_cantidad, rd_modelo, rd_id, rd_articulo FROM remisionesd INNER JOIN articulos ON a_id = rd_articulo WHERE rd_id = "'.$row0['rc_remisiond'].'"'; 
              $resultcmb = setq($sqlcmb);
              list($aid, $anmb, $tipoprod, $cantidad, $modelo, $rd_id, $rd_articulo) = $resultcmb->fetch_array();
              /* $resp = 0;
              if($resp == 0){ */
              if($tipoprod == "M") {
              for($j = 1; $j <= intval($cantidad); $j++){

              /* $queryd = 'SELECT * FROM remisionesc WHERE rc_remision = "'.$idcotiza.'" AND rc_remisiond = "'.$row0['rc_remisiond'].'" AND rc_combo = "'.$j.'" AND rc_tipoenvio != "C" ORDER BY rc_paqueteria'; */
              $queryd = 'SELECT rc_articulo, rc_modelo, rc_id, rc_remision, rc_remisiond, 
                          rc_articulo, rc_modelo, rc_numero, rc_tipoenvio, rc_paqueteria, rc_sucursal, 
                          rc_combo, rc_estatus, rc_embarque
                          FROM remisionesc
                          WHERE rc_remision = "'.$idremision.'" AND rc_remisiond = "'.$row0['rc_remisiond'].'" AND rc_combo = "'.$j.'" AND rc_tipoenvio = "P"
                          GROUP BY rc_articulo, rc_modelo ORDER BY rc_paqueteria, rc_id';
              $resultd = setq($queryd);

              if($resultd->num_rows > 0){
                echo '<tr style="background: #FFEE85 !important;"><td colspan="8">&nbsp;&nbsp;PAQUETE - '.$anmb.' '.$j.' </td></tr>';
              }

              /* echo '<tr style="background: #FFEE85 !important;"><td colspan="7">&nbsp;&nbsp;PAQUETE - '.$anmb.' '.$j.' </td></tr>'; */
              /* echo '<tr style="background: #FFEE85" ><td colspan="2"></td></tr>';    */
              $res = 0;
              while($rowcb = $resultd->fetch_array()){
                $artname = busca($rowcb['rc_articulo'], "articulos", "a_id", "a_nmb");
                $var = busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['rc_modelo'].'" AND av_articulo', 'COUNT(*)');
                if($var > 0) {
                  $artname .= " ".busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['rc_modelo'].'" AND av_articulo', 'av_nmb');
                }
                
                $p1 = busca($rowcb['rc_articulo'], "remisionesc", "rc_remision = '".$idremision."' AND rc_remisiond = '".$rd_id."' AND rc_modelo = '".$rowcb['rc_modelo']."' AND rc_combo = '".$j."' AND rc_numero = '".$rowcb['rc_numero']."' AND rc_articulo", "rc_id");
                $p2 = busca($rowcb['rc_articulo'], "remisionesc", "rc_remision = '".$idremision."' AND rc_remisiond = '".$rd_id."' AND rc_modelo = '".$rowcb['rc_modelo']."' AND rc_combo = '".$j."' AND rc_numero = '".$rowcb['rc_numero']."' AND rc_articulo", "rc_tipoenvio");
                if($p2 == "P"){
                $status = busca($p1, 'remisionesc', 'rc_id', 'rc_estatus');
                
                $producto = '<td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' '.$h.' </td>';
                $peso = busca($rowcb['rc_articulo'], 'articulos', 'a_id', 'a_peso');
                echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2,$idremision, $producto, $peso, $cp) . '</tr>';
                $numarticulos++;
                  }  
              }
              if($resultd->num_rows > 0){
                echo '<tr style="background: #FFEE85" ><td colspan="8"></td></tr>';
              }
              }
              } else {
                for($i = 0; $i < intval($cantidad); $i++){
                  $artname = busca($rd_articulo, "articulos", "a_id", "a_nmb");
                  $var = busca($rd_articulo, 'articulos_variantes', 'av_modelo = "'.$modelo.'" AND av_articulo', 'COUNT(*)');
                  if($var > 0) {
                    $artname .= " ".busca($rd_articulo, 'articulos_variantes', 'av_modelo = "'.$modelo.'" AND av_articulo', 'av_nmb');
                  }
                  $p1 = busca($rd_articulo, "remisionesc", "rc_remision = '".$idremision."' AND rc_remisiond = '".$rd_id."' AND rc_modelo = '".$modelo."' AND rc_numero = '".($i + 1)."' AND rc_articulo", "rc_id");
                  $p2 = busca($rd_articulo, "remisionesc", "rc_remision = '".$idremision."' AND rc_remisiond = '".$rd_id."' AND rc_modelo = '".$modelo."' AND rc_numero = '".($i + 1)."' AND rc_articulo", "rc_tipoenvio");
                  if($p2 == "P"){
                    $status = busca($p1, 'remisionesc', 'rc_id', 'rc_estatus');
                  
                    $producto = '<td> * '.$artname." ".($i + 1).'</td>';
                    $peso = busca($rd_articulo, 'articulos', 'a_id', 'a_peso');
                    echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2,$idremision, $producto, $peso, $cp) . '</tr>';
                    $numarticulos++;
                    $k++;
                  }
  
                  $sqligados = 'SELECT rc_id, rc_tipoenvio, rc_articulo, rc_modelo, rc_numero FROM remisionesc WHERE rc_ligado = "'.$p1.'" AND rc_tipoenvio = "P"';        
                  $resultligado = setq($sqligados);
                  while($rowligado = $resultligado->fetch_array()){
                    $artnamevar = busca($rowligado['rc_articulo'], "articulos", "a_id", "a_nmb");
                    $var2 = intval(busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowligado['rc_modelo'].'" AND av_articulo', 'COUNT(*)'));
                    if($var2 > 0) {
                      $artnamevar .= " ".busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowligado['rc_modelo'].'" AND av_articulo', 'av_nmb');
                    }

                      $artnamevar .= " ".$rowligado['rc_numero']." DEL ".$artname." ".($i + 1);

                      $p1 = $rowligado['rc_id'];
                      $status = busca($p1, 'remisionesc', 'rc_id', 'rc_estatus');                
                      $producto = '<td> ** '.$artnamevar.'</td>';
                      $peso = busca($rowligado['rc_articulo'], 'articulos', 'a_id', 'a_peso');
                      echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2,$idremision, $producto, $peso, $cp) . '</tr>';
                      $numarticulos++;
                      $k++;
                    } 
                }
              }      
            }
            echo '
              <script>
              
                var totalreg = document.getElementById("totalreg");
                totalreg.textContent = "Número de artículos: '.$numarticulos.'";
              
              </script>
              ';
            /* die($consultas); */
            /* } */
          echo '</tbody>
        </table>
        <input type="hidden" id="cant" name ="cant" value="'.$k--.'">
      <!-- </form> -->
    </div>
  </center>
  </div>';
  ?>
    <script>
      function selectOne(k, j = ''){
        var checkbox = document.getElementById("select"+k);
        var paqueteria = document.getElementById("paqueteria"+k);
        var tipoenvio = document.getElementById("tipoenvio"+k);
        var sucursal = document.getElementById("sucursal"+k);
        var input = document.getElementById("in"+k);
        var obs = document.getElementById("obs"+k);

        if(checkbox.checked){
          paqueteria.removeAttribute("disabled");
          paqueteria.setAttribute("required", true);
          tipoenvio.removeAttribute("disabled");
          tipoenvio.setAttribute("required", true);
          /* sucursal.removeAttribute("disabled");
          sucursal.setAttribute("required", true); */
          obs.removeAttribute("readonly");
          //obs.setAttribute("required", true);
        } else{
          paqueteria.removeAttribute("required");
          paqueteria.setAttribute("disabled", true);

          tipoenvio.removeAttribute("required");
          tipoenvio.setAttribute("disabled", true);
          
          /* sucursal.removeAttribute("required");
          sucursal.setAttribute("disabled", true); */

          obs.setAttribute("readonly", true);
          obs.removeAttribute("required");
        }
      }

  function checkpaqueteria(k){
    var checkbox = document.getElementById("select" + k);
    if(checkbox.checked){
      document.getElementById("tr" + k).style.backgroundColor = '#ccffcc';
    } else {
      document.getElementById("tr" + k).style.backgroundColor = '#ffffff';
    }
  }

  function entregacliente(k) {
    // Obtenemos el botón por su ID
    var button = document.getElementById("btn" + k);
    if (button.classList.contains("btn-success")) {
      // Remueve una clase del botón
      button.classList.remove("btn-success");
      // Agrega una clase al botón
      button.classList.add("btn-primary");
      // Cambia el color de fondo
      document.getElementById("tr" + k).style.backgroundColor = '#ffffff';
      // Cambia el contenido del botón al icono
      button.innerHTML = '<i class="fas fa-check-circle"></i>&nbsp;Entregar a cliente';
    } else {
      // Remueve una clase del botón
      button.classList.remove("btn-primary");
      // Agrega una clase al botón
      button.classList.add("btn-success");
      // Cambia el color de fondo
      document.getElementById("tr" + k).style.backgroundColor = '#ccffcc';
      // Cambia el contenido del botón al icono
      button.innerHTML = '<i class="fas fa-check-circle"></i>';
    }

  }

  function selectTipo(k){
    console.log("K: "+k);
    var tipoEnvio = document.getElementById("tipoenvio"+k).value;
    var paqueteria = document.getElementById("paqueteria"+k);
    var sucursal = document.getElementById("sucursal"+k);
    var guia = document.getElementById("in"+k);
    var observaciones = document.getElementById("obs"+k);

    if(tipoEnvio == "C"){
      paqueteria.setAttribute("hidden", true);
      /* sucursal.setAttribute("hidden", true); */
      /* guia.setAttribute("readonly", true); */
      observaciones.setAttribute("readonly", true);
    } else if(tipoEnvio == "O"){
      /* paqueteria.setAttribute("hidden", true); */
      /* sucursal.removeAttribute("hidden"); */
      /* guia.removeAttribute("readonly"); */
      observaciones.removeAttribute("readonly");      
    } else{
      paqueteria.removeAttribute("hidden");
      /* sucursal.setAttribute("hidden", true); */
      /* guia.removeAttribute("readonly"); */
      observaciones.removeAttribute("readonly");
    }
   
  }

  function formaDeEnvio(k){
  var formadeenvio = document.getElementById("tipoenvio"+k).value;
  var paqueteriaO = document.getElementById("paqueteria"+k);
  var sucursal = document.getElementById("sucursal"+k);
  var tenvio = "";

  var direcciones = document.getElementById("direcciones");
  var cp = document.getElementById("cp");
  var coldesc = document.getElementById("coldesc");
  var estadoh = document.getElementById("estadoh");
  var municipio = document.getElementById("munidesc");
  var calle = document.getElementById("calle");
  var exterior = document.getElementById("exterior");
  var interior = document.getElementById("interior");
  var btnguardar = document.getElementById("guardar2");

  //Formulario de la dirección
  var formadeenvio2 = document.getElementById("tipoenvio"+k);

  /* var comentario =document.getElementById("paqueteria");
  var precio =document.getElementById("precio"); */
 if(formadeenvio == "C"){
    paqueteriaO.setAttribute("hidden", true);

    if(unoCheck("sel-dir", "D")){
      calle.setAttribute("required", true);
      exterior.setAttribute("required", true);
      tenvio = "D";
    } else if(unoCheck("sel-dir", "O")){
      if(unoCheck("sel-dir", "D")){
        calle.setAttribute("required", true);
        exterior.setAttribute("required", true);
        tenvio = "D";
      } else{
        calle.removeAttribute("required");
        exterior.removeAttribute("required");  
        tenvio = "O";
      }
    } else{
      calle.removeAttribute("required");
      exterior.removeAttribute("required");
    }

    if(todosCheck('sel-dir', 'C')){
      btnguardar.removeAttribute("disabled");
      setdireccion(true);
    }
    /* sucursal.setAttribute("hidden", true); */
  } else if(formadeenvio.includes("D") || formadeenvio.includes("O")){
    if(formadeenvio.includes("D")){        
        calle.setAttribute("required", true);
        exterior.setAttribute("required", true);
        tenvio = "D";
    } else{
      if(unoCheck("sel-dir", "D")){
        calle.setAttribute("required", true);
        exterior.setAttribute("required", true);
        tenvio = "D";
      } else{
        calle.removeAttribute("required");
        exterior.removeAttribute("required");  
        tenvio = "O";
      }
    }
    //Verificamos que el usuario tenga una dirección
    var data = {
      id: "<?php echo $_GET['id']; ?>",
      paqueteria: formadeenvio,
      tenvio: tenvio
    };  
    $.ajax({
    url: 'query/buscardireccion.php',
    dataType: 'json',
    method: 'POST',    
    data: data, // Los datos que quieres enviar
    success: function (response) {
      if(response.respuesta == 0){ 
        //Si tiene dirección registrada
        paqueteriaO.setAttribute("hidden", true);
        /* sucursal.setAttribute("hidden", true); */
      } else {
        if(response.respuesta == 1){
          mensaje = 'Dirección incompleta para entrega a domicilio. Complete sus datos';
          direcciones.value = response.direnvio;
          setdireccion(false);
        } else if(response.respuesta == 2){
          mensaje = 'No hay una dirección de envío registrada para la cotización. Para continuar debe registrar una.';
          direcciones.value = "YYY";
          setdireccion(false);
        } else if(response.respuesta == 3){
          mensaje = 'Código postal sin zona de cobertura para OCURRE';
          formadeenvio2.value = "C";
          setdireccion(false);
        } else{
          //Response == 4
          mensaje = 'El código postal está vacío. Debe ingresar uno para envío por ocurre.';
          direcciones.value = "YYY";
          setdireccion(false);
        }

        if(response.respuesta != 3){
          direcciones.setAttribute("required", true);
          cp.setAttribute("required", true);
          coldesc.setAttribute("required", true);
          estadoh.setAttribute("required", true);
          municipio.setAttribute("required", true);
          //calle.setAttribute("required", true);
          //exterior.setAttribute("required", true);
          /* interior.setAttribute("required", true); */
          btnguardar.setAttribute("disabled", true);
        }
        Swal.fire(
          'Atención',
          mensaje,
          'warning'
        )
      }
    }
  });
  }
}

function todosCheck(clase, valor){
var checkboxes = document.querySelectorAll('.'+clase);

var todosTienenC = true;

// Itera a través de los checkboxes
for (var i = 0; i < checkboxes.length; i++) {
  if (checkboxes[i].value !== valor) {
    todosTienenC = false;
    break; 
  }
}
  return todosTienenC;
}

function unoCheck(clase, valor){
  // Obtén todos los elementos checkbox con la clase "sel-dir"
  var checkboxes = document.querySelectorAll('.'+clase);

// Variable para rastrear si al menos uno tiene "D"
var alMenosUnoEs = false;

// Itera a través de los checkboxes
for (var i = 0; i < checkboxes.length; i++) {
  if(checkboxes[i].value.includes(valor)){
    alMenosUnoEs = true;
    break; // Detén la iteración si encuentras uno con "D"
  }
}

// Verifica el resultado
return alMenosUnoEs;
}

function paqueteriaOcurre(k) {
  var idPaqueteria = document.getElementById('paqueteria'+k).value;
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
      var select = $('#sucursal'+k);

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

    </script>

  <?php

//TERMINA EL LISTADO DE PRODUCTOS
      echo '
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
  echo '
      <div class="row">
      <h2><label for"precio">Costo del envío</label></h2>
      <div class="input-group mb-3 col-12 col-md-12">
        <div class="input-group-prepend">
          <span class="input-group-text">$</span>
        </div>
          <input hidden type="text" name="id" id="id" value="'.$_GET['id'].'"/>
          <input type="number" name="precio" min="0" id="precio" class="form-control" placeholder="Costo del envío de la cotización" required="required" onfocus="this.select();" />
      </div>

      </div>
      <div class="row">
      <div class="col-12 col-md-12">
        <div class="mb-5">
          <label for"comentario">Comentario</label>
          <textarea name="comentario" maxlength="100" id="comentario" class="form-control" placeholder="Agrega un comentario" onfocus="this.select();"></textarea>
        </div>
      </div>  


      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar2"><i class="fas fa-save"></i>Guardar</button></center>
      </div>
    </div>
  </form>
</div>';