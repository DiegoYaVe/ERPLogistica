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

function imprimircheckboxes($k, $articulotipo, $background, $idremision, $producto, $peso) {                       
  $sqlc = 'SELECT ccc_paqueteria, ccc_observacion, ccc_fenvio FROM crm_cotizacionesc WHERE ccc_id = "'.$k.'"';
  $resultc = setq($sqlc);
  list($pqt, $obs, $fecha) = $resultc->fetch_array();
  $folio = busca($idremision, 'remisiones', 'r_id', 'r_folio');
  $cliente = busca($idremision, 'remisiones INNER JOIN crm_clientes ON c_id = r_cliente', 'r_id', 'c_nmb');

  $nguias = 'SELECT ag_guia FROM articulos_guias WHERE ag_cccid="'.$k.'" ORDER BY ag_id ASC';
  $resultguia = setq($nguias);
  $n = 0;
  while($rowguia = $resultguia->fetch_array()){
    if($n == 0){
      $guia1 = $rowguia['ag_guia'];
    } else{
      $guia2 = $rowguia['ag_guia'];
    }
    $n++;
  }

  if($pqt == 0){
    if($articulotipo == "C"){
      $paqueteria = 'RECOGE EN SUCURSAL';
    } else {
      $paqueteria = 'OCURRE';
    }
    
  } else{
    $paqueteria = busca($pqt, 'paqueterias', 'p_id', 'p_nmb');
  }

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
  $rtn .= '<td>
        <center>
            <span>'.$paqueteria.'</span>
        </center>
        </td>
        <td>
        <center>
            <span>'.$fecha.'</span>
        </center>
        </td>
        ';
  $rtn .= '
        <td>
        <center>
            <input class="form-check-input" onclick="selectOne('.$parametros.')" type="checkbox" name="select'.$k.'" id="select'.$k.'" '.$check.' '.$disa.'>
        </center>
        </td>
        <td>
        <div style="display: flex; align-items: center;"> 
            <input placeholder="Ingrese el número de guía" class="form-control" type="text" name="in'.$k.'" id="in'.$k.'" value="'.$guia1.'" readonly>
            &nbsp;&nbsp;
        </div>';
  $rtn .= '</td>
        <td>
        <textarea class="form-control" id="obs'.$k.'" name="obs'.$k.'" readonly>'.$obs.'</textarea>
        </td>';
  return $rtn;
}

/* die($idremision); */
$sqlcc = 'SELECT * FROM remisiones WHERE r_id = "'.$idremision.'"';
/* $sql = 'SELECT * FROM crm_cotizacionesd
              WHERE cdm_cotizacion = "'.$idremision.'" ORDER BY cdm_id ASC';*/
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
  <form action="?modulo=guias&accion=setenvios" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary"><h2><span style="color: #5490c6;">Detalles remisión</span></h2><br>';
      $fini = $rowcc['r_fliquidacion'];  
      $almacen = busca($rowcc['r_almacen'], 'almacenes', 'a_id', 'a_nmb');
      $cliente = busca($rowcc['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');

      $idcotiza = busca($idremision, 'crm_cotizaciones', 'cc_remision', 'cc_id');
      $direnvio = busca($idremision, 'crm_cotizaciones', 'cc_remision', 'cc_direnvio');
      $direccion = busca($direnvio, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
      $telefono = busca($idremision, 'crm_cotizaciones', 'cc_remision', 'cc_teldestino');
      echo '      
      <span>Folio: '.$rowcc['r_folio'].'</span><br>
      <span>Cliente: '.$cliente.'</span><br>';
      if(!empty($rowcc['r_observaciones'])){
        echo '<span>Descripción: '.$rowcc['r_observaciones'].'</span><br>';
      }
      echo'
      <span>Direccion: '.$direccion.'</span><br>
      <span>Teléfono: '.$telefono.'</span><br>
      <!-- <h4><span style="color: #5490c6;">Importe total: $'.$rowcc['cc_importe'].'</span></h4><br> -->
      ';
      echo '</div>';

      //INICIA EL LISTADO DE PRODUCTOS
  echo '<div class="row mt-5" style="background: white;">
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
              <th width="13%">Paquetería</th>

              <th width="8%">Fecha envío programada</th>
              <th width="5%"></th>
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
            $sql = 'SELECT * FROM crm_cotizacionesd INNER JOIN articulos ON cdm_articulo = a_id WHERE cdm_cotizacion = "'.$idcotiza.'"';
            $result = setq($sql);

            $query0 = 'SELECT DISTINCT ccc_cotizaciond AS ccc_cotizaciond FROM crm_cotizacionesc 
                      WHERE ccc_cotizacion = "'.$idcotiza.'" 
                      ORDER BY ccc_cotizaciond ASC;';
            $result0 = setq($query0);
            $k = 0;
            $numarticulos = 0;
            $resp = 0;
            echo '<input type="hidden" value="'.$idcotiza.'" name="idcotiza">';
            echo '<input type="hidden" value="'.$idremision.'" name="idremision">';
            while($row0 = $result0->fetch_array()){
              //Buscamos el aticulo dentro los detalles de la cotización para determinar posteriormente su tipo (Combo o no)
              $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, cdm_cantidad, cdm_id, cdm_articulo FROM crm_cotizacionesd INNER JOIN articulos ON a_id = cdm_articulo WHERE cdm_id = "'.$row0['ccc_cotizaciond'].'"'; 
              $resultcmb = setq($sqlcmb);
              list($aid, $anmb, $tipoprod, $cantidad, $cdmid, $cdmarticulo) = $resultcmb->fetch_array();
              if($tipoprod == "M") {
              for($j = 1; $j <= intval($cantidad); $j++){
              $queryd = 'SELECT ccc_articulo, ccc_modelo, COUNT(*) as cantidad, ccc_id, ccc_cotizacion, ccc_cotizaciond, 
                          ccc_articulo, ccc_modelo, ccc_numero, ccc_tipoenvio, ccc_paqueteria, ccc_sucursal, 
                          ccc_combo, ccc_estatus, ccc_embarque
                          FROM crm_cotizacionesc
                          WHERE ccc_cotizacion = "'.$idcotiza.'" AND ccc_cotizaciond = "'.$row0['ccc_cotizaciond'].'" AND ccc_combo = "'.$j.'" AND ccc_tipoenvio != "C" AND ccc_tipoenvio != "P"
                          GROUP BY ccc_articulo, ccc_modelo ORDER BY ccc_paqueteria, ccc_id';

              $resultd = setq($queryd);

              if($resultd->num_rows > 0){
                echo '<tr style="background: #FFEE85 !important;"><td class="sin-hover" colspan="8">&nbsp;&nbsp;PAQUETE - '.$anmb.' '.$j.' </td></tr>';
              }
              $res = 0;
              while($rowcb = $resultd->fetch_array()){
                $artname = busca($rowcb['ccc_articulo'], "articulos", "a_id", "a_nmb");
                $var = busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'COUNT(*)');
                if($var > 0) {
                  $artname .= " ".busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'av_nmb');
                }
                $tantos = intval($rowcb['cantidad']);
                if($tantos > 1){
                  for($h = 1; $h <= $tantos; $h++){
                    $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_id");
                    $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_tipoenvio");
                    $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
                    $back = ($status != "N") ? ' style="background-color: rgb(204, 255, 204);"' : '';
                    $producto = '<td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' '.$h.' </td>';
                    $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
                    echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso) . '</tr>';
                    $numarticulos++;
                  }
                  $res = ($tantos - 1);
                } else{
                  $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_id");
                  $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_tipoenvio");
                  $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
                  $back = ($status != "N") ? ' style="background-color: rgb(204, 255, 204);"' : '';
                  $producto = '<td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' </td>';
                  $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
                  echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso) . '</tr>';
                  $res = 0;
                  $numarticulos++;
                }
                
                
              }
              if($resultd->num_rows > 0){
                echo '<tr style="background: #FFEE85" ><td class="sin-hover" colspan="8"></td></tr>';
              }
              }
              } else {
                $tantos = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_articulo", "COUNT(*)");
                $p1 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_articulo", "ccc_id");                
                $p2 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_articulo", "ccc_tipoenvio");
                $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
                $back = ($status != "N") ? ' style="background-color: rgb(204, 255, 204);"' : '';
                $producto = '<td>'.$anmb.'</td>';
                $peso = busca($cdmarticulo, 'articulos', 'a_id', 'a_peso');
                echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso) . '</tr>';
                $numarticulos++;
                $k++;
              }      
            }
            echo '
              <script>
              
                var totalreg = document.getElementById("totalreg");
                totalreg.textContent = "Número de artículos: '.$numarticulos.'";
              
              </script>
              ';
          echo '</tbody>
        </table>
        <input type="hidden" id="cant" name ="cant" value="'.$k--.'">
      </form>
    </div>
  </center>
  </div>';
  ?>
    <script>
      function selectOne(k, j = ''){
        var checkbox = document.getElementById("select"+k);
        var input = document.getElementById("in"+k);
        var obs = document.getElementById("obs"+k);
        /* var btn = document.getElementById("btn"+k); */
        if(j != ''){
          var input2 = document.getElementById("in"+k+"2");
          /* var btn2 = document.getElementById("btn"+k+"2"); */
        }
        if(checkbox.checked){
          input.removeAttribute("readonly");
          input.setAttribute("required", true);
          obs.removeAttribute("readonly");
          obs.setAttribute("required", true);
          input.focus();
          /* btn.removeAttribute("disabled"); */
          if(j != ''){
            input2.removeAttribute("readonly");
            /* btn2.removeAttribute("disabled"); */
          }
        } else{
          input.setAttribute("readonly", true);
          input.removeAttribute("required");

          obs.setAttribute("readonly", true);
          obs.removeAttribute("required");
          /* btn.setAttribute("disabled", true); */
          if(j != ''){
            input2.setAttribute("readonly", true);
            /* btn2.setAttribute("disabled", true); */
          }
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



    </script>

  <?php

//TERMINA EL LISTADO DE PRODUCTOS
      echo '
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
  echo '
      <div class="row">
      <!-- 
      <div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"adjunto">Archivo</label>
          <input type="file" name="adjunto" id="adjunto" class="form-control"/>
        </div>
      </div>  
      
      <div class="col-12 col-md-9">
        <div class="mb-5">
          <label for"comentario">Comentario</label>
          <textarea name="comentario" maxlength="100" id="comentario" class="form-control" placeholder="Agrega un comentario" onfocus="this.select();"></textarea>
        </div>
      </div>  
      -->

      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fas fa-save"></i>Guardar</button></center>
      </div>
    </div>
  </form>
</div>';