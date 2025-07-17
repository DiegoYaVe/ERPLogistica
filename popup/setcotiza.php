<?php
ini_set('display_errors', 0);

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
include_once('../funciones.php');
//foreachdie();
$tablero = $_GET['tablero'];
include_once('../modulos/cotizaciones.php');
$mcotiza = new modelcotizaciones();
$mcotiza->select($_GET['idcotiza']);

echo '<script language="javascript">
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
  function desglose(){
    chtotal = document.getElementById("total");
    chiva = document.getElementById("iva");
    if(chtotal.checked){
      chiva.removeAttribute("disabled");
      chiva.removeAttribute("style");
      document.getElementById("descuento").value = 0;
      chiva.checked = false;
    }else {
      $("#iva").prop("disabled","true");
      $("#iva").prop("style","background:grey");
      chiva.checked = false;
      document.getElementById("descuento").value = 0;
    }
  }
</script>';

if(isset($_GET['idcotiza'])) $_GET['idcotiza'] = "0";
$diva = ' ';
$dtotal = ' checked="checked" ';
$reccliente = "";
$setdireccion = "";
$cltablero = busca($tablero,'crm_tableros','ct_id','ct_cliente');
$clagente = busca($tablero,'crm_tableros','ct_id','ct_agente');
if($mcotiza->id){
  $nmbcliente = $mcotiza->destino;
  $telcliente = $mcotiza->teldestino;
  $mailcliente = $mcotiza->maildestino;
  $ffin = $mcotiza->ffin;
  $nmbact = $mcotiza->nmb;
  $responsable = $mcotiza->responsable;
  $nmbresponsable = busca($mcotiza->responsable, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
  $descuento = $mcotiza->descuento;
  $impdescuento = $mcotiza->montodescuento;
  $observadir = $mcotiza->observadir;
  $direnvio = $mcotiza->direnvio;
  $almacen = $mcotiza->almacen;
  $estatus = $mcotiza->estatus;
  if($mcotiza->diva == 0) $diva = '  ';
  else $diva='checked';
  if($mcotiza->mtotal == 0) {
    $dtotal = '';
    $disablediva = 'disabled';
    $style = 'style="background:grey"';
  }
  if($mcotiza->envio == "C") $reccliente = 'checked';
  $accion = 'updatecotiza&idcotiza='.$mcotiza->id;
  $title = "Editar";
  $direccion = '';
  if($mcotiza->direnvio != "0") $setdireccion = "checked";
}else{
  $nmbcliente = busca($cltablero,'crm_clientes','c_id','c_nmb');
  $apcliente = busca($cltablero,'crm_clientes','c_id','c_apellidos');
  if($apcliente && !empty($apcliente)) $nmbcliente .= ' '.$apcliente;
  $telcliente = busca($cltablero,'crm_clientes','c_id','c_telefono2');
  $mailcliente = busca($cltablero,'crm_clientes','c_id','c_correo1');
  $mcotiza->cliente = $cltablero;
  $almacen = '1';
  $estatus = "N";
  $impdescuento = 0;
  $fecha = new DateTime();
  $fecha2 = new DateTime("+10 days");
  $mesfecha1 = $fecha->format("n");
  $mesfecha2 = $fecha2->format("n");
  if ($mesfecha1 == $mesfecha2) $ffin = date('Y-m-d', strtotime('+10 days'));
  else $ffin = date('Y-m-d', strtotime('last day of this month'));
  $nmbtab = busca($tablero,'crm_tableros','ct_id','ct_nmb');
  if(!isset($nmbact)) $nmbact = "Cotización de ".$nmbtab;

  $responsable = $clagente;
  $nmbresponsable = busca($clagente, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
  $accion = 'insertcotiza';
  $title = "Registrar";
  $descuento = 0;
  $direccion = 'hidden';
}
$grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
if($grupo != "ADMIN") {
  $sel = 'style="display: none;"';
  $inp = 'style="display: block;"';
}else {
  $sel = 'style="display: block;"';
  $inp = 'style="display: none;"';
}
if($estatus != "N" && $estatus != "D" && $estatus != "R" ) {$disall = "readonly"; $styledis = 'style="background: grey;"'; $discheck = "disabled";}
else {$disall = ""; $styledis = ""; $discheck = "";}
echo'
<style>
  .error-message {
    color: red;
  }
</style>
<div class="container mt-1 col-9">
  <form id="setcotiza" action="?modulo=cotizaciones&accion='.$accion.'&tablero='.$tablero.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="estatus" id="estatus" value = "'.$estatus.'">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' cotización</div>
      <div class="btn-group" data-toggle="buttons"></div>
    </div>';
    echo '<div class="row nowrap">
      <div class="col-12 col-md-3 ">
        <div class="mb-5">
          <label for"nmbac">Alias de tu cotización</label>
          <input type="text" name="nmbac" maxlength="50" value="'.$nmbact.'" id="nmbac" class="form-control" placeholder="Nombre de tu actividad" required="required" onfocus="this.select();"/>
        </div>
      </div>
      <div class="col-12 col-md-3" >
        <div class="mb-5">
          <label for"fini">Vigencia</label>
          <input type="date" name="ffin" id="ffin" value="'.$ffin.'" class="form-control" placeholder="Cuando programas tu actividad" required="required" readonly/>
        </div>
      </div>
      <div class="col-12 col-md-2" >
        <div class="mb-5">
          <label for"fini">Mostrar total</label>
          <div class="">
          <input type="checkbox" id="total" name="total" '.$dtotal.' class="flipswitch2" onchange="desglose();" />
          </div>
        </div>
      </div>
      <div class="col-12 col-md-2" >
        <div class="mb-5">
          <label for"fini">Desglosar IVA</label>
          <div class="">
            <input type="checkbox" id="iva" name="iva" '.$diva.' '.$disablediva.' '.$style.' class="flipswitch2"/>
          </div>  
        </div>
      </div>';
      if($estatus == "N" || $estatus == "R")
      echo '<div class="col-12 col-md-2" >
        <div class="mb-5">
          <label >Venta rápida</label>
          <div class="">
          <input type="checkbox" id="reccliente" name="reccliente" '.$reccliente.' class="flipswitch2"/>
          </div>
        </div>
      </div>';
      echo '<div class="col-6 col-md-2">
        <div class="mb-5">
          <label for"correo">Responsable</label>
          <select class="form-control" searchable="Responsable del tablero" name="responsable" '.$read.' '.$sel.'>';
            $sqlu = 'SELECT u_nuser,u_id,u_nmb FROM usuarios WHERE u_estatus = "A" AND u_nuser != "1"';
            $resultu = setq($sqlu);
            while($rowu = $resultu->fetch_array()){
              if($row['u_nuser'] != "1"){
                if($responsable == $rowu['u_id']) $seldis = "selected"; else $seldis = "";
                echo '<option value="'.$rowu['u_id'].'" '.$seldis.'>'.$rowu['u_nmb'].' '.$rowu['u_apellidos'].'</option>';
              }
            }
          echo '</select>
          <input name="responsable" class="form-control" type="text" value="'.$nmbresponsable.'" '.$inp.' disabled> 
        </div>
      </div>
      <div class="col-6 col-md-3 mt-2">
        <div class="mb-5">
          <label for"fini">Almacen</label>
          '.menu_select_db('almacenes','a_id','a_nmb',$almacen,'almacen','a_estatus = "A"',false,false,false,true,"SELECCIONA ALMACEN").'
        </div>
      </div>';
     /*  $gruposPermitidos = array("ADMIN", "GERENCIA", "SUBGERENCIA");
      if (in_array($grupo, $gruposPermitidos))
       {
        if($impdescuento != 0) {
          $hiddenpor = 'hidden';
          $hiddenmonto = '';
        }else {
          $hiddenpor = '';
          $hiddenmonto = 'hidden';
        } */
        $num = array();
        $k =0;
        $j=0;
        $l=0;
        $categoria = array();
        $sqlcat = 'SELECT cat_id FROM categorias WHERE cat_inflable = "1"';
        $resultcat = setq($sqlcat);
        while($rowcat = $resultcat -> fetch_array()){
          $categoria[$k] = $rowcat['cat_id'];
          $k++;
        }
        $sqlsel = 'SELECT * FROM crm_cotizacionesd INNER JOIN articulos ON a_id = cdm_articulo WHERE cdm_cotizacion = "'.$mcotiza->id.'"';
        $resultsel = setq($sqlsel);
        while($rowsel = $resultsel -> fetch_array()){
          if($rowsel['a_tipoprod'] == "M"){
            $sqlcant = 'SELECT SUM(ccv_cantidad), a_categoria FROM crm_cotizacion_variantes INNER JOIN articulos ON a_id = ccv_articulo WHERE ccv_cotizaciond = "'.$rowsel['cdm_id'].'" AND ccv_cotizacion = "'.$mcotiza->id.'" AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
            $resultcant = setq($sqlcant);
            list($cant, $catart) = $resultcant -> fetch_array();
            if(!$num[$catart]) $num[$catart] = 0;
            $num[$catart] += ($cant*$rowsel['cdm_cantidad']);
            $l+= ($cant*$rowsel['cdm_cantidad']);
          } else {
            if(in_array($rowsel['a_categoria'], $categoria)){
              if(!$num[$rowsel['a_categoria']]) $num[$rowsel['a_categoria']] = 0;
              $num[$rowsel['a_categoria']] += $rowsel['cdm_cantidad'];
              $l+= $rowsel['cdm_cantidad'];
            }
          }
        }
        if($estatus != "V") $hiddenpor = "";
        else $hiddenpor = "hidden";
        echo '<div class="col-6 col-md-3 mt-2" id="porcentajediv" '.$hiddenpor.'>
          <div class="mb-5">
            <label for"fini">%Descuento (Porcentaje)</label>
            <select name="descuento" class="form-control" required>
              <option value="0" selected>Selecciona el descuento</option>';
              if(count($num)>0){
                $sql = 'SELECT DISTINCT(d_descuento) FROM descuentos WHERE d_estatus = "1"'; 
                $sql.=' AND (';
                foreach($num AS $cat => $valor){
                  $j++;
                  $sql.='(d_categoria = "'.$cat.'" AND d_numeroart <= "'.$valor.'")';
                  if($j<count($num)){
                    $sql.=' OR ';
                  }
                }
                $sql.=') ORDER BY d_descuento ASC';
                $result = setq($sql);
                while($row = $result -> fetch_array()){
                  if($row['d_descuento'] == $descuento) $sel = "selected";
                  else $sel = "";
                  echo '<option value="'.$row['d_descuento'].'" '.$sel.'>'.$row['d_descuento'].' %</option>';
                }
              }

              $sqldst = 'SELECT d_descuento FROM descuentos  WHERE d_estatus = "1" AND d_categoria = "T" AND d_numeroart <= "'.$l.'"';
              $resultdst = setq($sqldst);
              while($rowdst = $resultdst -> fetch_array()){
                if($rowdst['d_descuento'] == $descuento) $sel = "selected";
                  else $sel = "";
                  echo '<option value="'.$rowdst['d_descuento'].'" '.$sel.'>'.$rowdst['d_descuento'].' %</option>';
              }
            echo '</select>
            <!-- <input type="number" name="descuento" min="0" max="100" step="1" value="'.$descuento.'" id="descuento" class="form-control" placeholder="Porcentaje de descuento" required="required" onfocus="this.select();" data-toggle="tooltip" data-placement="right" title="Expresar descuento en porcentaje" '.$blockdes.' /> -->
          </div>
        </div>';
        /* echo '<div class="col-6 col-md-3 mt-2 " id="montodiv" '.$hiddenmonto.'>
          <div class="mb-5">
            <label for"fini">$ Descuento (Monto)</label>
            <input type="number" name="impdescuento" min="0" step="1" value="'.$impdescuento.'" id="impdescuento" class="form-control" placeholder="Monto de descuento" required="required" onfocus="this.select();" data-toggle="tooltip" data-placement="right" title="Expresar descuento en cantidad" />
          </div>
        </div>'; */
        /* echo '<div class="col-auto mt-8" >
        <div class="mb-5">
          <button type="button" name="btnporcentaje"  id="btnporcentaje" onclick="cambiardescuento(0);" class="btn btn-primary" data-toggle="tooltip" data-placement="right" title="Ingresar descuento por porcentaje" '.$hiddenmonto.'> <i class="fas fa-percent"></i> Descuento en porcentaje</>
          <button type="button"name="btnmonto"  id="btnmonto" class="btn btn-primary" onclick="cambiardescuento(1);" data-toggle="tooltip" data-placement="right" title="Ingresar descuento por monto" '.$hiddenpor.'><i class="fas fa-calculator" ></i> Descuento en monto</>
        </div>
      </div>'; */
        
      //}
      echo '
      <div class="col-12 col-md-12">
        <div class="mb-5">
          <label for"correo">Descripción de la cotización</label>
          <textarea class="form-control" name="descripcion" rows="2" >'.$descripcion.'</textarea> 
        </div>
      </div>
      <div class="col-12 col-md-12 alert alert-primary">Receptor de la cotización</div>
      <div class="col-12 col-md-4">
        <div class="mb-5">
          <label for"destino">Dirigido a</label>
          <input type="text" name="destino" id="destino" value="'.$nmbcliente.'" class="form-control" placeholder="Persona para contactar"  />
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="mb-5">
          <label for"telefono">Número telefónico</label>
          <input type="tel" name="telefono" id="telefono" value="'.$telcliente.'" class="form-control" placeholder="Telefóno del contacto" />
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="mb-5">
          <label for"correo">Correo destino</label>
          <input type="email" style="text-transform:lowercase;" name="correo" id="correo" value="'.$mailcliente.'" class="form-control" placeholder="correo de la contacto" />
        </div>
      </div>';
      
      echo '<div class="row col-12 col-md-12 alert alert-primary">
        Seleccionar dirección de envío
        <div class="col-1" >
          <input id="setdir" name="setdir" type="checkbox" class="flipswitch2" style="height: 30px !important; font-size: 12px !important;"  onclick="setdireccion();" '.$setdireccion.' '.$disall.' '.$styledis.' >
        </div>
      </div>';
        $dir = busca($cltablero, 'crm_direcciones', 'cd_cliente', 'COUNT(*)');
        if($dir != 0){
          $seldir = '';
          $formdir = "hidden";
        } else {
          $seldir = 'selected';
          $formdir = "";
        }
        echo '
        <div id="camposdir" hidden>
        <div class="row" id="selectdir">
          <div class="col-12 col-md-6">
            <div class="mb-5">
              <label for"cp">Seleccionar dirección</label>
              <select id="direcciones" name="direcciones" onchange="cambiardir();" class="form-control"  '.$disall.' '.$discheck.'>
                <option value="YYY" disabled> Seleccionar una dirección </option>';
                $i = 0;
                $sqldir = 'SELECT * FROM crm_direcciones WHERE cd_cliente = "'.$cltablero.'"';
                $result = setq($sqldir);
                while($row = $result -> fetch_array()){
                  $pais = busca($row['cd_pais'], 'paises', 'p_id', 'p_nmb');
                  if($mcotiza->direnvio == $row['cd_id'] || $row['cd_predeterminada'] == "1") {
                    $seld = 'selected';
                  }else{
                    $seld = '';
                  }
                  echo '<option value="'.$row['cd_id'].'" '.$seld.' >'.strtoupper($pais).' '.$row['cd_calle'].' '.$row['cd_nume'].' '.$row['cd_numi'].' '.$row['cd_colonia'].' '.$row['cd_municipio'].' '.busca($row['cd_estado'], 'estado', 'e_id', 'e_nmb').' '.$row['cd_cp'].'</option>';
                }
              echo '
                <option value="XXX" '.$seldir.'> Nueva dirección</option>
              </select> 
            </div>
          </div>';
          if($mcotiza->direnvio){
            $sqlget = 'SELECT cd_cp, cd_colonia, cd_municipio, cd_estado, cd_calle, cd_nume, cd_numi, cd_pais, cd_recibe FROM crm_direcciones WHERE cd_id ="'.$mcotiza->direnvio.'"';
            $resultget = setq($sqlget);
            list($cp, $colonia, $municipio, $estadoh, $calle, $nume, $numi, $pais, $recibe) = $resultget->fetch_array();
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
            $recibe= $nmbcliente;
            $pais = "146";
          }
          if($pais == "146"){
            $hiddatos = "";
            $hiddetalle = "hidden";
          } else {
            $hiddatos = "hidden";
            $hiddetalle = "";
          }                
          echo '<div class="col-6 col-md-6">
            <div class="mb-5">
              <label for"correo">Observaciones de envío</label>
              <input type="text" name="observadir" maxlenght="100" id="observadir" value="'.$observadir.'" class="form-control" placeholder="Indicaciones" />
            </div>
          </div>
        </div>
        ';
        echo '<div class="row" id="formdir">
          <input type="hidden" id="countdir">
          <div id="direccionmex" class="row" '.$hiddatos.'>
          <div class="col-12 col-md-3">
            <div class="mb-5" >
              <label >Código postal</label>
              <input type="text" value="'.$cp.'" class="form-control" onchange="colonia();" id="cp" name="cp"  '.$disall.'>
              <div id="nameError" class="error-message"></div>
            </div>
          </div>
          <div class="col-12 col-md-5" >
            <div class="mb-5">
              <label >Colonia</label>
              <select id="col" name="col" value="'.$colonia.'" class="form-control"  '.$disall.'>
                <option value="XXX" disabled selected>Seleccione la colonia</option>
              </select>
              <input type="hidden" value="'.$colonia.'" name="coldesc" id="coldesc">
            </div>
          </div> 
          <div class="col-12 col-md-4" >
            <div class="mb-5">
              <label >Estado</label>
              <input type="text" value="'.$estado.'" class="form-control" id="estado" name="estado"  '.$disall.'>
              <input type="hidden" value="'.$estadoh.'" class="form-control" id="estadoh" name="estadoh" >
            </div>
          </div>
          <div class="col-12 col-md-4" >
            <div class="mb-5">
              <label >Municipio</label>
              <select id="municipio" value = "'.$municipio.'" name="municipio" class="form-control"  '.$disall.'>
                <option value="XXX" disabled selected>Seleccione el municipio</option>
              </select>
              <input type="hidden" value="'.$municipio.'" name="munidesc" id="munidesc">
            </div>
          </div>
          <div class="col-12 col-md-4" >
            <div class="mb-5">
              <label >Calle</label>
              <input type="text" value="'.$calle.'" class="form-control" id="calle" name="calle"  '.$disall.'>
            </div>
          </div>
          <div class="col-12 col-md-2" >
            <div class="mb-5">
              <label >Número exterior</label>
              <input type="text" value="'.$nume.'" class="form-control" id="exterior" name="exterior"  '.$disall.'>
            </div>
          </div>
          <div class="col-12 col-md-2" >
            <div class="mb-5">
              <label >Número interior</label>
              <input type="text" value="'.$numi.'" class="form-control" id="interior" name="interior" '.$disall.'>
            </div>
          </div>
          </div>';
          echo '<div class="col-12 col-md-4">
            <div class="mb-5">
              <label >Pais</label>
              <select class="form-control" id="pais" name="pais" onchange="setpais();" '.$disall.'>';
                $sql = 'SELECT * FROM paises';
                $result = setq($sql);
                while($row = $result -> fetch_array()){
                  if($row['p_id'] == $pais) $sel = "selected";
                  else $sel = "";
                  echo '<option value="'.$row['p_id'].'" '.$sel.'> '.$row['p_nmb'].' </option>';
                } 
              echo '</select>
            </div>
          </div>';
          echo '<div class="col-12 col-md-8" id="detallediv" '.$hiddetalle.'>
            <div class="mb-5">
              <label>Referencias de la dirección</label>
              <textarea placeholder="Detalles de la ubicacion (entre calles..., etc)" class="form-control" id="direccionint" name="direccionint" '.$disall.'>'.$calle.'</textarea>
            </div>
          </div>';
          echo '<div class="col-12 col-md-6" id="recibediv">
            <div class="mb-5">
              <label>Recibe el pedido</label>
              <input type="text" placeholder="Encargado de recoger el pedido" value="'.$recibe.'" class="form-control" id="recibe" name="recibe" '.$disall.'>
            </div>
          </div>';
        echo '</div>
        </div>';
      echo '<div class="col-12 col-md-12">
        <center><button type="button" onclick="guardarform();" class="btn btn-primary" id="guardar" ><i class="fa fa-save"></i> Guardar</button></center>
      </div>
    </div>
  </form>
</div>'; 
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
  var pais = document.getElementById("pais");
  var direccionint = document.getElementById("direccionint");
  
  pais.value="146";
  setpais();
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
        pais.value = datax['pais'];
        if(datax['pais'] != "146"){
          direccionint.value = datax['calle'];
          setpais();
        } else {
          munidesc.value = datax['municipio'];
          coldesc.value = datax['colonia'];
          cp.value = datax['cp'];
          calle.value = datax['calle'];
          numi.value = datax['numi'];
          nume.value = datax['nume'];
          colonia();
        }
      }
    });
  }
}  
function guardarform(){
  var form = document.getElementById("setcotiza");
  var elements = form.elements;

  for (var i = 0; i < elements.length; i++) {
    if (!elements[i].checkValidity()) {
      console.log('elemento', elements[i]);
      alert("Error al capturar sus datos, verifique su información para continuar.");
      elements[i].focus();
      event.preventDefault(); 
      return;
    }
  }

  form.submit();

}
function setdireccion(){

  var check = document.getElementById('setdir');
  var campos = document.getElementById('camposdir');
  var seldir = document.getElementById("direcciones");
  var cp = document.getElementById("cp");
  if(check.checked){
    campos.hidden = false;
    cp.setAttribute("required", "required");
    cambiardir();
  } else {
    campos.hidden = true;
    cp.removeAttribute("required");
  }
  setpais();
}

function setpais(){
  var pais = document.getElementById("pais");
  var detalle =document.getElementById("detallediv");
  var direccionmex =document.getElementById("direccionmex");
  var cp =document.getElementById("cp");
  var detdir = document.getElementById("direccionint");
  var check = document.getElementById('setdir');
  if(check.checked){
    if(pais.value == "146"){
      cp.setAttribute('required', 'required');
      detdir.removeAttribute('required');
      detalle.hidden = true;
      direccionmex.hidden = false;
    } else {
      detalle.hidden = false;
      detdir.setAttribute('required', 'required');
      cp.removeAttribute('required');
      direccionmex.hidden = true;
    }
  }
}

setdireccion();
</script>