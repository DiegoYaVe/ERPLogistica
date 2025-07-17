<?php
  session_start();
  //ini_set('display_errors', 1);
  include_once('../funciones.php');
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
  //foreachdie();
  $cotizacion = $_GET['cotizacion'];
  $cotizaciond = $_GET['cotizaciond'];
  $sqlsel = 'SELECT cc_almacen, cc_estatus, a_id, a_nmb FROM crm_cotizaciones INNER JOIN crm_cotizacionesd ON cdm_cotizacion = cc_id INNER JOIN articulos ON cdm_articulo = a_id 
              WHERE cdm_id = "'.$cotizaciond.'" AND cdm_cotizacion = "'.$cotizacion.'"';
  $resultsel = setq($sqlsel);
  list($almacen, $estatus, $art, $nmbart) = $resultsel -> fetch_array();     
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD X7HTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" data-textdirection="ltr" class="loading">
<head>
    <base href="">
    <title>Fabrica de inflables</title>
    <meta name="description"
      content="SOMOS LA FÁBRICA #1 DE IFLABLES EN MÉXICO" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../assets/media/logos/favicon.png" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Page Vendor Stylesheets(used by this page)-->
    <link href="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Page Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/jquery.fancybox.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" class="">
    <!--end::Global Stylesheets Bundle-->
    <script src="../assets/plugins/global/plugins.bundle.js"></script>
    <script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  </head>
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
function ejecutar(id, accion, articulo, collapse, limite){
    let sel = document.getElementById('seleccionar'+id+'/'+articulo);
    let cant = document.getElementById('cantidad'+id+'/'+articulo);
    let span = document.getElementById('completo'+collapse);
    let max = document.getElementById('contador');

    /* ACCIONES
      1 - seleccionar
      2 - deseleccionar
      3 - Modificar cantidad
    */
    console.log('sel: '+sel.checked);
    if(sel.checked){  
      cant.disabled = false;
      if(parseInt(cant.value) == 0) cant.value = "1";
    } else {
      accion = "2";
      cant.value = "0";
      cant.disabled = true;
    }
    if(accion == "3"){
      var minimo = parseInt(cant.min);
      var maximo = parseInt(cant.max);
      if(cant.value > maximo || cant.value < minimo){
        alert("La cantidad ingresada es mayor a la existencia actual.");
        cant.value = "1.000";
        return false;
      }
    }

    $.ajax({
      url: "../query/setvariantecotizacion.php",
      type: "POST",
      data: {'cot': <?php echo $cotizacion; ?> ,
            'cotd': <?php echo $cotizaciond; ?>,
            'accion': accion,
            'cant': cant.value,
            'modelo': id, 
            'articulo': articulo, 
            'cmb': <?php echo $art; ?>,
            'maximo': limite
    },
    })
    .done(function(datax){
      console.log(datax);
      var datacol = JSON.parse(datax);
      if(accion != 2){
        if(datacol['error'] == "2"){
          alert("La cantidad de productos ingresada es mayor a la establecida en el paquete.");
          cant.value = "0";
          cant.disabled = true;
          sel.checked = false;
          span.innerHTML = 'Incompleto <b>('+datacol['count']+' de '+datacol['cantidad']+' seleccionadas)</b><i class="far fa-times-circle" style="color: #ec3232;"></i>';
          updatedetalle();
        } else if(datacol['error'] == "3"){
          alert("La cantidad máxima de productos seleccionables fue alcanzada.");
          cant.value = "0"; 
          sel.checked = false;
          cant.disabled = true;
          updatedetalle();
        } else {
          if(datacol['valida'] == "1"){
            span.innerHTML = 'Completo <b>('+datacol['cantidad']+' de '+datacol['cantidad']+' seleccionadas)</b><i class="fas fa-check" style="color: #00ff1e;"></i> ';
            document.getElementById('col'+collapse).click();
            var j = 0;
            for(let i = 1;i<= max.value; i++){
              let span = document.getElementById('completo'+i);
              if(span.textContent.includes('Incompleto')){
                document.getElementById('col'+i).click();
                break;
              }
              j++;
            }
            console.log('j: '+j+' max value: '+max.value);
            if(j == parseInt(max.value)){
              Swal.fire({
                title: 'Configuración terminada',
                icon: 'success',
              }).then((result) => {
                $.ajax({
                  url: "../query/setcomentario.php",
                  type: "POST",
                  data: {'cotizacion': <?php echo $cotizacion; ?> ,
                        'cotizaciond': <?php echo $cotizaciond; ?>,
                },
                })
                .done(function(dataq){
                  console.log(dataq);
                  opener.location.reload();
                  //window.close();
                })
              })
            }
          } else {
            span.innerHTML = 'Incompleto <b>('+datacol['suma']+' de '+datacol['cantidad']+' seleccionadas)</b> <i class="far fa-times-circle" style="color: #ec3232;"></i>';
          }
        }
      } else{
        span.innerHTML = 'Incompleto <b>('+datacol['suma']+' de '+datacol['cantidad']+' seleccionadas)</b> <i class="far fa-times-circle" style="color: #ec3232;"></i>';
        updatedetalle();
      }
    });
  }

  function updatedetalle(){
    $.ajax({
      url: "../query/validadetalle.php",
      type: "POST",
      data: {'cotizacion': <?php echo $cotizacion; ?> ,
            'cotizaciond': <?php echo $cotizaciond; ?>,
    },
    })
    .done(function(datax){
      console.log( "detalle" ,datax);
      opener.location.reload();
      //window.close();
    })
  }

  function abrircollapse(id){
    let max = document.getElementById('contador');

    for(let i = 1;i<= max.value; i++){
      if(i != id){
        if(document.getElementById('collapse'+i).classList.contains('show')){
          document.getElementById('col'+i).click();
        }
      }
    }
  }
</script>
<?php
//<form id="formnew" method="post" action="buscaprod?modulo='.$_GET['modulo'].'&accion='.$_GET['accion'].'&id='.$_GET['id'].'">

echo '<script>
      window.addEventListener("beforeunload", function() {
        opener.location.reload();
      });
      </script>';


if($estatus == "N" || $estatus == "R"){
  $title = "Variantes seleccionadas del artículo ".$artnmb;
  $col = "col-10";
}
else{
  $producto = busca($_GET['cotizacion'],'crm_cotizacionesd','cdm_id = "'.$_GET['idrec'].'" AND cdm_cotizacion','cdm_articulo');
  $display  = "";
  $title = "Elegir articulos del paquete ".$artnmb;
  $col = "col-12";
}


echo '<center>
<div class="mt-5 col-11 row">
  <div class="col-8 col-md-8 alert alert-info">Configuración de artículos para el paquete '.$nmbart.'</div>
  <div class="col-3 col-md-3 alert alert-success ms-6" ><h4>Precio:  $'.number_format(busca($cotizaciond, 'crm_cotizacionesd', 'cdm_id', 'cdm_precio'), 2).'</h4></div>
</div>

</center>';

$i = 0;
$j = 0;

$mostrados = "";
$sqla = 'SELECT a_id, a_nmb, cca_articulo, cca_cantidad FROM crm_cotizaciones_cambios INNER JOIN articulos ON a_id = cca_articulo WHERE cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$cotizaciond.'" AND cca_estatus = "N"';
$resulta = setq($sqla);
while($rowa = $resulta -> fetch_array()){
  $showac = 0;
  /* if($mostrados != ""){ //Verifica que solo se muestre una vez cada articulo
    $ids = explode(',', $mostrados);
    foreach($ids as $elemento){
      if($elemento == $rowa['ac_ahijo']){
        $showac = 1;
        break;
      }
    }
  } */
  /* //Checa si el articulo fue cambiado por otro
  $cambio = busca($rowa['ac_ahijo'], 'crm_cotizaciones_cambios', 'cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$cotizaciond.'" AND cca_articulo', 'cca_cambio');
  //Checa si el articulo es cambio de otro
  $cambio2 = busca($rowa['ac_ahijo'], 'crm_cotizaciones_cambios', 'cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$cotizaciond.'" AND cca_cambio', 'cca_articulo');
  if($showac != 1){
    if($cambio2) $showac = 1; #Si es cambio de otro no lo muestra para que solo se muestre por el que se cmabio
    else $showac = 0;
  } */
  /* if($cambio) {
    $articulo = $cambio;
    $canthijo = busca($articulo, 'articulos_combos', 'ac_articulo = "'.$art.'" AND ac_ahijo', 'ac_cantidad');
    if(!$canthijo) $canthijo = 0;
    $nmb = busca($articulo, 'articulos', 'a_id', 'a_nmb');
    $cantidad = floatval(busca($cambio, 'crm_cotizaciones_cambios', 'cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$cotizaciond.'" AND cca_cambio', 'SUM(cca_cantidad)'))+floatval($canthijo);
    $mostrados .= $articulo.',';
  }
  else {
    $articulo = $rowa['ac_ahijo'];
    $nmb = $rowa['a_nmb'];
    $cantidad = $rowa['ac_cantidad'];
  } */
  
  if($showac == 0){
    /* $sqlac = 'SELECT cca_articulo FROM crm_cotizaciones_cambios WHERE cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$cotizaciond.'" AND cca_cambio = "'.$articulo.'" ';
    $resultac = setq($sqlac);
    while($rowac = $resultac -> fetch_array()){
      $mostrados .= $rowac['cca_articulo'].',';
    } */
    $articulo = $rowa['a_id'];
    $nmb = $rowa['a_nmb'];
    $cantidad = $rowa['cca_cantidad'];
    $var = busca($articulo, 'articulos_variantes', 'av_articulo', 'COUNT(*)');
    if($var > 0){
      $i++;
      $title = "Elegir variantes del artículo ".$nmb;
      $count = busca($articulo, 'crm_cotizacion_variantes', 'ccv_cotizacion = "'.$cotizacion.'" AND ccv_cotizaciond = "'.$cotizaciond.'" AND ccv_articulo', 'SUM(ccv_cantidad)');
      if($count == $cantidad) {
        $done = 'Completo <b>('.number_format($count, 0).' de '.number_format($cantidad, 0).' seleccionadas) </b> <i class="fas fa-check" style="color: #00ff1e;"></i> ';
        $show = '';
      }
      else {
        if(!$count) $count="0";
        $done = 'Incompleto <b>('.number_format($count, 0).' de '.number_format($cantidad, 0).' seleccionadas)</b>  <i class="far fa-times-circle" style="color: #ec3232;"></i>';
        if($j == 0){
          $show = 'show';
          $j++;
        } else {
          $show = '';
        }
      }
      echo'
      <center>
        <div class="col-md-11 mt-2">
          <div class="row">
            <div class="'.$col.'">
                <button id="col'.$i.'" onclick="abrircollapse('.$i.')" class="btn btn-info text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$i.'" aria-expanded="false" aria-controls="collapse" style="width: 100%;">
                  <span class="d-flex justify-content-between align-items-center">
                    '.$title.'
                    <span id="completo'.$i.'" name>'.$done.' </span>
                  </span>
                </button>
            </div>';
            if($estatus == "N"  || $estatus == "R")
            echo '<div class="col-2">
              <a class="btn btn-primary" id="fancychange'.$articulo.'" data-fancybox data-type="ajax" data-src="cambiarartcmb.php?cotizacion='.$cotizacion.'&cotizaciond='.$cotizaciond.'&articulo='.$articulo.'&cantidad='.$cantidad.'" href="javascript:;">
                <i class="fas fa-exchange-alt"></i> Cambiar 
              </a>
            </div>';
          echo '</div>
        </div>
        </center>
        <div class="collapse '.$show.'" id="collapse'.$i.'">
          <div class="col-12 centrar-contenido">
            <form>';
            echo '
            <div class="">
              <div class="col-12">
                <table width="100%" class="table table-stripped table-bordered table-hover">
                <thead class="bg-primary bg-darken-2 text-white">
                <tr>';
                  if($estatus == "N"  || $estatus == "R")echo '<td style="width:5%;">Seleccionar</td>';
                  echo '<td style="width:20%;">Nombre</td>
                  <td style="width:20%;">Modelo</td>
                  <td style="width:20%;">Código de barras</td>';
                  if($estatus == "N"  || $estatus == "R") echo '<td style="width:15%;">Existencia</td>';
                  else echo '<td style="width:15%;">Existencia antes del movimiento</td>';
                  echo '<td style="width:30%;">Cantidad</td>
                  </tr>
                  </thead>
                  <tbody>';
                  
                    if($estatus == "N" || $estatus == "R"){
                      $sql = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$articulo.'"';
                      $result = setq($sql);
                      while($row = $result -> fetch_array()){
                        $vc = 'disabled';
                        $movl = busca($row['av_modelo'], 'crm_cotizacion_variantes', 'ccv_cotizacion = "'.$cotizacion.'" AND ccv_cotizaciond = "'.$cotizaciond.'"
                                    AND ccv_articulo = "'.$articulo.'" AND ccv_modelo', 'ccv_cantidad');
                        $exist = existenciaModelo($articulo, $almacen, $row['av_modelo']);
                        if(isset($movl)){
                          $checksel = 'checked';
                          $vc = '';
                        } else {
                          $checksel = '';
                          $movl = "0";
                        }
                        echo '
                        <tr>
                            <td style="width:5%; text-align: center!important;">
                            <div class="checkbox-wrapper-13 align-center">
                                <input type="checkbox" id="seleccionar'.$row['av_modelo'].'/'.$articulo.'" name="seleccionar'.$row['av_modelo'] .'" onChange="ejecutar(\''.$row['av_modelo'] .'\', \''.'1'.'\', \''.$articulo.'\', \''.$i.'\', \''.$cantidad.'\')" '.$checksel.'>
                            </div>
                            </td>
                            <td style="width:20%;">' . $row['av_nmb'] . '</td>
                            <td style="width:20%;">' . $row['av_modelo'] . '</td>
                            <td style="width:20%;">' . $row['av_cb'] . '</td>
                            <td style="width:20%;">' . number_format($exist, 2) . '</td>
                            <td style="width:45%;">
                                <input class="form-control" type="number" step="1" min="0" value="'.$movl.'" name="cantidad'.$row['av_modelo'].'" id="cantidad'.$row['av_modelo'].'/'.$articulo.'" onChange="ejecutar(\''.$row['av_modelo'].'\', \''.'3'.'\',  \''.$articulo.'\', \''.$i.'\', \''.$cantidad.'\')" '.$vc.'>
                            </td>
                            
                          </tr>
                        ';
                      }
                    } else {
                      $sql = 'SELECT * FROM crm_cotizacion_variantes WHERE ccv_cotizacion = "'.$cotizacion.'" AND ccv_cotizaciond = "'.$cotizaciond.'" AND ccv_articulo = "'.$articulo.'"';
                      $result = setq($sql);
                      while($row = $result -> fetch_array()){
                        $nmb = busca($row['ccv_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['ccv_modelo'].'" AND av_articulo', 'av_nmb');
                        $cb = busca($row['ccv_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['ccv_modelo'].'" AND av_articulo', 'av_cb');
                        echo '
                        <tr>
                            <td style="width:20%;">' . $nmb . '</td>
                            <td style="width:20%;">' . $row['ccv_modelo'] . '</td>
                            <td style="width:20%;">' . $cb . '</td>
                            <td style="width:20%;">' . $row['ccv_existenciaant'] . '</td>
                            <td style="width:45%;">
                                <input class="form-control" type="number" value="'.$row['ccv_cantidad'].'" disabled>
                            </td>
                          </tr>
                        ';
                      }
                    }
                  echo '</tbody>
                  </table>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      ';
    }
  }
}
$sqla = 'SELECT a_id, a_nmb, cca_articulo, cca_cantidad FROM crm_cotizaciones_cambios INNER JOIN articulos ON a_id = cca_articulo WHERE cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$cotizaciond.'" AND cca_estatus = "N"';
$resulta = setq($sqla);
while($rowa = $resulta -> fetch_array()){
  $articulo = $rowa['a_id'];
    $nmb = $rowa['a_nmb'];
    $cantidad = $rowa['cca_cantidad'];
    $var = busca($articulo, 'articulos_variantes', 'av_articulo', 'COUNT(*)');
    /* if($var  == 0){
      echo '<center>
      <div class="col-md-11 mt-2">';
      echo '<div class="alert alert-primary mt-2 d-flex justify-content-between align-items-center">
      '.$nmb.' - Cantidad: '.number_format($cantidad, 0);
    
      if($estatus == "N"  || $estatus == "R")
        echo '<a id="fancychange'.$row['ccv_id'].'" data-fancybox data-type="ajax" data-src="cambiarartcmb.php?cotizacion='.$cotizacion.'&cotizaciond='.$cotizaciond.'&articulo='.$articulo.'&cantidad='.number_format($cantidad, 0).'" href="javascript:;">
          <button class="btn btn btn-primary"><i class="fas fa-exchange-alt"></i> Cambiar 
          </button>
        </a>';
      echo'</div>
      </div>';
      echo '</center>';
    } */
}

echo '<div class="col-12">
  <center>
    <button type="button" class="btn btn-danger" onclick="cargar();"><i class="fas fa-times-circle"></i> Cerrar</button>
  </center>
</div>';

echo '<input type="hidden" id="contador" value="'.$i.'">';

?>

    <!-- BEGIN VENDOR JS-->
  </div>
    <!--begin::Javascript-->
    <!--begin::Global Javascript Bundle(used by all pages)-->
    <script src="../assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    <!--begin::Page Vendors Javascript(used by this page)-->
    <script src="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.js"></script>
    <!--end::Page Vendors Javascript-->
    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="../assets/js/custom/widgets.js"></script>
    <script src="../assets/js/custom/apps/chat/chat.js"></script>
    <script src="../assets/js/custom/modals/create-app.js"></script>
    <script src="../assets/js/custom/modals/upgrade-plan.js"></script>
    <script src="../assets/js/jquery.fancybox.min.js"></script>
  </body>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
</html>