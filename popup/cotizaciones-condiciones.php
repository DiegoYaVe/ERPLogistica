<?php
  session_start();
  ini_set('display_errors',0);
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
  include('../funciones.php');
  
  $idg = $_GET['id'];

  $sql = 'SELECT * FROM crm_cotizaciones WHERE cc_id = "'.$idg.'" ';
  $result = setq($sql);
  $row = $result->fetch_array();
  $id = $row['cc_id'];
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

    <body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="menu-collapsed vertical-layout vertical-menu 2-columns " style="mt-2">
      <?php
        if(!isset($_POST['busca'])){
          $_POST['busca']="";
          $autof1 = 'autofocus';
          $autof2 = '';
        }else{
          $autof1 = "";
          $autof2 = 'autofocus onfocus="this.select();"';
        }

        if(isset($_POST['producto']) AND isset($_POST['cantidad'])){
          $producto = busca($_POST['producto'],'productos','p_id','p_nmb');
          $precio = busca($_POST['producto'],'productos','p_id','p_precio');
          $sqlm = 'SELECT MAX(cd_id) FROM cotizacionesd WHERE cd_cotizacion = "'.$_GET['id'].'"';
          $resultm = mysql_query($sqlm) or die($sqlm);
          list($id) = mysql_fetch_array($resultm);
          $id++;
          $sqli = 'INSERT INTO cotizacionesd SET
                  cd_id = "'.$id.'",
                  cd_cotizacion = "'.$_GET['id'].'",
                  cd_producto = "'.$_POST['producto'].'",
                  cd_productonmb = "'.$producto.'",
                  cd_cantidad = "1",
                  cd_precio = "'.$precio.'",
                  cd_iva = "1",
                  cd_tipo = "P"';
          mysql_query($sqli) or die($sqli);

          $sqldd = 'SELE';

          $sqldd = 'SELECT * FROM productos_extra WHERE pe_producto = "'.$_POST['producto'].'"';
          $resultdd = mysql_query($sqldd) or die($sqldd);
          while($rowdd = mysql_fetch_array($resultdd)){
            $sqlm = 'SELECT MAX(ce_id) FROM cotizacionesd_extra
                    WHERE ce_idd = "'.$id.'" AND ce_cotizacion = "'.$_GET['id'].'"';
            $resultm = mysql_query($sqlm) or die($sqlm);
            list($idce) = mysql_fetch_array($resultm);
            $idce++;

            $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
            $simboldi = array('\"',"\'","\#","\$","\%", "\&","\/","\(","\)","\=","\?","\¡","\*","\+","\~","\^","\[","\°","\|","\{","\}","\[","\]");
            $extra = str_replace($simbol,$simboldi, mb_strtoupper(trim($rowdd['pe_detalle'])));

            $sqlin = 'INSERT INTO cotizacionesd_extra SET
                      ce_id = "'.$idce.'",
                      ce_cotizacion = "'.$_GET['id'].'",
                      ce_idd = "'.$id.'",
                      ce_descripcion = "'.$extra.'"';
            mysql_query($sqlin) or die($sqlin);
          }

          echo '<script>
            opener.location.reload();
            window.close();
          </script>';
        }
        //<form id="formnew" method="post" action="buscaprod?modulo='.$_GET['modulo'].'&accion='.$_GET['accion'].'&id='.$_GET['id'].'">

        echo '<script>
          function cierrarpop(){
            //window.close();
            window.location.reload();
          }
          function guardarcondi(){
            document.getElementById("guardar").value = "Guardando";
            document.getElementById("guardar").disabled = true;
            document.condi.submit();
            return false;
          }
          function seleccionarcondiciones(){
            document.getElementById("guardar").value = "Guardando";
            document.getElementById("guardar").disabled = true;
            document.setcondiciones.submit();
            return false;
          }
          function mostraradd(){
            document.getElementById("nuevacondi").removeAttribute("hidden");
            $("#addmostrar").prop("hidden","true");
          }
        </script>
        <script>
          function cerrar(){
            opener.location.reload();
            window.close();
          }

          window.onbeforeunload = opener.location.reload();
        </script>';

        echo'<div class="container-fluid">
          <div class="row">
          <div id="nuevacondi" class="col-md-7 col-7 form-inline mt-2" hidden>
              <form name="condi1" id=""  autocomplete="off" method="post" action="../index?modulo=cotizaciones&accion=nuevacondicion&idc='.$_GET['id'].'">
                <b><p>Agregar Nueva Condicion al Catalogo</p></b>
                <input type="text" style="max-width: 80%;" class="form-control form-control-sm" name="nomc" autocomplete="off" required>
                <button class="btn btn-success text-white" id="add" ><i class="fa fa-save"></i> Guardar</button>
              </form>
            </div>';
            $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
            $gruposPermi = ['ADMIN', 'GERENTE' ,'SUBGERENTE'];
            echo '<div class="col-md-7 col-7 form-inline ">';
            if(in_array($grupo, $gruposPermi))
            echo '
              <button class="btn btn-primary text-white mt-4" id="addmostrar" onclick="mostraradd();" ><i class="fas fa-plus"></i> Agregar condición</button>
              <b><p>Filtrar Condicion Por Nombre</p></b>
              <input type="text" style="max-width: 100%;" class="form-control form-control-sm" name="nmb" id="nmb" autocomplete="off" autofocus>';
            echo '</div>';
            //Condiciones comerciales ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            echo '<div class="col-md-5 col-5">
              <div class="table-responsive" style="position: fixed; overflow: scroll; max-height: 100vh; background: white;">
                <table class="table table-bordered table-striped">
                  <thead class="thead-active bg-primary text-white">
                    <tr class="bg-light-blue bg-darken-2 text-white">
                      <th colspan="3" style="text-align: right">
                        <button id="guardar" onclick="guardarcondi();" role="button" name="save" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
                        <button type="button" onclick="cerrar();"class="btn btn-info"><i class="fa fa-times"></i> Cerrar</button>
                      </th>
                    </tr>
                    <th colspan="3" class="p-1"><b>Condiciones Comerciales</b>
                    <!-- <a href="popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$id.'" 
                    onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false" class="btn btn-success" id="xbuscar"> Agregar</a> --> </td> 
                    </th>
                  </thead>
                  <tbody>
                    <th>Estatus</th>
                    <th>Descripcion</th>
                    <th></th>';
                    $id;
                    echo '<tr>
                      <form name="condi" id="condi" autocomplete="off" method="post" action="../index?modulo=cotizaciones&accion=insertnota&idc='.$_GET['id'].'">
                        <td><input type="checkbox"  name="estatus" checked " ></td>
                        <td><textarea class="form-control" name="condicion" id="" rows="3" placeholder="Incluir nota" required>'.$row['cf_descripcion'].'</textarea>
                        <td></td>
                      </form>
                    </tr>';
                    //$selectcondiciones($_GET['id']);
                    $sql='SELECT * FROM  crm_cotizaciones_condiciones WHERE cf_cotizacion = "'.$id.'" order by cf_orden';
                    $result = setq($sql);
                    while($row= $result -> fetch_array()){
                      $obliga = busca($row['cf_condicion'], 'condicionesc', 'c_id', 'c_obligatorio');
                      if($obliga == "1") $check = 'onclick="return false;"';
                      else $check = "";
                      echo '<tr>';
                        echo '<form autocomplete="off" method="post" action="../index?modulo=cotizaciones&accion=actualizacondicion&id='.$row['cf_id'].'&idc='.$_GET['id'].'" >';
                          if($row['cf_estatus'] == "I") echo '<td ><input type="checkbox"  name="estatus" onchange="submit()" '.$check.'></td>';
                          if($row['cf_estatus'] == "A") echo '<td ><input type="checkbox" name="estatus" checked onchange="submit()" '.$check.'></td>';//style="width:60px;height:15px"
                          echo '<td><textarea class="form-control" name="condicion" id="" rows="3" onchange="submit()" required>'.$row['cf_descripcion'].'</textarea></td>';
                          if($obliga != "1")
                          echo '<td>
                            <a href="../index?modulo=cotizaciones&accion=deletecondicion&id='.$row['cf_id'].'&idc='.$_GET['id'].'" type="button" class="btn btn-danger btn-sm" id="borrar">
                              <i class="fas fa-trash"></i> Borrar
                            </a>
                          </td>';
                        echo '</form>
                      </tr>';
                    }
                  echo '</tbody>
                </table>
              </div>
            </div>';
            echo '<div class="col-md-7 col-7">
              <div id="queryin">
              </div>
              <table width="table table-border table-striped" border="0" class="lista">
                <thead>
                  <tr>
                    <input type="hidden" id="cotiza" value="'.$_GET['id'].'" />
                    <th>
                    </th>
                  </tr>
                </thead>
              </table>
              <form id="setcondiciones" name="setcondiciones" autocomplete="off" method="POST" action="../index?modulo=cotizaciones&accion=seleccionarcondiciones&idc='.$_GET['id'].'">      
                <div id="datos">
                </div>
              </form>
              </tr></thead>
            </div>';
          echo '</div>
        </div>';
        //////////////////////////////////////////////////////////////////////////////////////////////////////////
      ?>
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
      <!-- -->
      <!-- END ROBUST JS-->
      <!-- BEGIN PAGE LEVEL JS
      <script src="js/scripts/pages/dashboard-lite.js" type="text/javascript"></script>  -->
      <!-- END PAGE LEVEL JS-->
      <script src="../js/select2.min.js"></script>

      <script>
        $('[data-fancybox]').fancybox({
          toolbar  : false,
          smallBtn : true,
          iframe : {
            preload : false
          }
        })
      </script>
    </body>
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <!--<footer class="footer footer-light footer-light navbar-border">
      <p class="clearfix text-muted text-sm-center mb-0 px-2"><span class="float-md-left d-xs-block d-md-inline-block">JD CEO - CRM </span><span class="float-md-right d-xs-block d-md-inline-block">Desarrollado por <a href="https://tye-solutions.com" target="_blank" class="text-bold-800 grey darken-2">T&E Innovate::Develop </a></span></p>
    </footer>-->
  </html>