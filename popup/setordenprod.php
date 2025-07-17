<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$ordenprod = $_GET['ordenprod'];
$sell = '';
$seli = '';
$selo = '';
$ptsid = '0';
$fentrega = date('Y-m-d');
$estatus = "N"; 
$titulo = "Insertar";
$cantidad = "0";
$accion = 'insert';
if($ordenprod){
  $titulo = "Actualizar";
  $forecastd = busca($ordenprod, 'pr_forecastdorden', 'pfo_ordenprod', 'pfo_forecastd');
  $forecast = busca($forecastd, 'pr_forecastd', 'prfd_id', 'prfd_forecast');  
  $nmbforecast = busca($forecast, 'pr_forecast', 'prf_id', 'prf_nmb');  
  $sql='SELECT * FROM pr_ordenprod WHERE po_id = "'.$ordenprod.'"';
  /* $sql='SELECT * FROM pr_ordenprod INNER JOIN articulos ON a_id = po_articulo WHERE po_id = "'.$ordenprod.'"'; */
  $result = setq($sql);
  $row = $result -> fetch_array();
  $accion = 'update';
  $articulo = $row['po_articulo'];
  $modelo = $row['po_modelo'];
  $nmbarticulo = $row['po_nmbarticulo'];
  $cantidad = $row['po_cantidad'];
  if($row['po_tipo'] == "L") $sell = "checked";
  else if($row['po_tipo'] == "I") $seli = "checked";
  else if($row['po_tipo'] == "O") $selo = "checked";
  $encargado = $row['po_encargado'];
  $obstipo = $row['po_obstipo'];
  $notasalida= $row['po_notasalida'];
  $fentrega = $row['po_fentrega'];
  $folio = $row['po_folio'];
  $estatus = $row['po_estatus'];
  $encargado = $row['po_encargado'];
  $encargado = $row['po_cortador'];
  $almacen = $row['po_almacen'];
  $almacendestino = $row['po_almacendestino'];
  $tiposoli = $row['po_tiposoli'];
  $solicitud = $row['po_solicitud'];
  $nmbsolicitud = busca($solicitud, 'pr_solicitudes', 'ps_id', 'CONCAT(ps_folio, " ", ps_nmb)');
  $obstiposoli = $row['po_obstiposoli'];
  $url = '&id="'.$ordenprod.'"';
} else if($_GET['forecast']) {
  $forecastd = $_GET['forecastd'];  
  $forecast = $_GET['forecast'];
  $nmbforecast = busca($forecast, 'pr_forecast', 'prf_id', 'prf_nmb');  
  $sql='SELECT * FROM pr_forecastd INNER JOIN articulos ON a_id = prfd_articulo WHERE prfd_id = "'.$forecastd.'"';
  $result = setq($sql);
  $row= $result -> fetch_array();
  $accion = 'insert';
  $articulo = $row['prfd_articulo'];
  $modelo = $row['prfd_modelo'];
  $nmbarticulo = $row['a_nmb'];
  if($modelo != ""){  
    $nmbarticulo .= ' '.busca($modelo, 'articulos_variantes', 'av_articulo = "'.$row['a_id'].'" AND av_modelo', 'av_nmb');
  }
  $artinop = busca($row['prfd_id'], 'pr_forecastdorden', 'pfo_forecastd', 'SUM(pfo_cantidad)');
  $cantidad = $row['prfd_cantidad']-$artinop;
  $sell = 'checked';
  $encargado='';
  $obstipo = '';
  $fentrega = busca($forecast, 'pr_checkpoints', 'pc_id ="'.$row['prfd_checkpoint'].'" AND pc_forecast', 'pc_ffin');
  $notasalida = '';
  $folio = '';
  $estatus = "N"; 
  $encargado = '';
  $encargado = '';
  $almacen = '';
  $almacendestino = '';
  $tiposoli = '';
  $obstiposoli = '';
  $solicitud = busca($forecast, 'pr_forecast', 'prf_id', 'prf_solicitud');
  $nmbsolicitud = busca($solicitud, 'pr_solicitudes', 'ps_id', 'CONCAT(ps_folio, " ", ps_nmb)');
  $url = '&forecast="'.$forecast.'"';
}

if($estatus != "N"){
  $read = "disabled";
} else {
  $read = '';
}

if($solicitud != "0" && $solicitud != ""){
  $bandera = false;
} else {
  $bandera = true;
}

echo '
    <div class="container col-10">
    <div class="col-12 alert alert-primary">'.$titulo.' orden de producción</div>
      <form method="post" action="index?modulo=ordenprod&accion='.$accion.'&id='.$ordenprod.'" enctype="multipart/form-data" id="formulario" onsubmit="checksubmit();">
        <input type="hidden" value="'.$forecastd.'" name="forecastd">
        <input type="hidden" value="'.$forecast.'" name="forecast">
        <input type="hidden" value="" name="ammp" id="ammp">
        <div class="row">';
        if($bandera){
          echo '
          <div class="mb-5 col-md-4 col-12">
            <label for="" class="">Solicitud</label>
            <select name="solicitud" id="solicitud" class="form-control">
              <option value="" class="">Selecciona solicitud</option>';
              $sqlsoli = 'SELECT * FROM pr_solicitudes WHERE ps_estatus = "N" ';
              $resultsoli = setq($sqlsoli);
              while($rowsoli = $resultsoli->fetch_array()){
                if($rowsoli['ps_id'] == $solicitud) $solisel = 'selected';
                else $solisel = '';
                echo '
                <option value="'.$rowsoli['ps_id'].'" class="" '.$solisel.'>'.$rowsoli['ps_folio']. ' '.$rowsoli['ps_nmb'].'</option>
                ';
              }
            echo '
            </select>
          </div>';
            } else{
          echo '
          <div class="mb-5 col-md-4 col-12" >
            <label for="" class="">Solicitud</label>
            <input class="form-control" name="nmbsolicitud" value="'.$nmbsolicitud.'" readonly>
            <input type="hidden" value="'.$solicitud.'" name="solicitud" id="solicitud">
          </div>';
          }

          if($folio != ""){
            echo '<div class="mb-5 col-md-4 col-12">
              <label>Folio:</label>
              <input type="text" id="folio" class="form-control" name="cantidad" value="'.$folio.'" required="required" readonly>
            </div>';
          }
          if($nmbforecast != ""){
            echo '<div class="mb-5 col-md-4 col-12">
              <label>Forecast:</label>
              <input type="text" id="forecast" class="form-control" name="nmbforecast" value="'.$nmbforecast.'" required="required" readonly>
            </div>';
          }
          echo '<input type="hidden" value="'.$folioart.'" id="valorart">';
          if($articulo || $modelo){
            $sqlts = 'SELECT pts_nmb, pts_descripcion FROM pr_tiposoli WHERE pts_id = "'.$tiposoli.'"';
            $resultts = setq($sqlts);
            list($nmbts, $descts) = $resultts -> fetch_array();
            echo '<div class="mb-5 col-md-4 col-12" hidden>
              <label>Tipo de solicitud:</label>
              <input type="text" id="tiposoliin" class="form-control" name="tiposoli" value="'.$nmbts.' - '.$descts.'" required="required" readonly>
            </div>';
            echo '<div class="mb-5 col-md-4 col-12">
              <label>Artículo:</label>
              <input type="text" id="nmb" class="form-control mayus" name="nmb" value="'.$nmbarticulo.'" required="required" readonly>
              <input type="hidden" value="'.$articulo.'" name="articulo">
              <input type="hidden" value="'.$modelo.'" name="modelo">';
              $folioart = busca($articulo, 'articulos_variantes', 'av_modelo = "'.$modelo.'" AND av_articulo', 'av_cb'); 
              if(empty($folioart)){
                $folioart = busca($articulo, 'articulos', 'a_id', 'a_cb');
                if(empty($folioart)){
                  echo '
                  <script>
                    alert("Error 512: El artículo no existe.");
                    window.location.reload();
                  </script>';
                }
              }

            echo '
              </div>';
          } else {
            echo '<div class="mb-5 col-md-4 col-12" hidden>
            <label>Tipo de solicitud:</label>
            <select class="form-control" id="tiposolisel" name="tiposoli" onchange="checartiposoli();">';
              $sql = 'SELECT * FROM pr_tiposoli';
              $result = setq($sql); 
              while($row = $result -> fetch_array()){
                if($tiposoli == $row['pts_id']) $sel = 'selected';
                else $sel = '';
                echo '<option value="'.$row['pts_id'].'" '.$sel.'>'.$row['pts_nmb'].' - '.$row['pts_descripcion'].'</option>';
              }
            echo '</select>
            </div>';
            echo '
            <div class="mb-5 col-md-4 col-12" id="obstiposolidiv">
              <label>Descripción del tipo de solicitud:</label>
              <input type="text" id="obstiposoli" class="form-control mayus" name="obstiposoli" placeholder="Describe el tipo de solicitud" value="'.$obstiposoli.'" required="required">
            </div>';
            echo '<div class="mb-5 col-md-4 col-12">
              <label>Artículo:</label>
              <input type="text" id="nmb" class="form-control mayus" name="nmb" placeholder="Nombre del artículo a fabricar" value="'.$nmbarticulo.'" required="required" autocomplete="off">
              <div id="suggestions" class="ocultaoscroll" style="max-height: 400px; overflow: overlay;"></div>
              <span id="error" style="color:#f1416c"></span>
              
            </div>';
          }
          echo '<div class="mb-5 col-md-4 col-12">
              <label>Cantidad:</label>
              <input type="number" min="1" id="cantidad" class="form-control" name="cantidad" value="'.$cantidad.'" required="required" onfocus="this.select();" '.$read.'>
            </div>';
          echo '<div class="mb-5 col-md-4 col-12">  
            <label>Tipo:</label>
            <br> <br>
            <input type="radio" class="form-check-input" id="liso" name="tipo" onchange="checartipo();" value="L" '.$sell.' '.$read.'> Liso
            <input type="radio" class="form-check-input" id="impresion" name="tipo" onchange="checartipo();" value="I" '.$seli.' '.$read.'> Impresión
            <input type="radio" class="form-check-input" id="otro" name="tipo" value="O" onchange="checartipo();" '.$selo.' '.$read.'> Otro (especificar)
          </div>';
          echo '<div class="mb-5 col-md-4 col-12" id="obs">  
            <label>Especificar:</label>
            <input type="text" id="texto" name="texto" value="'.$obstipo.'" class="form-control flipswitch2" placeholder="DESCRIBE EL TIPO DE INFLABLE" data-toggle="tooltip"  '.$sel.' '.$read.'>
          </div>';
          
          echo '<div class="mb-5 col-md-4 col-12">
            <label>Persona a cargo:</label>
            <select class="form-control" id="encargado" name="encargado" '.$read.' required>';
              $sql='SELECT * FROM usuarios WHERE u_grupo = "ADMIN" || u_grupo = "PRODUCCIÓN"';
              $result = setq($sql);
              while($row = $result -> fetch_array()){
                if($row['u_id'] != "ADMIN"){
                  if($row['u_id'] == $encargado) $sel = 'selected';
                  $sel = '';
                  echo '<option id="'.$row['u_id'].'" value="'.$row['u_id'].'" '.$sel.'>'.$row['u_nmb'].'</option>';
                }
              }
            echo '</select>
          </div>
          <div class="mb-5 col-md-4 col-12">
            <label>Cortador:</label>
            <select class="form-control" id="cortador" name="cortador" '.$read.' required>';
              $sql='SELECT * FROM pr_empleados WHERE pe_estatus = "A" AND pe_rol IN (SELECT rp_idrol FROM roles_permisos WHERE rp_proceso IN (SELECT ppr_id FROM pr_procesos WHERE ppr_tipo = "C"))';
              $result = setq($sql); 
              while($row = $result -> fetch_array()){
                if($row['pe_id'] == $cortador) $sel = 'selected';
                $sel = '';
                echo '<option id="'.$row['pe_id'].'" value="'.$row['pe_id'].'" '.$sel.'>'.$row['pe_nmb'].'</option>';
              }
            echo '</select>
          </div>';
          echo '<div class="mb-5 col-md-3 col-12">
            <label>Fecha de entrega:</label>
            <input type="date" id="fentrega" class="form-control" name="fentrega" value="'.$fentrega.'" required="required" onfocus="this.select();" '.$read.'>
          </div>
          <div class="mb-5 col-md-3 col-12">
            <label>Almacén de materia prima:</label>
            <select class="form-control" id="almacen" name="almacen" '.$read.' required>';
              $sql='SELECT * FROM pr_almacenes WHERE pa_estatus = "A" AND pa_tipoa = "M"';
              $result = setq($sql);
              while($row = $result -> fetch_array()){
                if($row['pa_id'] == $almacen) $sel = 'selected';
                $sel = '';
                echo '<option id="'.$row['pa_id'].'" value="'.$row['pa_id'].'" '.$sel.'>'.$row['pa_nmb'].'</option>';
              }
            echo '</select>
          </div>
          <div class="mb-5 col-md-6 col-12">
            <label>Notas de salida:</label>
            <textarea id="notasalida" class="form-control" name="notasalida" placeholder="ESCRIBE LAS NOTAS DE SALIDA" onfocus="this.select();" '.$read.'>'.$notasalida.'</textarea>
          </div>

          <div class="mb-5 col-md-4" id="divalmacen">
                <label>Almacen de destino:</label>
                <select id="almacendestino" name="almacendestino" class="form-control" '.$read.' required>';
                    $sqlalm = 'SELECT * FROM pr_almacenes WHERE pa_estatus = "A" AND pa_tipoa = "I"';
                    $resultalm = setq($sqlalm);
                    if($resultalm->num_rows == 0){
                        echo '<option value="">No hay almacenes disponibles</option>';
                    } else {
                        echo '<option value="">Selecciona un almacen</option>';
                        while($rowalm = $resultalm->fetch_array()){
                        if($rowalm['pa_id'] == $almacendestino){
                          $sel2 = 'selected';
                        } else{
                          $sel2 = '';
                        }
                        echo '<option value="'.$rowalm['pa_id'].'" '.$sel2.'>'.$rowalm['pa_nmb'].'</option>';
                        }
                    }
                    echo '
                </select>            
            </div>';

            $ruta = "'img/" . busca($ordenprod, 'pr_ordenprod', 'po_id', 'po_adj') . "'";
            echo '<input type="hidden" id="rutaimg" value="'.$ruta.'" name="rutaimg">';
            $ver = '';
            if($estatus == "N"){
            $timagen = busca($ordenprod, 'pr_ordenprod', 'po_id', 'po_tipoimagen');

            if($timagen == "E"){
              $chec = "checked";
              $hid = "";
              $req = "";
              $hid2 = 'hidden';
            } else{
              $chec = "";
              $hid = "hidden";
              $req = 'required';
              $hid2 = '';
            }
              
            echo '
            <div class="mb-5 col-md-4">
              <label>Tipo de imagen:</label>
              <input class= "flipswitch-imagen form-control" type="checkbox" name="timagen" id="timagen" onchange="selTipoImagen();" '.$chec.'>
            </div>
              ';

              echo '
              <div class="mb-5 col-md-4 col-4" id="divadj" '.$hid2.'>
                  <label>Imagen ilustrativa:</label>
                  <input type="file" id="adj" class="form-control" name="adj" onfocus="this.select();" '.$req.'>
                </div>
              </div>';
            } else{
              $hid = ' style="display: flex; justify-content: center;"';
              $ruta = "'img/" . busca($ordenprod, 'pr_ordenprod', 'po_id', 'po_adj') . "'";
              $ver = '
             <div class="view overlay col-md-12 col-12 div-sel seleccionado-dos" style="height: 220px; width: 220px; background: url(' . $ruta . '); background-size: contain; background-position: center; background-repeat: no-repeat;" alt="Imágen descriptiva">
             </div>';
            }

            //Inicio
            //Crearemos un panel de selección para las imágenes disponibles del artículo a producir
            echo '<div class="mb-5 row" id="divimg" '.$hid.'>';
            echo $ver;
                  echo '
                  </div>';
            echo '
            <script>
              function selDiv(k){
                var elementosDivSel = document.querySelectorAll(".div-sel");
                
                elementosDivSel.forEach(function (elemento) {
                  if(elemento.id != "div"+k){
                    elemento.classList.remove("seleccionado");
                    elemento.classList.add("seleccionado-dos");
                  }
                });
                var miElemento = document.getElementById("div"+k);
                if(miElemento.classList.contains("seleccionado")){
                  miElemento.classList.remove("seleccionado");
                  miElemento.classList.add("seleccionado-dos");
                  rutaimg.value = "";
                } else{
                  miElemento.classList.add("seleccionado");
                  miElemento.classList.remove("seleccionado-dos");
                  rutaimg.value = document.getElementById("in"+k).value;
                }
              }
            </script>
            ';
            // Final
            
        if($estatus == "N"){
          echo '<div class="col-12">
            <center>
              <div class="mb-5 col-md-2 col-12">
                <button type="submit" class="btn btn-primary" id="guardar" onclick="enviarForm();"><i class="fa fa-save"></i> Guardar </button>
              </div> 
            </center>
          </div>';
        }
        
      echo '</form>
    </div>';
?>
<script>
var forecast = document.getElementById('forecast');
var solicitud = document.getElementById('solicitud').value;
var divimg = document.getElementById('divimg');
var rutaimg = document.getElementById("rutaimg");
var formulario = document.getElementById('formulario');
// Captura el formulario

// Agrega un escuchador de eventos para el evento submit
formulario.addEventListener('submit', function(event) {
    // Previene el envío del formulario
    event.preventDefault();

    if(enviarForm()){
      formulario.submit();
    }
});


function enviarForm(){
  var bandera = true;
  var timagen = document.getElementById('timagen');
  if(timagen){
  // Selecciona todos los elementos con la clase "seleccionado"
  var elementosSeleccionados = document.querySelectorAll('.seleccionado');

  // Obtiene la longitud de la NodeList
  var cantidad = elementosSeleccionados.length;

  if(timagen.checked){
    if(cantidad == 0){
      Swal.fire({
        title: "Atención",
        text: "Selecciona una imagen para continuar",
        icon: "warning"
      });
      bandera = false;
    } else{
      bandera = true;
    }
  }
  } else{
    bandera = true;
  }

  return bandera;
  
}

function selTipoImagen() {
    var timagen = document.getElementById('timagen');
    var adj = document.getElementById('adj');
    var divadj = document.getElementById('divadj');
    
    var elementosDivSel = document.querySelectorAll(".div-sel");        

    if (timagen.checked) {
        divadj.setAttribute("hidden", "true");
        divimg.removeAttribute("hidden");
        adj.removeAttribute("required");
        
        elementosDivSel.forEach(function (elemento) {
          elemento.classList.remove("seleccionado");
          elemento.classList.add("seleccionado-dos");
        });
    } else {
        divimg.setAttribute("hidden", "true");
        divadj.removeAttribute("hidden");
        adj.setAttribute("required", "true");
        rutaimg.value = '';
    }
}


  function checartipo(){
    var otro = document.getElementById('otro');
    var texto = document.getElementById('obs');
    var inptext = document.getElementById('texto');
    if(otro){ 
      if(otro.checked){
        texto.hidden = false;
        inptext.setAttribute('required', 'required')
      }else {
        texto.hidden = true;
        inptext.removeAttribute('required')
      }
    }
  }
  function checartiposoli(){
    var seltipo = document.getElementById('tiposolisel');
    var obs = document.getElementById('obstiposoli');
    var obsdiv = document.getElementById('obstiposolidiv');
    if(seltipo){
      if(seltipo.value == "4"){
        obsdiv.hidden = false;
        obs.setAttribute('required', 'required')
      }else {
        obsdiv.hidden = true;
        obs.removeAttribute('required')
      }
    }
  }
  checartipo();
  checartiposoli();
  $(document).ready(function() {
    $('#nmb').on('keyup', function() {
      
      if (event.keyCode == 38 || event.keyCode == 40){
      }else{
      var boton = document.getElementById('guardar');
      var spanerror = document.getElementById('error');  
      
      var ammp = document.getElementById("ammp");
      // Obtén el elemento por su ID
      //var btnmermas = document.getElementById("btnmermas");
      //var abtnmermas = document.getElementById("abtnmermas");

      boton.disabled = false;
      spanerror.textContent = '';
      ammp.value = "";
      
      var key = $(this).val();
      //var empresa = $('#empresa').val();
      var dataString = 'producto='+key;
      $.ajax({
        type: "POST",
        url: "query/suggestproducts2.php",
        data: dataString.trim(),
        success: function(data) {
          //Escribimos las sugerencias que nos manda la consulta
          $('#suggestions').fadeIn(500).html(data);
          //Al hacer click en alguna de las sugerencias
          $('.suggest-element').on('click', function(){
            //Obtenemos la id unica de la sugerencia pulsada
            var id = $(this).attr('id');
            var valor = $(this).attr('value');
            var spanText = '';

            //Procedemos a buscar las imagenes correspondientes a el artículo
            
            $.ajax({
            type: "POST",
            dataType: 'json',
            url: "query/traerimagenes.php",
            data: {'articulo': valor},
            success: function(data) {
              if(data.respuesta == 1){ //Si hay imagenes registradas        
                divimg.innerHTML = data.html;
              } else{ //No hay imagenes registradas
                divimg.innerHTML = "<span><center>No hay imagenes disponibles</center></span>";
              }
            },
            error: function(jqXHR, textStatus, errorThrown) {
              console.error("Error en la petición AJAX: " + errorThrown);
            }
          });
            rutaimg.value = "";

            //var esquema = document.getElementById('esquema');
            //Editamos el valor del input con data de la sugerencia pulsada
            $('#nmb').val($('#'+id).attr('data'));
            //Hacemos desaparecer el resto de sugerencias
            $('#suggestions').fadeOut(500); 
            $.ajax({
              type: "POST",
              url: "query/checkprocesos.php", 
              data: {'articulo': valor},
              success: function(dataRTN) {
                console.log(dataRTN);
                if(dataRTN == "0"){
                  boton.disabled = true;
                  spanerror.textContent = 'El artículo no tiene configurado los procesos para su producción.';
                  ammp.value = "";
                } else {
                  boton.disabled = false;
                  spanerror.textContent = '';
                  ammp.value = "";
                }
              }
            });

              //(Inicio) Buscamos si existen mermas de este artículo
              $.ajax({
              type: "POST",
              dataType: 'json',
              url: "query/buscarmermas.php", 
              data: {'articulo': valor},
              success: function(data) {
                spanText = spanerror.textContent;

                if(data.respuesta == 4){
                  spanerror.textContent = spanText+' El artículo seleccionado tiene mermas en existencia.';
                  ammp.value = data.ammp; //Id de la tabla de articulos_mermas
                } else{
                  spanerror.textContent = spanText;
                  ammp.value = "";
                }
              }
            });
            //(Fin) Buscamos si existen mermas de este artículo
            //setname();
            //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
            $("#cantidad").focus();
            return false;
          });
        }
      });
      }
    });
  });
  let listGroup = document.getElementById('suggestions');
  // Asignar evento al campo de texto
  document.querySelector('#nmb').addEventListener('keydown', e => {
    if(!listGroup) {
      return; // No existe la lista
    }    
    // Obtener todos los elementos
    let items = listGroup.querySelectorAll('a');
    var a = document.getElementById("suggestions");
    // Saber si alguno está activo
    let actual = Array.from(items).findIndex(item => item.classList.contains('active'));
    //let actual = 0;
    // Analizar tecla pulsada
    if(e.keyCode == 13) {
      // Tecla Enter, evitar que se procese el formulario
      e.preventDefault();
      // ¿Hay un elemento activo?
      if(items[actual]) {
        // Hacer clic
        items[actual].click();
      }
    } 
    if(e.keyCode == 38 || e.keyCode == 40) {
      // Flecha arriba (restar) o abajo (sumar)
      if(items[actual]) {
        // Solo si hay un elemento activo, eliminar clase
        items[actual].classList.remove('active');
        //items[actual].className += " active";
      }
      // Calcular posición del siguiente
      if((e.keyCode == 38)){
        a.scrollTop -= ((items[actual].clientHeight - 1));
        actual += -1;
      }else{
        if(actual > 1) a.scrollTop += ((items[actual-2].offsetHeight + 1));            
        actual += 1;
      }
      //actual += (e.keyCode == 38) ? -1 : 1;
      // Asegurar que está dentro de los límites
      if(actual < 0) {
        actual = 0;
      } else if(actual >= items.length) {
        actual = items.length - 1;
      } 
      // Asignar clase activa
      items[actual].classList.add('active');
      items[actual].setAttribute('autofocus','');
      //items[actual].className += " active";
    }
  });
  // En la función donde generas la lista debes activar evento clic para cada elemento
  // Para este ejemplo se hace manual
  document.addEventListener('DOMContentLoaded', function() {
  // Obtener el elemento
    var listGroup = document.getElementById('suggestions');

    // Verificar si el elemento existe
    if (listGroup) {
      // Iterar sobre los elementos si existe
      listGroup.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', e => {
          // Asignar valor al campo
          document.querySelector('#nmb').value = e.currentTarget.textContent;
          // Aquí deberías cerrar la lista y/o eliminar el contenido
        });
      });
    } else {
      console.error('El elemento listGroup no existe.');
    }

  });

  startPanel();

  function startPanel(){
    if(forecast || solicitud != ""){
    var val = document.getElementById("valorart").value;    
    var rutaimg = document.getElementById("rutaimg").value;  
    var timagen = document.getElementById("timagen");  
    var adj = document.getElementById('adj');
    var divadj = document.getElementById('divadj');
                
    
    // Procedemos a buscar las imágenes correspondientes al artículo
    $.ajax({
      type: "POST",
      dataType: 'json',
      url: "query/traerimagenes.php",
      data: {'articulo': val,
             'ordenp': "<?php echo $_GET['ordenprod']?>",
             'tipo': 1},
      success: function(data) {
        /* console.log("respuesta: "+data.respuesta); */
        divimg.removeAttribute("hidden");
        if(data.respuesta == 1){ //Si hay imagenes registradas        
          divimg.innerHTML = data.html;

          // Convertir la cadena de texto en un arreglo utilizando el método split
          var arreglo = data.rutas.split(',');

          var indice = arreglo.indexOf(rutaimg);

          // Verificar si la cadena específica está presente y obtener su índice
          if (indice !== -1) {
              var miElemento = document.getElementById("div"+(indice + 1));
              miElemento.classList.remove("seleccionado-dos");
              miElemento.classList.add("seleccionado");
              timagen.checked = true;
              divadj.setAttribute("hidden", "true");
              divimg.removeAttribute("hidden");
              adj.removeAttribute("required");
          }

        } else{ //No hay imagenes registradas
          divimg.innerHTML = "<span><center>No hay imagenes disponibles</center></span>";
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error("Error en la petición AJAX: " + errorThrown);
      }
    });
    rutaimg.value = ""; 
    }
  }

  function checksubmit() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
  }

</script>