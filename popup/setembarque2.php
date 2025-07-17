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

  //INICIO DE LA FUNCIÓN PARA EL LLENADO DE LA TABLA POR CELDAS
  function imprimircheckboxes($k, $articulotipo, $background, $idremision, $producto, $peso) {
    /* $color = ' style="background: #c8c8c8;"'; */                         
    $pqt = busca($k, 'crm_cotizacionesc', 'ccc_id', 'ccc_paqueteria');
    $fecha = busca($idremision, 'remisiones', 'r_id', 'r_fliquidacion');
    $folio = busca($idremision, 'remisiones', 'r_id', 'r_folio');
    $cliente = busca($idremision, 'remisiones INNER JOIN crm_clientes ON c_id = r_cliente', 'r_id', 'c_nmb');
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
            <input placeholder="Ingrese el número de guía" class="form-control" type="text" name="in'.$k.'" id="in'.$k.'" value="" readonly>
            &nbsp;&nbsp;
        </div>';
    if(intval($peso) > 60){
    $rtn .= '
        <div style="padding-top: 5px; display: flex; align-items: center;">                     
          <input placeholder="Ingrese el número de guía" class="form-control" type="text" name="in'.$k."2".'" id="in'.$k."2".'" value="" readonly>
          &nbsp;&nbsp;
        </div>';
    }

    $rtn .= '</td>';
    return $rtn;
    } 
    //FIN DE LA FUNCIÓN PARA EL LLENADO DE LA TABLA POR CELDAS


    echo '
    <style>
    #myTableO {
      width: 100%;
      border-collapse: collapse;
    }

    #myTableO th, #myTableO td {
      padding: 8px;
      text-align: left;
      border: 1px solid #ddd;
      /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
    }

    #myTableO tbody tr:nth-child(odd) td {
      /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
    }

    #myTableO tbody tr:nth-child(even) td {
      /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
    }
    </style>
    ';
    //INICIA LA TABLA DE PRODUCTOS DE TODAS LAS REMISIONES
  echo'
  <div class="container mt-1 ">
  <form action="?modulo=guias&accion=setenvios" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">
  <div class="row">
  <div class="row mt-5" style="background: white;">
  <div class="card-body">
  <center>
    <div class="table-responsive col-12">
    <b><i><span id="totalreg" style="justify-content: left; display: flex; color: black;"></span></i></b><br>
    <form method="post" id="formenvios" action="?modulo=cotizaciones&accion=seleccionarenvio&idremision='.$idremision.'" autocomplete="off" class="mt-2 container">
    <table class="table table-hover" id="myTableO">
    <thead class="thead-active bg-primary text-white">
      <tr>
        <th width="10%">&nbsp;&nbsp;Remisión</th>
        <th width="15%">Cliente</th>
        <th width="25%">&nbsp;&nbsp;Producto</th>
        <th width="15%">Paquetería</th>
        <th width="10%">Fecha</th>
        <th colspan="2" width="30%">Guías</th>
      </tr>
    </thead>
    <tbody>';
    //COMIENZA EL LLENADO DE TABLA CON LOS ARTICULOS
    $querycot = 'SELECT cc_id FROM crm_cotizaciones WHERE cc_estatus = "V"';
    $numarticulos = 0;
    $resultcot = setq($querycot);
    $n = 0;
    while($rowcot = $resultcot->fetch_array()){
    $idcotiza = $rowcot['cc_id'];
    $idremision = busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_remision');
    

    $query0 = 'SELECT DISTINCT ccc_cotizaciond AS ccc_cotizaciond FROM crm_cotizacionesc 
              WHERE ccc_cotizacion = "'.$idcotiza.'" 
              ORDER BY ccc_cotizaciond ASC;';                      
    $result0 = setq($query0);
    $k = 0;
    $resp = 0;
    if($n == 0){
      $tcotiza .= $idcotiza;  
      $tremision .= $idremision;
    } else{
      $tcotiza .= ",".$idcotiza;
      $tremision .= ",".$idremision;
    }
    
    echo '<input type="hidden" value="'.$tcotiza.'" name="idcotiza">';
    echo '<input type="hidden" value="'.$tremision.'" name="idremision">';
    while($row0 = $result0->fetch_array()){
      //Buscamos el aticulo dentro los detalles de la cotización para determinar posteriormente su tipo (Combo o no)
      $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, cdm_cantidad, cdm_id, cdm_articulo FROM crm_cotizacionesd INNER JOIN articulos ON a_id = cdm_articulo WHERE cdm_id = "'.$row0['ccc_cotizaciond'].'"'; 
      $resultcmb = setq($sqlcmb);
      list($aid, $anmb, $tipoprod, $cantidad, $cdmid, $cdmarticulo) = $resultcmb->fetch_array();
      /* $resp = 0;
      if($resp == 0){ */
      if($tipoprod == "M") {
      for($j = 1; $j <= intval($cantidad); $j++){

      $queryd = 'SELECT * FROM crm_cotizacionesc WHERE ccc_cotizacion = "'.$idcotiza.'" AND ccc_cotizaciond = "'.$row0['ccc_cotizaciond'].'" AND ccc_combo = "'.$j.'" AND ccc_tipoenvio != "C" ORDER BY ccc_paqueteria';
      $resultd = setq($queryd);

      echo '<tr style="background: #FFEE85 !important;"><td colspan="7">&nbsp;&nbsp;PAQUETE - '.$anmb.' '.$j.' </td></tr>';
      $res = 0;
      while($rowcb = $resultd->fetch_array()){
        if($res == 0){
        $artname = busca($rowcb['ccc_articulo'], "articulos", "a_id", "a_nmb");
        $var = busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'COUNT(*)');
        if($var > 0) {
          $artname = busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'av_nmb');
        }
        $tantos = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "COUNT(*)");
        if($tantos > 1){
          for($h = 1; $h <= intval($tantos); $h++){
            $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_id");
            $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_tipoenvio");
            $back = ($rowcb['ccc_estatus'] != "N") ? ' style="background-color: rgb(204, 255, 204);"' : '';
            $producto = '<td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' '.$h.' </td>';
            $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
            echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso) . '</tr>';
            $numarticulos++;
          }
          $res = intval($tantos);
        } else{
          $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_id");
          $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_tipoenvio");
          $back = ($rowcb['ccc_estatus'] != "N") ? ' style="background-color: rgb(204, 255, 204);"' : '';
          $producto = '<td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' </td>';
          $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
          echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso) . '</tr>';
          $res = 0;
          $numarticulos++;
        }
      } else{
        $res--;
      } 
      }
      echo '<tr style="background: #FFEE85" ><td colspan="7"></td></tr>';
      }
      } else {
        $tantos = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_articulo", "COUNT(*)");
        if($tantos > 1){
          for($h = 1; $h <= intval($tantos); $h++){
            $p1 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_id");                
            $p2 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_tipoenvio");
            $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
            $back = ($status != "N") ? ' style="background-color: rgb(204, 255, 204);"' : '';
            $producto = '<td> '.$anmb.' '.$h.'</td>';
            $peso = busca($cdmarticulo, 'articulos', 'a_id', 'a_peso');
            /* echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' </td>' . imprimircheckboxes($p1.$h, $p2, $aid.$j) . '</tr>'; */
            echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso) . '</tr>';
            $numarticulos++;
          }
          $resp = $tantos;
        } else{
        $p1 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_articulo", "ccc_id");                
        $p2 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_articulo", "ccc_tipoenvio");
        $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
        $back = ($status != "N") ? ' style="background-color: rgb(204, 255, 204);"' : '';
        $producto = '<td> '.$anmb.'</td>';
        $peso = busca($cdmarticulo, 'articulos', 'a_id', 'a_peso');
        echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso) . '</tr>';
        $numarticulos++;
        $k++;
        $resp = 0;
        }
      }     
      }
      $n++;
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
  //FIN DE LA TABLA DE PRODUCTOS DE TODAS LAS REMISIONES
  ?>
  <script>
  function mandar(id){
  //var table = $("#myTable").DataTable();
  $("#myTableO").DataTable().clear().draw();
  $("#myTableO").DataTable().destroy();

  $("#myTableO").DataTable( {
    paging: true,
    scrollY: 400,
    processing: true,
    serverside: true,
    language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
    },
    ajax: {
      url: "query/datatableselectembarque.php",
      type: "POST",
      datatype: "json"
    },
    pageLength: "50",
    responsivePriority: 1,
    });
    }
    mandar(1);


    function selectOne(k, j = ''){
    var checkbox = document.getElementById("select"+k);
    var input = document.getElementById("in"+k);
    /* var btn = document.getElementById("btn"+k); */
    if(j != ''){
    var input2 = document.getElementById("in"+k+"2");
    /* var btn2 = document.getElementById("btn"+k+"2"); */
    }
    if(checkbox.checked){
    input.removeAttribute("readonly");
    input.setAttribute("required", true);
    input.focus();
    /* btn.removeAttribute("disabled"); */
    if(j != ''){
      input2.removeAttribute("readonly");
      /* btn2.removeAttribute("disabled"); */
    }
    } else{
    input.setAttribute("readonly", true);
    input.removeAttribute("required");
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
      <!--    
      <div class="row">
      <div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"adjunto">Archivo</label>
          <input type="file" name="adjunto" id="adjunto" class="form-control"/>
        </div>
      </div>  
      -->
      <!-- 
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