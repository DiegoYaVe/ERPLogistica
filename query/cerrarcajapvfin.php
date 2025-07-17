<?php
//ini_set('display_errors',1);
session_start();
include('../funciones.php');
//foreachdie();
include('../modulos/remisiones.php');
include_once('../modulos/cxcobrar.php');
$mdremisiones = new modelremisiones();
$cuentaxc = new modelcxcobrar(); 

$idcaja = $_POST['idcaja'];
$efectivotot = $_POST['efectivotot'];
//$efectivosf = $_POST['efectivosf'];
$efectivosf = $_POST['efectivo'];
$tarjeta = $_POST['tarjeta'];
/* $vales = $_POST['vales'];
$transferencia = $_POST['transferencia'];
$cheque = $_POST['cheque'];
$totalfinal = $_POST['total'];
$totalsf = $_POST['totaltotsf'];
$tablero = busca($idcaja,'pv_tableroscaja','tc_id','tc_tablero');
$cliente = busca($tablero, 'crm_tableros','ct_id','ct_cliente');
$correo = busca($cliente,'crm_clientes','c_id','c_correo1');
//$almacen = busca($_SESSION['emp'],'almacenes','a_estatus = "A" AND a_empresa','a_id');
$almacen = busca($idcaja,'pv_tableroscaja','tc_id','tc_almacen');
$nmb = 'CORTE CAJA '.$idcaja.' - '.$_SESSION['uid'].'';
$sqltickets  = 'SELECT * FROM pv_tickets WHERE pt_caja = "'.$idcaja.'" AND pt_estatus = "N"';
$resulttickets = setq($sqltickets);
while($rowtickets = $resulttickets->fetch_array()){
  $sqltdelete = 'DELETE FROM pv_ticketsd WHERE 
                ptd_ticket = "'.$rowtickets['pt_id'].'"';
  setq($sqltdelete);
}

$sqldelete = 'DELETE FROM pv_tickets WHERE
pt_caja = "'.$idcaja.'" AND pt_estatus = "N"';
setq($sqldelete); */

include_once('../modulos/cuentas.php');
$cuentas = new modelcuentas();
$estatus = busca($idcaja, 'cajas', 'c_id', 'c_estatus');
if($estatus == "A"){
  /* $falt = busca($idcaja, 'cajasd' ,'cd_estatus = "P" AND cd_caja', 'COUNT(*)');
  if($falt > 0){
    echo '<script>
      alert("Error. Existen abonos sin confirmar.");
    </script>';
  }else{ */
    $ingresos = busca($idcaja, 'cajas_movimientos', 'cm_tipo = "I" AND cm_caja', 'SUM(cm_monto)');
    $retiros = busca($idcaja, 'cajas_movimientos', 'cm_tipo = "G" AND cm_caja', 'SUM(cm_monto)');
    $efe = busca($idcaja, 'cajasd', 'cd_caja','SUM(cd_efectivo)');
    $tar = busca($idcaja, 'cajasd', 'cd_caja','SUM(cd_tarjeta)');
    
    $total = $efectivosf + $tarjeta;
    $sql = 'UPDATE cajas SET c_estatus = "C",
                          c_ffin = "'.date('Y-m-d').'",
                          c_hfin = "'.date('H:i:s').'",
                          c_ufin = "'.$_SESSION['uid'].'",
                          c_efectivo = "'.$efectivosf.'",
                          c_tarjeta = "'.$tarjeta.'",
                          c_total = "'.$total.'"
                          WHERE c_id = "'.$idcaja.'"';
    setq($sql);

    $slqinstab = 'INSERT INTO caja_tabulador SET ct_caja = "'.$idcaja.'",
                                                ct_1000 = "'.$_POST['1000'].'",
                                                ct_500 = "'.$_POST['500'].'",
                                                ct_200 = "'.$_POST['200'].'",
                                                ct_100 = "'.$_POST['100'].'",
                                                ct_50 = "'.$_POST['50'].'",
                                                ct_20 = "'.$_POST['20'].'",
                                                ct_10 = "'.$_POST['10'].'",
                                                ct_5 = "'.$_POST['5'].'",
                                                ct_2 = "'.$_POST['2'].'",
                                                ct_1 = "'.$_POST['1'].'",
                                                ct_50c = "'.$_POST['50c'].'"';  
    setq($slqinstab);

    $totalefe = $efe + $ingresos - $retiros;

    $corte = busca('A', 'cortes', 'c_cuenta = "1" AND c_estatus', 'c_id');
    $sqlins= 'INSERT INTO cortesd SET cd_corte = "'.$corte.'", cd_fecha = "'.date('Y-m-d H:i:s').'",
                                      cd_gen = "'.$_SESSION['uid'].'", cd_tipo = "C", cd_idref = "'.$idcaja.'",
                                      cd_monto = "'.$totalefe.'", cd_observaciones = "CORTE DE CAJA '.$idcaja.' '.date('Y-m-d H:i:s').'",
                                      cd_tipom = "C", cd_cuenta = "1"';
    setq($sqlins);
    
    $cuentas->actualizarsaldo('1', saldo_actual('1'));

    /* $sql= 'SELECT * FROM cajasd WHERE cd_caja = "'.$idcaja.'" AND cd_fpago IN ("18", "4")';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      $folio = busca($row['cd_remision'], 'remisiones', 'r_id', 'r_folio');
      $sqlcor= 'INSERT INTO cortesd SET cd_corte = "'.$row['cd_ugen'].'", cd_fecha = "'.$row['cd_fgen'].'",
                                        cd_gen = "'.$_SESSION['uid'].'", cd_tipo = "C", cd_idref = "'.$row['cd_remision'].'",
                                        cd_monto = "'.$row['cd_monto'].'", cd_observaciones = "INGRESO A REMISIÓN '.$folio.'",
                                        cd_tipom = "R", cd_cuenta = "'.$row['cd_cuenta'].'"';
      setq($sqlcor);
    } */
  //}
  ?>
  <script src="../assets/js/jquery.min.js" type="text/javascript"></script>
  <script>
    function imprimirticket(idcaja){
      //console.log('entro a la funcion');
      $.ajax({
        type: "POST",
        url: "ticketcortecaja.php",
        data: {"idcaja": idcaja},
        success: function(data){
          //console.log(data);
          var datax = JSON.parse(data);
          var ventimp = window.open('about:blank', 'ImprimirTicket');
          ventimp.document.open();
          ventimp.document.write('<html><head><title>' + document.title + '</title>');
          //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
          ventimp.document.write('</head><body>');
          ventimp.document.write('<div style="width:402px;height:200px">');
          ventimp.document.write('<center><img src="'+datax['logo']+'" height="200" width="250px" alt="'+datax['logo']+'"></center>');
          ventimp.document.write('</div>');
          ventimp.document.write('<table width="402px"><tr>');
          ventimp.document.write('<td width="50%" colspan="2" align="center" style="font-size:20px">FECHA DEL CORTE</td></tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="center" style="font-size:18px">'+datax['fecha']+'</td></tr>');
          ventimp.document.write('<tr><td colspan="2" height="10px"></td></tr>');
          ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Apertura:</td><td align="left" colspan="2" style="font-size:20px">'+datax['uapertura']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Cierre:</td><td align="left" colspan="2" style="font-size:20px">'+datax['ucierre']+'</td></tr>');
          ventimp.document.write('<tr><td colspan="2" height="15px"></td></tr>');
          ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="100%" colspan="2" align="center" style="font-size:20px">TABULADOR</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">VALOR</td><td align="center" width="20%" style="font-size:20px">CANTIDAD</td></tr>');
          ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">1000</td><td align="center" width="50%" style="font-size:20px">'+datax['1000']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">500</td><td align="center" width="50%" style="font-size:20px">'+datax['500']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">200</td><td align="center" width="50%" style="font-size:20px">'+datax['200']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">100</td><td align="center" width="50%" style="font-size:20px">'+datax['100']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">50</td><td align="center" width="50%" style="font-size:20px">'+datax['50']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">20</td><td align="center" width="50%" style="font-size:20px">'+datax['20']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">10</td><td align="center" width="50%" style="font-size:20px">'+datax['10']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">5</td><td align="center" width="50%" style="font-size:20px">'+datax['5']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">2</td><td align="center" width="50%" style="font-size:20px">'+datax['2']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">1</td><td align="center" width="50%" style="font-size:20px">'+datax['1']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">50c</td><td align="center" width="50%" style="font-size:20px">'+datax['50c']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Total: </td><td align="center" width="50%" style="font-size:20px">'+datax['sumtab']+'</td></tr>');
          ventimp.document.write('<tr><td colspan="2" height="5px"></td></tr>');
          ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="100%" colspan="2" align="center" style="font-size:20px">EFECTIVO</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Calculado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['efesis']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Contado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['efecap']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Retirado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['eferetiro']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Diferencia:</td><td align="left" width="50%" style="font-size:20px">$'+datax['efedif']+'</td></tr>');
          ventimp.document.write('<tr><td colspan="2" height="5px"></td></tr>');
          ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="100%" colspan="2" align="center" style="font-size:20px">TARJETA</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Calculado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['tarsis']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Contado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['tarcap']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Retirado:</td><td align="left" width="50%" style="font-size:20px">$0.00</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Diferencia:</td><td align="left" width="50%" style="font-size:20px">$'+datax['tardif']+'</td></tr>');
          ventimp.document.write('<tr><td colspan="2" height="5px"></td></tr>');
          ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="100%" colspan="2" align="center" style="font-size:20px">TOTAL</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Calculado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['totalsis']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Contado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['totalcap']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Retirado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['eferetiro']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:20px">Diferencia:</td><td align="left" width="50%" style="font-size:20px">$'+datax['totaldif']+'</td></tr>');
          ventimp.document.write('</table></body></html>');
          ventimp.document.close();
          ventimp.focus();

          ventimp.addEventListener('load', function () {
            //ventimp.document.open(); 
              window.close(); 
              ventimp.print();
              // Realiza las operaciones en el documento de la ventana emergente aquí
              ventimp.document.close();
          });
        }
      });
    }

    imprimirticket(<?php echo $idcaja; ?>);

    opener.location.reload();
    
  </script>
  <?php
} else {
  echo '<script>
    alert("ERROR. La caja ya esta cerrada");
  </script>';
}


