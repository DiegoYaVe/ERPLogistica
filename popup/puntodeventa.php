<?php
//ini_set('display_errors',1);
include('../funciones.php');
date_default_timezone_set("America/Mexico_City");
session_start();

  $sqlseg = 'SELECT * FROM esquema_precio WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_puntodv = "1" AND ep_estatus = "A"';
  $resultseg = setq($sqlseg);
  $rowseg = $resultseg->fetch_array();
  if($rowseg['ep_puntodv'] == NULL){
    echo '
    <script>
      alert(\'Selecciona un esquema para el punto de venta. Dirigete a "ARTICULOS" -> "ESQUEMAS DE PRECIOS"\');
      window.opener.location.href = "https://jdceo.jdsuite.mx/?modulo=precios&accion=index";
      window.close();
    </script>
    ';
  }


  $sql = 'SELECT * FROM pv_tableroscaja WHERE tc_cajero = "'.$_SESSION['uid'].'" and tc_empresa = "'.$_SESSION['emp'].'" ORDER BY tc_id DESC';
  $result = setq($sql);
  $row = $result->fetch_array();
  if($row == NULL || $row['tc_estatus'] == "F" ){
    /*echo '
    <script>
    alert("Abre nueva caja para este usuario");
    </script>
    ';*/
    $disabled = 'disabled';
    $hidden = '';
    $hiddencontra = 'hidden';
  }elseif($row['tc_estatus'] == "A" || $row['tc_estatus'] == "N"){
    $disabled = '';
    $hidden = 'hidden'; 
    $hiddencontra = '';
    $idcaja = $row['tc_id'];
    $sqlticket = 'SELECT MAX(pt_id) FROM pv_tickets WHERE pt_caja = "'.$idcaja.'" AND pt_estatus = "N"';
    $resultticket = setq($sqlticket);
    $rowticket = $resultticket->fetch_array();
    if($rowticket['MAX(pt_id)'] == NULL){
      $acronimo = busca($_SESSION['emp'],'empresas','e_id','e_siglas');
      $acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas').'-TCK';
      $folioint = getmax('pt_folio','pv_tickets','pt_empresa = "'.$_SESSION['emp'].'"');
      if($folioint == "1"){
        $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
      }
      $sqlinsert = 'INSERT INTO pv_tickets SET
                    pt_folio = "'.$folioint.'",
                    pt_caja = "'.$idcaja.'",
                    pt_estatus = "N",
                    pt_cajero = "'.$_SESSION['uid'].'",
                    pt_empresa = "'.$_SESSION['emp'].'"';
      setq($sqlinsert);
      $sqlticket = 'SELECT MAX(pt_id) FROM pv_tickets WHERE pt_caja = "'.$idcaja.'" AND pt_estatus = "N"';
      $resultticket = setq($sqlticket);
      $rowticket = $resultticket->fetch_array();
      $idticket = $rowticket['MAX(pt_id)'];
      
    }else{
      $idticket = $rowticket['MAX(pt_id)'];
    }
    
  }

  $revisar = busca($idticket,'pv_tickets','pt_id','pt_total');
  if($revisar > 0) $disabled2 = '';
  else $disabled2 = 'disabled';


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD X7HTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <!-- //// -->
<html lang="en" data-textdirection="ltr" class="loading">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no"/>
    <meta name="author" content="JD Suite">
    <title>JD CEO</title>
    <link rel="icon" type="image/png" href="../images/ico/favicon-32.png">
    <link rel="apple-touch-icon" sizes="60x60" href="../images/ico/apple-icon-60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="../images/ico/apple-icon-76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="../images/ico/apple-icon-120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="../images/ico/apple-icon-152.png">
    <link rel="shortcut icon" type="image/x-icon" href="../images/ico/favicon-32.png">
    <link rel="shortcut icon" type="image/png" href="../images/ico/favicon-32.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
    <!-- font icons-->
    <link rel="stylesheet" type="text/css" href="../fonts/icomoon.css">
    <link rel="stylesheet" type="text/css" href="../fonts/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" type="text/css" href="../css/pace.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN ROBUST CSS-->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="../css/app.css">
    <link rel="stylesheet" type="text/css" href="../css/colors.css">
    <link rel="stylesheet" type="text/css" href="../css/screen.css">
    <!-- END ROBUST CSS-->
    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="../css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="../css/core/menu/menu-types/vertical-overlay-menu.css">
    <link rel="stylesheet" type="text/css" href="../css/core/colors/palette-gradient.css">
    <!-- END Page Level CSS-->
    <!-- BEGIN Custom CSS-->
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <!-- END Custom CSS-->
    <link rel="stylesheet" type="text/css" href="../css/select2.min.css" />
    <link rel="stylesheet" type="text/css" href="../css/dropzone.css" />
    <link rel="stylesheet" type="text/css" href="../css/jquery.fancybox.min.css" />
    <!-- TINYMCE-->
    <script src="../js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
    <!-- //// -->
  </head>

  <body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="menu-collapsed vertical-layout vertical-menu 2-columns bg-white" style="mt-2">
    <style>
      div::-webkit-scrollbar {
          display: none;
      }
    </style>
      <!-- style="width:40%;" bg: #e3ebf3-->
      <div class="col-md-12 table-inverse p-1" style="">
        <div class="col-md-6">
          <h4 style="">Punto de Venta JDCEO</h4>
        </div>
        <div class="col-md-6 text-xs-right">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-target="#exampleModal" style="display: contents;"><i class="icon-question-circle" style="color: #02ff02; font-size: x-large;"></i></button>
        </div>
      </div>


      <!-- Modal  Atajos -->
      <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Atajos del Teclado</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <th >Comando</th>
                      <th >Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td ><kbd><kbd>Alt</kbd> + <kbd>c</kbd></kbd></td>
                      <td >Cobrar ticket.</td>
                    </tr>
                    <tr>
                      <td ><kbd><kbd>Alt</kbd> + <kbd>t</kbd></kbd></td>
                      <td >Ver tickets de la caja.</td>
                    </tr>
                    <tr>
                      <td ><kbd><kbd>Alt</kbd> + <kbd>x</kbd></kbd></td>
                      <td >Borrar el ultimo producto agregado a la lista.</td>
                    </tr>
                    <tr>
                      <td ><kbd><kbd>Alt</kbd> + <kbd>l</kbd></kbd></td>
                      <td >Limpiar campos.</td>
                    </tr>
                    <tr>
                      <td ><kbd><kbd>Alt</kbd> + <kbd>b</kbd></kbd></td>
                      <td >Buscar producto.</td>
                    </tr>
                    <tr>
                      <td ><kbd><kbd>Crtl</kbd> + <kbd>F4</kbd></kbd></td>
                      <td >Cerrar ventana.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
            </div>
          </div>
        </div>
      </div>
    </div>


    <form method="post" id="formularioprod" action="../ticketcj.php?idcaja=<?php echo $idcaja.'&idticket='.$idticket; ?>" autocomplete="off" >
      <!-- HEADER -->
      <div class="container-fluid" id="btncobro">
        <div class="row">
          <div class="col-md-12 p-1 form-inline" style=" ">
            <div class="mb-5 col-md-2">
              <label for=""><i class="icon-male2"></i> Cajero: </label>
              <input type="text" class="form-control" name="cajero" value="<?php echo $_SESSION['uid']; ?>" readonly <?php echo $disabled; ?> >
            </div>
            <div class="mb-5 col-md-2">
              <label for=""><i class="icon-group"></i> Cliente: </label>
              <input type="text" class="form-control" value="<?php echo $rowseg['ep_nmb']; ?>" readonly <?php echo $disabled; ?> >
              <input type="text" class="form-control" name="esquema" value="<?php echo $rowseg['ep_id']; ?>" readonly hidden>
            </div>
            <div class="mb-5 col-md-3">
              <label for=""><i class="icon-calendar"></i> Fecha: </label>
              <input type="text" class="form-control" name="fecha" value="<?php echo fecha_formato(date('Y-m-d'),false,false); ?>" readonly <?php echo $disabled; ?> >
            </div>
            <div class="mb-5 col-xl-1">
              <label for=""><i class="icon-dollar"></i> Cobrar: </label> <br>
              <button type="button" id="cobrar"
                onclick="cobrartck();" 
                class="btn btn-warning text-white" data-toggle="tooltip"
                data-placement="top" title="Cobrar" <?php echo $disabled2; ?>><i class="icon-dollar" ></i> Cobrar
              </button>
              <!-- <button class="btn btn-warning" <?php echo $disabled; ?> ><i class="icon-dollar"></i> Cobrar</button> -->
            </div>
            <div class="mb-5 col-md-2">
              <label for=""><i class="icon-ticket"></i> Tickets: </label> <br>
              <button type="button" id="tickets" onclick="window.open('../popup/ticketspv.php?idcaja=<?php echo $idcaja; ?>','ticketscaja','width=700 height=700');return false" 
              class="btn btn-info" <?php echo $disabled; ?>><i class="icon-ticket"></i> Tickets</button>
            </div>
            <div class="mb-5 col-xl-2" style="" <?php echo $hiddencontra; ?>>
              <label for=""><i class="fa fa-times"></i> Cerrar Caja: </label> <br>
              <button  type="button"
              onclick="cerrarcj();" 
              class="btn btn-danger" <?php echo $disabled; ?> ><i class="fa fa-times"></i> Cerrar Caja</button>
            </div>
            <!-- MODAL ABRIR CAJA -->
            <div class="mb-5 col-xl-2" <?php echo $hidden; ?> >
              <label for=""><i class="icon-open"></i> Abrir Caja: </label> <br>
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-target="#abrircaja" style="box-shadow: 6px 6px 6px 0px black;"><i class="icon-open"></i> Abrir Caja</button>
            </div>
            <!-- MODAL ABRIR CAJA     box-shadow: 0px 0px 8px 4px #37bc9b; -->
          </div>
        </div>
      </div>
      <!-- HEADER -->

      <div class="col-md-12 table-inverse" style="height: 30px;" >
      </div>

      <!-- TABLA PRODUCTOS -->

      <div class="" style="" <?php echo $hiddencontra; ?>>
        <div class="">
          <div class="container">
            <div class="col-md-12 p-0 mt-1">
              <div class="mb-5 col-md-2">
                <img id="mini" src="https://placehold.jp/ffffff/0d0d0d/400x400.png?text=JDCEO" alt="img-fluid" style="max-width: 60%; border-style: groove; padding: 5px;">
              </div>
              <div class="mb-5 col-md-4">
                <label for=""><i class="fa fa-search5"></i> Buscar Producto: </label>
                <input type="text" id="producto" name="producto" placeholder="CB, Nombre, Modelo" class="search_query form-control" required autofocus <?php echo $disabled; ?> >
                <div class="dropdown" id="suggestions" style="overflow: scroll; max-height: 400px;"></div>
              </div>
              <div class="mb-5 col-md-2">
                <label for=""><i class="icon-hashtag"></i> Cantidad: </label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" step="1" min="1" max="9999" value="1" required <?php echo $disabled; ?> >
              </div>
              <div class="mb-5 col-md-2">
                <label for=""><i class="icon-plus"></i> Añadir Producto: </label> <br>
                <button id="add" type="button" onclick="addproducto()" class="btn btn-success text-white" <?php echo $disabled; ?> ><i class="icon-plus"></i> Agregar</button>
              </div>
              <div class="mb-5 col-md-2">
                <label for=""><i class="icon-magic"></i> Limpiar: </label> <br>
                <button type="button" id="limpiarr" onclick="limpiar()" class="btn btn-purple text-white " <?php echo $disabled; ?> ><i class="icon-magic"></i> Limpiar Campos</button>
              </div>
            </div>
            <?php
            if($_GET['ftck']){
              $img = busca($_SESSION['emp'],'empresas','e_id','e_logo');
              $sqldet = 'SELECT ptd_nmbarticulo,ptd_cantidad,ptd_precio FROM pv_ticketsd WHERE ptd_ticket = "'.$_GET['ftck'].'"';
              $resultdet = setq($sqldet);
              while($rowdet = $resultdet->fetch_array()){
                echo '<input type="hidden" class="nmbart" value="'.ucfirst($rowdet['ptd_nmbarticulo']).'"/>';
                echo '<input type="hidden" class="cantart" value="'.number_format($rowdet['ptd_cantidad'],2).'"/>';
                echo '<input type="hidden" class="preart" value="'.number_format($rowdet['ptd_precio'],2).'"/>';
              }
              echo '
              <input type="hidden" id="logo" value="'.$img.'"/>
              <div class="table-responsive p-1 imprimir" id="ticketinfo">
                <table class="table table-bordered table-striped" id="tablainfo">';
                  $funcjs = "window.location.href = \"puntodeventa.php\";";
                  $idticketfin = $_GET['ftck'];
                  $sqlidtck = 'SELECT * FROM pv_tickets WHERE pt_id = "'.$idticketfin.'"';
                  $resultidtck = setq($sqlidtck);
                  $rowidtck = $resultidtck->fetch_array();
                  echo '
                  <input type="hidden" id="fecha" value="'.date('d/m/Y H:i:s',strtotime($rowidtck['pt_fechacobro'])).'"/>
                  <thead>
                    <tr>
                      <th class="text-xs-center" colspan="2" id="txtinfo">
                        Información del Ticket: 
                      </th>
                    </tr>
                  </thead>
                  <tr>
                    <td>Folio: </td>
                    <td class="number-align" id="folio1">'.$rowidtck['pt_folio'].'</td>
                  </tr>
                  <tr>
                    <td>Cajero: </td>
                    <td class="number-align" id="cajero1">'.$rowidtck['pt_cajero'].'</td>
                  </tr>
                  <tr>
                    <td>Subtotal: </td>
                    <td class="number-align" id="subtotal1">$'.number_format($rowidtck['pt_subtotal'],2).'</td>
                  </tr>
                  <tr>
                    <td>IVA: </td>
                    <td class="number-align" id="iva1">$'.number_format($rowidtck['pt_iva'],2).'</td>
                  </tr>
                  <tr>
                    <td>Total: </td>
                    <td class="number-align" id="total1">$'.number_format($rowidtck['pt_total'],2).'</td>
                  </tr>';
                  echo '
                  <thead>
                    <tr>
                      <th class="text-xs-center" colspan="2">
                        Resumen:  
                      </th>
                    </tr>
                  </thead>
                  <tr>
                    <td>Efectivo: </td>
                    <td class="number-align">$'.number_format($rowidtck['pt_efectivo'],2).'</td>
                    <input type="hidden" id="efectivo1" value="'.number_format($rowidtck['pt_efectivo'],2).'"/>
                  </tr>
                  <tr>
                    <td>Tarjeta: </td>
                    <td class="number-align">$'.number_format($rowidtck['pt_tarjeta'],2).'</td>
                    <input type="hidden" id="tarjeta1" value="'.number_format($rowidtck['pt_tarjeta'],2).'"/>
                  </tr>
                  <tr>
                    <td>Vales: </td>
                    <td class="number-align">$'.number_format($rowidtck['pt_vales'],2).'</td>
                    <input type="hidden" id="vale1" value="'.number_format($rowidtck['pt_vales'],2).'"/>
                  </tr>
                  <tr>
                    <td>Transferencia: </td>
                    <td class="number-align">$'.number_format($rowidtck['pt_trans'],2).'</td>
                    <input type="hidden" id="trans1" value="'.number_format($rowidtck['pt_trans'],2).'"/>
                  </tr>
                  <tr>
                    <td>Cheque: </td>
                    <td class="number-align">$'.number_format($rowidtck['pt_cheque'],2).'</td>
                    <input type="hidden" id="cheque1" value="'.number_format($rowidtck['pt_cheque'],2).'"/>
                  </tr>
                  <tr>
                    <td class="bg-light-green bg-accent-4">Cambio: </td>
                    <td class="number-align bg-light-green bg-accent-4">$'.number_format($rowidtck['pt_cambio'],2).'</td>
                    <input type="hidden" id="cambio1" value="'.number_format($rowidtck['pt_cambio'],2).'"/>
                  </tr>'; 
                  echo '
                </table>
              </div>';

            }else{
              echo '
              <div class="col-md-12 bg-white p-0" style="border-style: groove; height: 350px; overflow: scroll;"> <!-- TABLA -->
                <div class="table-responsive" id="tablaprod" bis_skin_checked="1">
                  <table class="table table-striped table-hover mb-0">
                    <thead class="table-inverse text-xs-right">
                      <tr>
                        <th width="5%">Cantidad</th>
                        <th width="50%">Nombre Articulo</th>
                        <th width="20%">Precio Unitario</th>
                        <th width="20%">Importe</th>
                        <th>Borrar</th>
                      </tr>
                    </thead>
                    <tbody>';
                      $sqlprods = 'SELECT * FROM pv_ticketsd WHERE ptd_ticket = "'.$idticket.'" ORDER BY ptd_id DESC ';
                      $sqlcount = 'SELECT SUM(ptd_cantidad) totalprod FROM pv_ticketsd WHERE ptd_ticket = "'.$idticket.'"';
                      $resultcount = setq($sqlcount);
                      $rowcount = $resultcount->fetch_array(); 
                      $resultprods = setq($sqlprods);
                      $total = 0;
                      while($rowprods = $resultprods->fetch_array()){
                        echo '
                        <tr>
                          <th><input type="number" onchange="cambiarcant('.$rowprods['ptd_id'].')" name="prod'.$rowprods['ptd_id'].'" id="prod'.$rowprods['ptd_id'].'" 
                          class="form-control" step="0.01" min="0.01" max="9999" value="'.$rowprods['ptd_cantidad'].'" ></th>
                          <td>'.$rowprods['ptd_nmbarticulo'].'</td>
                          <td>$ '.number_format($rowprods['ptd_precio'],2).'</td>
                          <td>$ '.(number_format($rowprods['ptd_precio']*$rowprods['ptd_cantidad'],2)).'</td>
                          <td><button type="button" onclick="borrarprod('.$rowprods['ptd_articulo'].','.$idticket.')" class="btn btn-danger" id="btntrash"><i class="fa fa-trash"></i></button></td>
                        </tr> ';
                        //class="text-xs-right"   class="number-align"
                        $total = $total+($rowprods['ptd_precio']*$rowprods['ptd_cantidad']);
                      }
                      echo '
                    </tbody>
                  </table>
                </div>
              </div> <!-- TABLA -->';
              ?>
              <div class="table-responsive text-xs-right" id="totales" bis_skin_checked="1"> <!-- Totales --> 
                <table class="table">
                  <tbody class="table-inverse">
                    <tr>
                      <td><h2>Total Productos:  </h2></td>
                      <td class="number-align"><h2><?php echo number_format($rowcount['totalprod'],0); ?></h2></td>
                      <td><h2>Total:  </h2></td>
                      <td class="number-align"><h2>$<?php echo number_format($total,2) ?></h2></td>
                    </tr>
                  </tbody>
                </table>
              </div> <!-- Totales --> 
              <?php
            }
              ?>
          </div>
        </div>
      </div>
      
      <!-- TABLA PRODUCTOS -->
    </form>


    <!-- MODALES -->

    <!-- Modal Abrir Caja-->
    <form action="../query/tablerocaja.php" method="POST">
      <div class="modal fade" id="abrircaja" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Abrir caja para: <?php echo $_SESSION['uid']; ?> </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body row">
              <div class="mb-5 col-md-6">
                <label for="">Efectivo Inicial: </label>
                <input type="number" name="efectivoap" id="efectivoap" step="1" min="0" max="99999999" class="form-control" placeholder="Ingresa el efectivo inicial de la caja" required>
              </div>
              <div class="mb-5 col-md-6">
                <label for="">Cajero: </label>
                <input type="text" class="form-control" value="<?php echo $_SESSION['uid']; ?>" readonly> 
              </div>
              <div class="mb-5 col-md-12">
                <label for="">Fecha de Caja: </label>
                <input type="text" class="form-control" value="<?php echo fecha_formato(date('Y-m-d'),false,false); ?>" readonly>
              </div>
            </div>
            <div class="modal-footer text-xs-center">
              <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
              <button class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
            </div>
          </div>
        </div>
      </div>
    </form>
    <!-- MODALES -->
  </body>

  <!-- BEGIN VENDOR JS-->
  <script src="../js/jquery.min.js"></script>
  <script type="text/javascript" src="js/dynamic_search.js"></script>
  <script src="../js/ui/tether.min.js" type="text/javascript"></script>
  <script src="../js/bootstrap.min.js" type="text/javascript"></script>
  <script src="../js/ui/perfect-scrollbar.jquery.min.js" type="text/javascript"></script>
  <script src="../js/ui/unison.min.js" type="text/javascript"></script>
  <script src="../js/ui/blockUI.min.js" type="text/javascript"></script>
  <script src="../js/ui/jquery.matchHeight-min.js" type="text/javascript"></script>
  <script src="../js/ui/screenfull.min.js" type="text/javascript"></script>
  <script src="../js/pace.min.js" type="text/javascript"></script>

  <!-- BEGIN VENDOR JS-->
  <!-- BEGIN PAGE VENDOR JS-->
  <script src="../js/charts/chart.min.js" type="text/javascript"></script>
  <script src="../js/popover.min.js" type="text/javascript"></script>
  <!-- END PAGE VENDOR JS-->
  <!-- BEGIN ROBUST JS-->
  <script src="../js/jquery.validate.js" type="text/javascript"></script>
  <script src="../js/validation.js" type="text/javascript"></script>
  <script src="../js/script.js"></script>
  <!--
  -->
  <!-- END ROBUST JS-->
  <!-- BEGIN PAGE LEVEL JS
  <script src="js/scripts/pages/dashboard-lite.js" type="text/javascript"></script>  -->
  <!-- END PAGE LEVEL JS-->
  <script src="../js/select2.min.js"></script>
  <script>
    window.onload=function(){
      const valores = window.location.search;
      const urlParams = new URLSearchParams(valores);
      var actividad = urlParams.get('ftck');
      if(actividad !== "undefined" && actividad !== null){
        imprimirticket();
      }
    }
    function imprimirticket(){
      var ficha = document.getElementById("fecha");
      var ficha1 = document.getElementById("folio1");
      var ficha2 = document.getElementById("cajero1");
      var ficha3 = document.getElementById("subtotal1");
      var ficha4 = document.getElementById("iva1");
      var ficha5 = document.getElementById("total1");
      var cambio = document.getElementById("cambio1").value;
      var efectivo = document.getElementById("efectivo1").value;
      var tarjeta = document.getElementById("tarjeta1").value;
      var trans = document.getElementById("trans1").value;
      var vale = document.getElementById("vale1").value;
      var cheque = document.getElementById("cheque1").value;

      var logo = document.getElementById("logo").value;
      nmbart = document.getElementsByClassName("nmbart");
      cantart = document.getElementsByClassName("cantart");
      preart = document.getElementsByClassName("preart");
      var ventimp = window.open(' ', 'Imprimir Ticket');
      ventimp.document.open();
      ventimp.document.write('<html><head><title>' + document.title + '</title>');
      //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
      ventimp.document.write('</head><body >');
      ventimp.document.write('<div style="width:302px;height:200px">');
      ventimp.document.write('<center><img src="../'+logo+'" height="200" width="250px"></center>');
      ventimp.document.write('</div>');
      ventimp.document.write('<table width="302px"><tr>');
      ventimp.document.write('<td width="50%" colspan="3" align="center" style="font-size:14px">FECHA</td></tr>');
      ventimp.document.write('<tr><td width="50%" colspan="3" align="center" style="font-size:12px">'+ficha.value+'</td></tr>');
      ventimp.document.write('<tr><td colspan="3" height="10px"></td></tr>');
      ventimp.document.write('<tr><td width="50%" align="center" style="font-size:14px">Folio:</td><td align="left" colspan="2" style="font-size:14px">'+ficha1.innerHTML+'</td></tr>');
      ventimp.document.write('<tr><td width="50%" align="center" style="font-size:14px">Cajero:</td><td align="left" colspan="2" style="font-size:14px">'+ficha2.innerHTML+'</td></tr>');
      ventimp.document.write('<tr><td colspan="3" height="15px"></td></tr>');
      ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
      ventimp.document.write('<tr><td width="50%" style="font-size:14px">PRODUCTO</td><td align="center" width="20%" style="font-size:14px">CANTIDAD</td><td align="center" width="30%" style="font-size:14px">PRECIO</td></tr>');
      ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
      for(var i=0; i<nmbart.length;i++){
        ventimp.document.write('<tr><td width="50%" style="font-size:12px">'+nmbart[i].value+'</td><td align="center" width="20%" style="font-size:12px">'+cantart[i].value+'</td><td align="center" width="30%" style="font-size:12px">$'+preart[i].value+'</td></tr>');
      }
      ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
      ventimp.document.write('<tr><td colspan="3" height="10px"></td></tr>');
      ventimp.document.write('<tr><td colspan="2" align="left" style="font-size:14px">Método de pago</td><td align="center" style="font-size:14px">Subtotal</td></tr>');
      ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
      if(parseFloat(efectivo) > 0) ventimp.document.write('<tr><td width="50%" colspan="2" align="left" style="font-size:12px">Efectivo</td><td align="right" style="font-size:12px">$'+efectivo+'</td></tr>');
      if(parseFloat(tarjeta) > 0) ventimp.document.write('<tr><td width="50%" colspan="2" align="left" style="font-size:12px">Tarjeta</td><td align="right" style="font-size:12px">$'+tarjeta+'</td></tr>');
      if(parseFloat(vale) > 0) ventimp.document.write('<tr><td width="50%" colspan="2" align="left" style="font-size:12px">Vale</td><td align="right" style="font-size:12px">$'+vale+'</td></tr>');
      if(parseFloat(trans) > 0) ventimp.document.write('<tr><td width="50%" colspan="2" align="left" style="font-size:12px">Transferencia Electronica</td><td align="right" style="font-size:12px">$'+trans+'</td></tr>');
      if(parseFloat(cheque) > 0) ventimp.document.write('<tr><td width="50%" colspan="2" align="left" style="font-size:12px">Cheque</td><td align="right" style="font-size:12px">$'+cheque+'</td></tr>');
      ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
      ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Subtotal:</td><td align="right" style="font-size:14px">'+ficha3.innerHTML+'</td></tr>');
      ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">IVA:</td><td align="right" style="font-size:14px">'+ficha4.innerHTML+'</td></tr>');
      ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Total:</td><td align="right" style="font-size:14px">'+ficha5.innerHTML+'</tr>');
      if(parseFloat(cambio) > 0) ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Cambio:</td><td align="right" style="font-size:14px">$'+cambio+'</tr>');
      ventimp.document.write('<tr><td colspan="3" height="20px"></td></tr>');
      ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:12px">Gracias por su compra</td></tr>');
      ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:12px">Este recibo no es un comprobante fiscal</td></tr>');
      ventimp.document.write('</table></body></html>');
      ventimp.document.close();
      ventimp.focus();
      ventimp.onload = function() {
        //document.boton.submit();
        ventimp.print();
        boton = ventimp.document.getElementsByClassName("action-button");
        console.log("evento onload "+boton.length);
        ventimp.close();
      };
    }

    $(document).ready(function() {
        $('#producto').on('keyup', function() {
          if (event.keyCode == 38 || event.keyCode == 40){
            //console.log('chi');
          }else{
          var key = $(this).val();
          var dataString = 'producto='+key;
        $.ajax({
          type: "POST",
          url: "../query/suggestproducts2.php",
          data: dataString,
          success: function(data) {
 
            //Escribimos las sugerencias que nos manda la consulta
            $('#suggestions').fadeIn(500).html(data);

            //Al hacer click en alguna de las sugerencias
            $('.suggest-element').on('click', function(){
              //Obtenemos la id unica de la sugerencia pulsada
              var id = $(this).attr('id');
              var cb = $(this).attr('value');
              //Editamos el valor del input con data de la sugerencia pulsada
              $('#producto').val($('#'+id).attr('data'));
              //Hacemos desaparecer el resto de sugerencias
              $('#suggestions').fadeOut(500);
              $.ajax({
              type: "POST",
              url: "../query/miniimg.php",
              data: {'cb': cb },
              success: function(dataRTN) {
                var img = document.getElementById('mini');
                img.src = dataRTN
              }
              });
              $('#cantidad').focus();
              $('#cantidad').select();
              return false;
            });
          }
        });
          }
      });
    });

    function borrarprod(idprod,ticket){
      var tabla = document.getElementById('tablaprod');
      $.ajax({
          type: "POST",
          url: "../query/borrarprodpv.php",
          data: {'id': idprod,
                'ticket': ticket},
          success: function(data) {
            $("#tablaprod").load(" #tablaprod");
            $("#totales").load(" #totales");
            $("#btncobro").load(" #btncobro");
          }
        });
    }

    function addproducto(){
      var tabla = document.getElementById('tablaprod');
      var prod = document.getElementById('producto');
      var cantidad = document.getElementById('cantidad');
      var formData = new FormData(document.getElementById('formularioprod'));
      var img = document.getElementById('mini');
      if((prod.value != "" && prod.value != undefined) && (cantidad.value > 0)){
      $.ajax({
          type: "POST",
          url: "../ticketcj.php?idcaja=<?php echo $idcaja.'&idticket='.$idticket; ?>",
          data: formData,
          processData: false,  // tell jQuery not to process the data
          contentType: false,   // tell jQuery not to set contentType
          success: function(data) {
            if(data == '0'){
              alert('Producto inexistente o erroneo');
            }else{
            //  window.location.href="puntodeventa.php";
            <?php echo $funcjs; ?>
            $("#tablaprod").load(" #tablaprod");
            $("#totales").load(" #totales");
            $("#btncobro").load(" #btncobro");
            prod.value = "";
            img.src = "https://placehold.jp/ffffff/0d0d0d/400x400.png?text=JDCEO";
            cantidad.value = 1;
            $('#producto').focus();
            }

          }
        });
      }else{
        alert('Verifica los campos');
      }
    }

    function limpiar(){
      var prod = document.getElementById('producto');
      var cantidad = document.getElementById('cantidad');
      var img = document.getElementById('mini');
      prod.value = "";
      img.src = "https://placehold.jp/ffffff/0d0d0d/400x400.png?text=JDCEO";
      cantidad.value = 1;

    }

    function cambiarcant(prod){
      var elemento = document.getElementById('prod'+prod);
      $.ajax({
        type: "POST",
        url: "../query/cantidad.php?idcaja=<?php echo $idcaja.'&idticket='.$idticket; ?>",
        data: {'id': prod,
              'cantidad': elemento.value},   // tell jQuery not to set contentType
        success: function(data) {
          $("#tablaprod").load(" #tablaprod");
          $("#totales").load(" #totales");
          $("#btncobro").load(" #btncobro");
        }
      });

    }

    //var x = -1;// Obtener la lista, para recorrer cada elemento
      let listGroup = document.getElementById('suggestions');
      // Asignar evento al campo de texto
      document.querySelector('#producto').addEventListener('keydown', e => {
          if(!listGroup) {
              return; // No existe la lista
          }

          console.log(listGroup);
          // Obtener todos los elementos
          let items = listGroup.querySelectorAll('a');

          // Saber si alguno está activo
          let actual = Array.from(items).findIndex(item => item.classList.contains('active'));
          //let actual = 0;
          // Analizar tecla pulsada
          //console.log(actual);
          //console.log(items[actual]);
          if(e.keyCode == 13) {
              // Tecla Enter, evitar que se procese el formulario
              e.preventDefault();
              // ¿Hay un elemento activo?
              if(items[actual]) {
                  // Hacer clic
                  items[actual].click();
              }
          } if(e.keyCode == 38 || e.keyCode == 40) {
              // Flecha arriba (restar) o abajo (sumar)
              if(items[actual]) {
                  // Solo si hay un elemento activo, eliminar clase
                  items[actual].classList.remove('active');
                  //items[actual].className += " active";
              }
              // Calcular posición del siguiente
              actual += (e.keyCode == 38) ? -1 : 1;
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
      listGroup.querySelectorAll('a').forEach(a => {
          a.addEventListener('click', e => {
              // Asignar valor al campo
              document.querySelector('#producto').value = e.currentTarget.textContent;
              // Aquí deberías cerrar la lista y/o eliminar el contenido
          });
      });

    /////////

    $('body').on("keydown", function(e) { 
      if (e.altKey && e.which === 88) {
        let listGroup = document.getElementById('tablaprod');
        let items = listGroup.querySelectorAll('button');
        items[0].click();
        e.preventDefault();
      }
    });

    $('body').on("keydown", function(e) { 
      if (e.altKey && e.which === 67) {
        var cobrar = document.getElementById('cobrar');
        cobrar.click();
        e.preventDefault();
      }
    });

    $('body').on("keydown", function(e) { 
      if (e.altKey && e.which === 76) {
        var limpiarr = document.getElementById('limpiarr');
        limpiarr.click();
        e.preventDefault();
      }
    });

    $('body').on("keydown", function(e) { 
      if (e.altKey && e.which === 66) {
        $('#producto').focus();
      }
    });

    $('body').on("keydown", function(e) { 
      if (e.altKey && e.which === 84) {
        var ticket = document.getElementById('tickets');
        ticket.click();
        e.preventDefault();
      }
    });

    $('#cantidad').on("keydown", function(e) { 
      if(event.keyCode == 13){
        var btn = document.getElementById('add');
        btn.click();
      }else{

      }
    });

    function cerrarcj(){
      $( "#producto" ).prop( "disabled", true );
      $( "#cantidad" ).prop( "disabled", true );
      $( "#add" ).prop( "disabled", true );
      $( "#tickets" ).prop( "disabled", true );
      $( "#cobrar" ).prop( "disabled", true );
      //window.open('../popup/cerrarcajapv.php?idcaja=<?php echo $idcaja; ?>','cerrarcaja','width=500 height=600');
      var win = window.open('../popup/cerrarcajapv.php?idcaja=<?php echo $idcaja; ?>','cerrarcaja','width=500 height=600');  
      var timer = setInterval(function() {   
          if(win.closed) {  
              clearInterval(timer);  
              //alert('closed');  
              //window.location.reload();
              window.location.href = "puntodeventa.php";
          }  
      }, 10); 
      return false;

    }

    function cobrartck(){
      $( "#producto" ).prop( "disabled", true );
      $( "#cantidad" ).prop( "disabled", true );
      $( "#add" ).prop( "disabled", true );
      $( "#tickets" ).prop( "disabled", true );
      $( "#cobrar" ).prop( "disabled", true );
      $( "#btntrash" ).prop( "disabled", true );
      var win = window.open('../popup/cobrarpv.php?idcaja=<?php echo $idcaja.'&idticket='.$idticket; ?>','cobrar','width=400 height=600');  
      var timer = setInterval(function() {   
          if(win.closed) {  
              clearInterval(timer);  
              //alert('closed');  
              //window.location.reload();
              $( "#producto" ).prop( "disabled", false );
              $( "#cantidad" ).prop( "disabled", false );
              $( "#add" ).prop( "disabled", false );
              $( "#tickets" ).prop( "disabled", false );
              $( "#cobrar" ).prop( "disabled", false );
              $( "#btntrash" ).prop( "disabled", false );
          }  
      }, 10); 
      return false;
      

    }

  </script>

</html>