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

$idcotiza = $_GET['id'];
/* die($idcotiza); */

$sqlcc = 'SELECT * FROM crm_cotizaciones WHERE cc_id = "'.$idcotiza.'"';
/* $sql = 'SELECT * FROM crm_cotizacionesd
              WHERE cdm_cotizacion = "'.$idcotiza.'" ORDER BY cdm_id ASC';*/
$resultcc = setq($sqlcc);
$rowcc = $resultcc->fetch_array();

$cp = busca($rowcc['cc_direnvio'], 'crm_direcciones', 'cd_id', 'cd_cp');
$cob = busca($cp, 'paqueterias_cobertura', 'pc_paqueteria = "1" AND pc_cp', 'pc_tipo');
$sqldom = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_adomicilio = 1';
$resultdom = setq($sqldom);
if($resultdom->num_rows < 1){
  $reado = ' disabled';
} else{
  $reado = '';
}

$sqlo2 = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_ocurre = 1';
$resulto2 = setq($sqlo2);
if($resulto2->num_rows < 1){
  $reado2 = ' disabled';
} else{
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
echo'
<div class="container mt-1 ">
  <form action="?modulo=cotizacionessolicitud&accion=setcotiza" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary"><h2><span style="color: #5490c6;">Detalles cotización</span></h2><br>';
      echo '<div class="row">';
      echo '<div class="col-8">';

      echo '      
      <span>Folio: '.$rowcc['cc_folio'].'</span><br>
      <span>Cliente: '.$rowcc['cc_destino'].'</span><br>';
      if(!empty($rowcc['cc_descripcion'])){
        echo '<span>Descripción: '.$rowcc['cc_descripcion'].'</span><br>';
      }
      echo'
      <span>Direccion: '.busca($rowcc['cc_direnvio'], 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)').'</span><br>
      <span>Teléfono: '.$rowcc['cc_teldestino'].'</span><br>
      <!-- <h4><span style="color: #5490c6;">Importe total: $'.$rowcc['cc_importe'].'</span></h4><br> -->
      ';
      echo '</div>';
      echo '<div class="col-4" style="vertical-align: top;">      
      <div>
      <h2><span style="color: #5490c6;">Importe total</span></h2><br>
      <h4><span style="color: #5490c6;">$'.number_format($rowcc['cc_importe'], 2).'</span></h4><br>
      </div>
      </div>
      </div>
      </div>';

      echo '
      <div class="row">
        <div class="col-12" style="display: flex; justify-content: end;">
          <b><i><span style="justify-content: left; display: flex; color: black;">* Artículo perteneciente a un paquete &nbsp;&nbsp;&nbsp;&nbsp; ** Artículo que es complemento</span></i></b><br>
        </div>
      </div>
      ';

      //INICIA EL LISTADO DE PRODUCTOS
  echo '<div class="row" style="background: white;">
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
        $k=0;
        $cob = busca(busca($rowcc['cc_direnvio'], 'crm_direcciones', 'cd_id', 'cd_cp'), 'paqueterias_cobertura', 'pc_paqueteria = "1" AND pc_cp', 'pc_tipo');
        if($cob == "0") {
          $selo = "checked";
          $selesp = "";
        } else {
          $selo = "";
          $selesp = "checked";
        }
        $sql = 'SELECT * FROM crm_cotizacionesd INNER JOIN articulos ON cdm_articulo = a_id WHERE cdm_cotizacion = "'.$idcotiza.'"';
        $result = setq($sql);

        function selectsucursal($k, $articulotipo, $cp, $cob, $padre, $reado, $reado2){
                
          if($padre == NULL){
            $clase = '';
            $clase2 = '';
          } else{
            $clase = ' sel'.$padre;
            $clase2 = ' selP'.$padre;
          }

          if($articulotipo == "O" ){
            $hid2 = 'hidden';
            $hid = '';
            if(!empty($reado2)){
              $hid = 'hidden';
            }
          } else {
            
            if($articulotipo == "P" || $articulotipo == "C"){
              $hid2 = 'hidden';
            } else{
              $hid2 = '';
            }
            $hid = 'hidden';
            if(!empty($reado)){
              $hid2 = 'hidden';
            }
            
          }


          $paq = busca($k, 'crm_cotizacionesc', 'ccc_id', 'ccc_paqueteria');
          $sucu = busca($k, 'crm_cotizacionesc', 'ccc_id', 'ccc_sucursal');
          $indicador = '';
          $indicadorS = '';

          $selectdom = 
          ' <select style="font-size: 12px;" class="form-control '.$clase2.'"  name="paqueteriaDom'.$k.'" id="paqueteriaDom'.$k.'" '.$hid2.'>';
          /* $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT DISTINCT(pc_sucursal) FROM paqueterias_cobertura WHERE pc_plaza IN (SELECT pc_plaza FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre = "1"'; */
          $sqldom = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_adomicilio = 1';
          $resultdom = setq($sqldom);
          if($resultdom->num_rows < 1){
            $selectdom .='<option value="">SIN RESULTADOS</option>';
          } else{
          while($rowdom = $resultdom -> fetch_array()){
            if($paq == $rowdom['p_id']){
              $indicador = ' selected';
            } else{
              $indicador = '';
            }
            $selectdom .='<option value="'.$rowdom['p_id'].'" '.$indicador.'>'.$rowdom['p_nmb'].'</option>';
          }
          }
          $selectdom .= '</select>';
          
          $k0 = "'".$k."'";
          $selectp = 
          ' <select style="font-size: 12px;" class="form-control '.$clase.'" onchange="paqueteriaOcurre('.$k0.');" name="paqueteriaOcurre'.$k.'" id="paqueteriaOcurre'.$k.'" '.$hid.'>';
          /* $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT DISTINCT(pc_sucursal) FROM paqueterias_cobertura WHERE pc_plaza IN (SELECT pc_plaza FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre = "1"'; */
          $sqlp = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_ocurre = 1';
          $resultp = setq($sqlp);
          if($resultp->num_rows < 1){
            $selectp .='<option value="">SIN RESULTADOS</option>';
          } else{
          while($rowp = $resultp -> fetch_array()){
            if($paq == $rowp['p_id']){
              $indicador = ' selected';
            } else{
              $indicador = '';
            }
            $selectp .='<option value="'.$rowp['p_id'].'" '.$indicador.'>'.$rowp['p_nmb'].'</option>';
          }
          }
          $selectp .= '</select>';

          $select = 
            ' <td style="width: 200px;">';
          if($cob == "0"){
            $select .= $selectp;
            $select .= '
              <select style="font-size: 12px;" class="form-control '.$clase.'"  name="sucursalOcurre'.$k.'" id="sucursalOcurre'.$k.'" '.$hid.'>';
              /* $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT DISTINCT(pc_sucursal) FROM paqueterias_cobertura WHERE pc_plaza IN (SELECT pc_plaza FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre = "1"'; */
              $sql = 'SELECT * FROM paqueterias_sucursales INNER JOIN paqueterias ON p_id = ps_paqueteria WHERE ps_plaza IN (SELECT DISTINCT(ps_plaza) FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT pc_sucursal FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre="1" AND p_estatus = "A" AND p_ocurre = 1;';
              $result = setq($sql);
              if($result->num_rows < 1){
                $select .='<option value="">SIN RESULTADOS</option>';
              } else{
              while($row = $result -> fetch_array()){
                if($sucu == $row['ps_id']){
                  $indicadorS = ' selected';
                } else{
                  $indicadorS = '';
                }
                $select .='<option value="'.$row['ps_id'].'" '.$indicadorS.'>'.$row['ps_sucursal'].' - '.$row['ps_nmb'].'</option>';
              }
              }
              $select .= '</select>';
          } else {
            $select .= "";
          }
          $select .= $selectdom;
          $select .= '</td>
          ';

          /* $select = ""; */
          
          return $select;
        }

        function imprimircheckboxes($k, $articulotipo, $cob, $reado, $reado2, $padre = NULL) {
          /* $cob = ''; */
          if($padre == NULL){
            $clase = '';
            $pad = 0;
          } else{
            $clase = ' select'.$padre;
            $pad = $padre;
          }

          $color = ' style="background: #c8c8c8;"';
          if($articulotipo == "D" ){
            $estatusD = 'checked';
            $colorfondoD = $color;
          } else {
            $colorfondoD = '';
            $estatusD = ''; 
          } 
          if($articulotipo == "E" ){
            $estatusE = 'checked';
            $colorfondoE = $color;
          } else {
            $estatusE = ''; 
            $colorfondoE = '';
          }
          if($articulotipo == "C" ){
            $estatusC = 'checked';
            $colorfondoC = $color;
          } else {
            $estatusC = ''; 
            $colorfondoC = '';
          }
          if($articulotipo == "O" ){
            $estatusO = 'checked';
            $colorfondoO = $color;
          } else {
            $estatusO = ''; 
            $colorfondoO = '';
          }
          if($articulotipo == "P" ){
            $estatusP = 'checked';
            $colorfondoP = $color;
          } else {
            $estatusP = ''; 
            $colorfondoP = '';
          }

          if(!empty($reado) && $articulotipo == "D"){
            $estatusP = 'checked';
            $colorfondoP = $color;
            $colorfondoD = '';
            $estatusD = ''; 
          }

          if(!empty($reado2) && $articulotipo == "O"){
            $estatusP = 'checked';
            $colorfondoP = $color;
            $estatusO = ''; 
            $colorfondoO = '';
          }

          $fenvio = busca($k, 'crm_cotizacionesc', 'ccc_id', 'ccc_fenvio');
          $i = 0;
          $k1 = "'".$k.$i."'";
          $k2 = "'".$k.($i + 1)."'";
          $k3 = "'".$k.($i + 2)."'";
          $k4 = "'".$k.($i + 3)."'";
          $k0 = "'".$k."'";
          $pad = "'".$pad."'";
          $retorno = '
          <td'.$colorfondoD.' class="select-all'.$padre.'" id="td'.$k.$i.'">
              <input class="form-check-input'.$clase.'" onclick="selectOne(this, '.$k0.', '.$k1.', '.$pad.', '.$cob.');" type="checkbox" name="tipo'.$k.'" id="domicilio'.$k.'" value="D" '.$estatusD.' '.$reado.'>
              <label class="form-check-label" for="domicilio">A domicilio</label>
          </td>&nbsp;&nbsp;
          <td'.$colorfondoC.' class="select-all'.$padre.'" id="td'.$k.($i + 1).'">
              <input class="form-check-input'.$clase.'" onclick="selectOne(this, '.$k0.', '.$k2.', '.$pad.', '.$cob.');" type="checkbox" name="tipo'.$k.'" id="recoge'.$k.'" value="C" '.$estatusC.'>
              <label class="form-check-label" for="recoge">Recoge cliente</label>
          </td>&nbsp;&nbsp;
          <td'.$colorfondoP.' class="select-all'.$padre.'" id="td'.$k.($i + 2).'">
              <input class="form-check-input'.$clase.'" onclick="selectOne(this, '.$k0.', '.$k3.', '.$pad.', '.$cob.');" type="checkbox" name="tipo'.$k.'" id="pordefinir'.$k.'" value="P" '.$estatusP.'>
              <label class="form-check-label" for="pordefinir">Por definir</label>
          </td>&nbsp;&nbsp;'
          .($cob == "0" ? '
          <td'.$colorfondoO.' class="select-all'.$padre.'" id="td'.$k.($i + 3).'">
              <input class="form-check-input'.$clase.'" onclick="selectOne(this, '.$k0.', '.$k4.', '.$pad.', '.$cob.');" type="checkbox" name="tipo'.$k.'" id="ocurre'.$k.'" value="O" '.$estatusO.' '.$reado2.'>
              <label class="form-check-label" for="ocurre">Ocurre</label>    
          </td>
              ' : '');
          $retorno .= '
          <td>
            <input style="font-size: 12px;" class="form-control" min="'.date("Y-m-d").'" type="date" name="fechaEnvio'.$k.'" id="fechaEnvio'.$k.'" value="'.$fenvio.'" required>
          </td>';        
          
            return $retorno;
        }


        function imprimircheckboxesCombos($k, $cob, $reado, $reado2) {
          /* $cob = ''; */
            return '
                <td class="sin-hover">
                    <input class="form-check-input" onclick="selectAll(this, '.$k.', 0, '.$cob.');" type="checkbox" name="tipoAll'.$k.'" id="domicilioAll'.$k.'" value="D" '.$reado.'>
                    <label class="form-check-label" for="domicilioAll'.$k.'">Todos</label>
                </td>&nbsp;&nbsp;

                <td class="sin-hover">
                    <input class="form-check-input" onclick="selectAll(this, '.$k.', 1, '.$cob.');" type="checkbox" name="tipoAll'.$k.'" id="recogeAll'.$k.'" value="C">
                    <label class="form-check-label" for="recogeAll'.$k.'">Todos</label>
                </td>&nbsp;&nbsp;
                <td class="sin-hover">
                    <input class="form-check-input" onclick="selectAll(this, '.$k.', 2, '.$cob.');" type="checkbox" name="tipoAll'.$k.'" id="pordefinirAll'.$k.'" value="P">
                    <label class="form-check-label" for="pordefinirAll'.$k.'">Todos</label>
                </td>&nbsp;&nbsp;'
                .($cob == "0" ? '
                <td class="sin-hover">
                    <input class="form-check-input" onclick="selectAll(this, '.$k.', 3, '.$cob.');" type="checkbox" name="tipoAll'.$k.'" id="ocurreAll'.$k.'" value="O" '.$reado2.'>
                    <label class="form-check-label" for="ocurreAll'.$k.'">Todos</label>
                </td>
                <td colspan="2" style="width: 200px;" class="sin-hover">
                </td>' : '');
        }

        $query0 = 'SELECT DISTINCT ccc_cotizaciond AS ccc_cotizaciond FROM crm_cotizacionesc 
                  WHERE ccc_cotizacion = "'.$idcotiza.'" 
                  ORDER BY ccc_cotizaciond ASC;';        
        $result0 = setq($query0);
        $k = 0;
        $consultas = '';
        $resp = 0;
        while($row0 = $result0->fetch_array()){
          //Buscamos el aticulo dentro los detalles de la cotización para determinar posteriormente su tipo (Combo o no)
          $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, cdm_cantidad, cdm_id, cdm_articulo 
                    FROM crm_cotizacionesd 
                    INNER JOIN articulos ON a_id = cdm_articulo 
                    WHERE cdm_id = "'.$row0['ccc_cotizaciond'].'"'; 
          $resultcmb = setq($sqlcmb);
          list($aid, $anmb, $tipoprod, $cantidad, $cdmid, $cdmarticulo) = $resultcmb->fetch_array();

          if($tipoprod == "M") {
          for($j = 1; $j <= intval($cantidad); $j++){

          $queryd = 'SELECT ccc_articulo, ccc_modelo, COUNT(*) as cantidad, ccc_id, ccc_cotizacion, ccc_cotizaciond, 
                      ccc_articulo, ccc_modelo, ccc_numero, ccc_tipoenvio, ccc_paqueteria, ccc_sucursal, 
                      ccc_combo, ccc_estatus, ccc_embarque
                      FROM crm_cotizacionesc
                      WHERE ccc_cotizacion = "'.$idcotiza.'" AND ccc_cotizaciond = "'.$row0['ccc_cotizaciond'].'" AND ccc_combo = "'.$j.'"
                      GROUP BY ccc_articulo, ccc_modelo;';
          $resultd = setq($queryd);

          if($resultd->num_rows > 0){
            echo '<tr style="background: #FFEE85 !important;" ><td class="sin-hover" colspan="2">PAQUETE - '.$anmb.' '.$j.' </td>'.imprimircheckboxesCombos($aid.$j, $cob, $reado, $reado2).'</tr>';
          }
          
          $res = 0;
          while($rowcb = $resultd->fetch_array()){
            $var = busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'COUNT(*)');
            
            if($var > 0) {
              $artname = busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'av_nmb');
            } else{
              $artname = busca($rowcb['ccc_articulo'], "articulos", "a_id", "a_nmb");
            }

            $tantos = intval($rowcb['cantidad']);
            if($tantos > 1){
              for($h = 1; $h <= $tantos; $h++){
                $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_id");
                $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_tipoenvio");
                $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
                echo '<tr><td>&nbsp;&nbsp; * '.$artname.' '.$h.'</td><td>'.$peso.' KG</td>' . imprimircheckboxes($p1, $p2, $cob, $reado, $reado2, $aid.$j) . ' ' .selectsucursal($p1, $p2, $cp, $cob, $aid.$j, $reado, $reado2).'</tr>';
                //Inicia listado de artículos ligados de este artículo que es un paquete con cantidad de uno
                $sqligados = 'SELECT al_cantidad, al_aligado, al_amodelo FROM articulos_ligados WHERE al_articulo = "'.$rowcb['ccc_articulo'].'"';
                $resultligado = setq($sqligados);
                while($rowligado = $resultligado->fetch_array()){
                  for($l = 0; $l < intval($rowligado['al_cantidad']); $l++){
                    $artnameligado = busca($rowligado['al_aligado'], "articulos", "a_id", "a_nmb"); 
                    $var = intval(busca($rowligado['al_aligado'], 'articulos_variantes', 'av_modelo = "' . $rowligado['al_amodelo'] . '" AND av_articulo', 'COUNT(*)'));
                    $modelo = $rowligado['al_amodelo'];
                    if ($var > 0) {
                      $artnameligado .= " ".busca($rowligado['al_aligado'], 'articulos_variantes', 'av_modelo = "' . $rowligado['al_amodelo'] . '" AND av_articulo', 'av_nmb');
                    }
                    $p12 = $p1."a".$rowligado['al_aligado']."n".($l+1);
                    if(!empty($modelo)){
                      echo '<input type="hidden" value="'.$modelo.'" name="modelo'.$p12.'">';
                    }
                    $artnameg = $artnameligado." ".($l + 1)." DEL ".$artname;
                    $peso = busca($rowligado['al_aligado'], 'articulos', 'a_id', 'a_peso');
                    echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp; ** ' . $artnameg . ' </td><td>'.$peso.' KG</td>' . imprimircheckboxes($p12, $p2, $cob, $reado, $reado2, $aid.$j) . ' ' .selectsucursal($p12, $p2, $cp, $cob, $aid.$j, $reado, $reado2).'</tr>';
                    $numarticulos++;
                  }
                }
                // Finaliza listado de artículos ligados de este artículo que es un paquete con cantidad de uno
              }
            } else{
              $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_id");
              $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_tipoenvio");
              $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
              echo '<tr><td>&nbsp;&nbsp * '.$artname . '</td><td>'.$peso.' KG</td>' . imprimircheckboxes($p1, $p2, $cob, $reado, $reado2, $aid.$j) . ' ' .selectsucursal($p1, $p2, $cp, $cob, $aid.$j, $reado, $reado2).'</tr>';
              //Inicia listado de artículos ligados de este artículo que es un paquete con cantidad de uno
              $sqligados = 'SELECT al_cantidad, al_aligado, al_amodelo FROM articulos_ligados WHERE al_articulo = "'.$rowcb['ccc_articulo'].'"';
              $resultligado = setq($sqligados);
              while($rowligado = $resultligado->fetch_array()){
                for($l = 0; $l < intval($rowligado['al_cantidad']); $l++){
                  $artnameligado = busca($rowligado['al_aligado'], "articulos", "a_id", "a_nmb"); 
                  $var = intval(busca($rowligado['al_aligado'], 'articulos_variantes', 'av_modelo = "' . $rowligado['al_amodelo'] . '" AND av_articulo', 'COUNT(*)'));
                  $modelo = $rowligado['al_amodelo'];
                  if ($var > 0) {
                    $artnameligado .= " ".busca($rowligado['al_aligado'], 'articulos_variantes', 'av_modelo = "' . $rowligado['al_amodelo'] . '" AND av_articulo', 'av_nmb');
                  }
                  $p12 = $p1."a".$rowligado['al_aligado']."n".($l+1);
                  if(!empty($modelo)){
                    echo '<input type="hidden" value="'.$modelo.'" name="modelo'.$p12.'">';
                  }
                  $artnameg = $artnameligado." ".($l + 1)." DEL ".$artname;
                  $peso = busca($rowligado['al_aligado'], 'articulos', 'a_id', 'a_peso');
                  echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp; ** ' . $artnameg . ' </td><td>'.$peso.' KG</td>' . imprimircheckboxes($p12, $p2, $cob, $reado, $reado2, $aid.$j) . ' ' .selectsucursal($p12, $p2, $cp, $cob, $aid.$j, $reado, $reado2).'</tr>';
                  $numarticulos++;
                }
              }
              // Finaliza listado de artículos ligados de este artículo que es un paquete con cantidad de uno
            }
            
          }
          if($resultd->num_rows > 0){
            echo '<tr style="background: #FFEE85" ><td class="sin-hover" colspan="8"></td></tr>';
          }
          }
          } else {
            for($i = 0; $i < intval($cantidad); $i++){
              $artname = $anmb.' '.($i + 1);
              $p1 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_numero = '".($i + 1)."' AND ccc_articulo", "ccc_id");                
              $p2 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_numero = '".($i + 1)."' AND ccc_articulo", "ccc_tipoenvio");
              $peso = busca($cdmarticulo, 'articulos', 'a_id', 'a_peso');
              echo '<tr><td>'.$artname.'</td><td>'.$peso.' KG</td>' . imprimircheckboxes($p1, $p2, $cob, $reado, $reado2) . ' ' .selectsucursal($p1, $p2, $cp, $cob, NULL, $reado, $reado2).'</tr>';
              //Inicia listado de artículos ligados de este artículo que no es un paquete
              $sqligados = 'SELECT al_cantidad, al_aligado, al_amodelo FROM articulos_ligados WHERE al_articulo = "'.$cdmarticulo.'"';
              $resultligado = setq($sqligados);
              while($rowligado = $resultligado->fetch_array()){
                for($l = 0; $l < intval($rowligado['al_cantidad']); $l++){
                  $artnameligado = busca($rowligado['al_aligado'], "articulos", "a_id", "a_nmb"); 
                  $var = intval(busca($rowligado['al_aligado'], 'articulos_variantes', 'av_modelo = "' . $rowligado['al_amodelo'] . '" AND av_articulo', 'COUNT(*)'));
                  $modelo = $rowligado['al_amodelo'];                  
                  if ($var > 0) {
                    $artnameligado .= " ".busca($rowligado['al_aligado'], 'articulos_variantes', 'av_modelo = "' . $rowligado['al_amodelo'] . '" AND av_articulo', 'av_nmb');
                  }
                  $p12 = $p1."a".$rowligado['al_aligado']."n".($l+1);
                  if(!empty($modelo)){
                    echo '<input type="hidden" value="'.$modelo.'" name="modelo'.$p12.'">';
                  }
                  $artnameg = $artnameligado." ".($l + 1)." DEL ".$artname;
                  $peso = busca($rowligado['al_aligado'], 'articulos', 'a_id', 'a_peso');
                  echo '<tr><td>&nbsp;&nbsp; ** ' . $artnameg . ' </td><td>'.$peso.' KG</td>' . imprimircheckboxes($p12, $p2, $cob, $reado, $reado2) . ' ' .selectsucursal($p12, $p2, $cp, $cob, NULL, $reado, $reado2).'</tr>';
                  $numarticulos++;
                }
              }
              // Finaliza listado de artículos ligados de este artículo que no es un paquete

              $k++;
              $resp = 0;
            }
          }
        }
      echo '</tbody>
      </table>
      <input type="hidden" id="cant" name ="cant" value="'.$k--.'">
      </form>
    </div>
  </center>
  </div>';
  ?>
    <script>

function checkIguales(k) {
    var checkboxes = document.querySelectorAll('.select'+k+':checked');
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




    function checkSelect(k){
    // Obtén todos los radio buttons con el mismo nombre
    var radioButtons = document.getElementsByName('tipoAll'+k);

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


    function limpiarCheck(id, cob){
      var limite = 4;
      if(cob != 0){
        limite = 4;
      }
      for (var i = 0; i < limite; i++) {
        document.getElementById("td"+id+i).style.background = '';
      }
      
    }

    function selectOne(checkbox, k, td, padre, cob) {   
    var select = document.getElementById('sucursalOcurre'+k);
    var paqueteria = document.getElementById('paqueteriaOcurre'+k);
    var paqueteriaDom = document.getElementById('paqueteriaDom'+k);
    
    if(select){
    if(checkbox.value != "O"){
      if(checkbox.value == "C" || checkbox.value == "P"){
        paqueteriaDom.hidden = true;
        paqueteria.hidden = true;
        select.hidden = true;
        paqueteriaDom.removeAttribute("required");
        paqueteria.removeAttribute("required");
        select.removeAttribute("required");
        
      } else{
        paqueteriaDom.hidden = false;
        paqueteria.hidden = true;
        select.hidden = true;
        paqueteriaDom.setAttribute("required", true);
        paqueteria.removeAttribute("required");
        select.removeAttribute("required");
      }
    } else {
      paqueteriaDom.hidden = true;
      paqueteria.hidden = false;
      select.hidden = false;
      paqueteria.setAttribute("required", true);
      select.setAttribute("required", true);
      paqueteriaDom.removeAttribute("required");
    }
    } else{
      if(checkbox.value == "C" || checkbox.value == "P"){
        paqueteriaDom.hidden = true;
        paqueteria.hidden = true;
        select.hidden = true;
        paqueteriaDom.removeAttribute("required");
        paqueteria.removeAttribute("required");
        select.removeAttribute("required");
      } else{
        paqueteriaDom.hidden = false;
        paqueteria.hidden = true;
        select.hidden = true;
        paqueteriaDom.setAttribute("required", true);
        paqueteria.removeAttribute("required");
        select.removeAttribute("required");
      }
    }

    limpiarCheck(k, cob);
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
          } else if(resultObject.value == "P"){
            document.getElementById("pordefinirAll"+padre).checked = true;
          } else {
            // Si es O            
            document.getElementById("ocurreAll"+padre).checked = true;
          }
      }
    }

  }

    function selectOneAll(checkbox, k) {
        var i = 0;
        var checkboxes = document.getElementsByName("tipoAll"+k);
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
    }

    function selectAll(idHTML, idArt, num, cob){
      // Obtén todos los elementos de tipo checkbox dentro del contenedor con la clase
      const checkboxes = document.querySelectorAll('.select'+idArt);
      const selects = document.querySelectorAll('.sel'+idArt);
      const selects2 = document.querySelectorAll('.selP'+idArt);
      const numberOfCheckboxes = checkboxes.length;
      //console.log('selects: ', selects);
      if(idHTML.value != "O"){
        selects.forEach(select => { 
          select.hidden = true;
        });
        if(idHTML.value == "C" || idHTML.value == "P"){
          selects2.forEach(select2 => { 
            select2.hidden = true;
          }); 
        }else{
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
      
      if(num == 0){
        marca = 0;
        p0 = 1;
        p1 = 2;
        p2 = 3;
        /* p3 = 4; */
      }
      if(num == 1){
        marca = 1;
        p0 = 0;
        p1 = 2;
        p2 = 3;
        /* p3 = 4; */
      }
      if(num == 2){
        console.log("Hip1");
        marca = 2;
        p0 = 0;
        p1 = 1;
        p2 = 3;  
        /* p3 = 4;   */ 
      }

      if(num == 3){
        marca = 3;
        p0 = 0;
        p1 = 1;
        p2 = 2;  
        /* p3 = 4;    */
      }
      
      if(cob == 0){
        if(num == 3){
        marca = 3;
        p0 = 0;
        p1 = 1;
        p2 = 2;  
        /* p3 = 4;   */
        }
      }
      var i = 0;          
      var j = 0; 
      checkboxes.forEach(checkbox => {  
        ind = checkbox.name.substring(4); // Empieza en el índice 4 y toma el resto del texto   
        var cant;
        if(cob == "0") cant = 2; else cant = 3;
        if(i == cant){
          document.getElementById("td"+ind+marca).style.background = '#c8c8c8';
          document.getElementById("td"+ind+p0).style.background = '';
          document.getElementById("td"+ind+p1).style.background = '';
          /* document.getElementById("td"+ind+p2).style.background = ''; */
          if(cob == "0") {document.getElementById("td"+ind+p2).style.background = '';}
          j++;
          i = 0;
        }
        
        if (checkbox.value === elemento) {
          checkbox.checked = true; // Marca el checkbox        
        } else{
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
      <div class="col-12 col-md-4">
        <div class="mb-5">
          <label for"adjunto">Número de guías</label>
          <input type="text" name="nguias" id="nguias" class="form-control" placeholder="Ingrese el número de guías" min="0" required>
        </div>
      </div> 
      <div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"adjunto">Archivo</label>
          <input type="file" name="adjunto" id="adjunto" class="form-control"/>
        </div>
      </div>  
      <div class="col-12 col-md-5">
        <div class="mb-5">
          <label for"comentario">Observacion del vendedor:</label>
          <textarea maxlength="100" class="form-control" placeholder="Agrega un comentario" onfocus="this.select();" readonly>'.$rowcc['cc_descripcion'].'</textarea>
        </div>
      </div>  

      <div class="col-12 col-md-12 text-center mb-4">
        <button type="button" onclick="motivoRegreso()" class="btn btn-danger" id="regresar">
          <i class="fa fa-arrow-left"></i> Devolver a ventas
        </button>
        <button type="submit" class="btn btn-primary mr-2" id="guardar">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>

    </div>
  </form>
  
  <form action="?modulo=cotizacionessolicitud&accion=regresoaventas" id="formulario" method="post" hidden>
    <input type="text" value="'.$_GET['id'].'" name="idcotiza">
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