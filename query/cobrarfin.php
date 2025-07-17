<?php
//ini_set('display_errors',1);
session_start();
include('../funciones.php');
//foreachdie();

$total = $_POST['total'];
$efectivo = $_POST['efectivo'];
$tarjeta = $_POST['tarjeta'];
$remision = $_REQUEST['remision'];
$caja = $_REQUEST['caja'];
$abonado = $_REQUEST['abonado'];
$efectivorec = $_POST['efectivorec'];
$folio = busca($remision, 'remisiones', 'r_id', 'r_folio');
$pagado = $efectivo+$tarjeta;
$subtotal = $pagado + $abonado;
$cambio = $efectivorec - $efectivo;
$idcxc = busca($remision, 'cxcobrar', 'cx_referencia', 'cx_id');
$sqlb = 'SELECT COUNT(*) FROM ingresos WHERE i_id IN (SELECT ic_ingreso FROM ingreso_cxcobrar WHERE ic_cxcobrar ="'.$idcxc.'") AND i_estatus="P"';
$resultb = setq($sqlb);
$sqlcaja = 'SELECT COUNT(*) FROM cajasd WHERE cd_remision  = "'.$remision.'" AND cd_estatus="A"';
$resultcaja = setq($sqlcaja);
list($numcountcj) = $resultcaja -> fetch_array();
$sqlcount = 'SELECT COUNT(*) FROM ingreso_cxcobrar WHERE ic_cxcobrar = "'.$idcxc.'"'; 
$resultcount = setq($sqlcount);
list($numcount) = $resultcount  -> fetch_array();
$sqlrem = 'SELECT COUNT(*) FROM remisionesc INNER JOIN articulos ON a_id = rc_articulo WHERE rc_remision = "'.$remision.'" AND  (a_categoria = "2" OR a_categoria = "4")';
$resultrem = setq($sqlrem);
list($numinf) = $resultrem -> fetch_array();
if($numinf >= 1){
  if($numcount >= 1 || $numcountcj >=1) $min = 0;
  else $min = 1000;
} else {
  $min = 0;
}
if($pagado < $min){
  echo '<script>
  alert("Ingreso invalido, verifica los montos.");
  window.close();
  </script>';
  die();
} 
$valida= busca($idcxc, 'ingresos INNER JOIN ingreso_cxcobrar ON i_id = ic_ingreso INNER JOIN cxcobrar ON cx_id = ic_cxcobrar', 'i_estatus = "F" AND cx_id', 'COUNT(*)');
$valida2 = busca($remision, 'cajasd', 'cd_estatus = "A" AND cd_remision', 'COUNT(*)');
if(intval($valida) == 0 && intval($valida2) == 0){
  include_once('../modulos/remisiones.php');
  $rem = new modelremisiones();
  $rem -> aplicar($remision);
  $sqlupd = 'UPDATE remisiones SET r_estatus = "A", r_uaplica="'.$_SESSION['uid'].'", r_faplica="'.date('Y-m-d H:i:s').'"  WHERE r_id = "'.$remision.'"';
  setq($sqlupd);
}
//$cambio = $pagado-$total;
$sql = 'INSERT INTO cajasd SET 
  cd_caja = "'.$caja.'",
  cd_ugen="'.$_SESSION['uid'].'",
  cd_fgen="'.date('Y-m-d H:i:s').'",
  cd_remision = "'.$remision.'",
  cd_total = "'.$pagado.'",
  cd_efectivo = "'.floatval($efectivo).'",
  cd_cambio = "'.floatval($cambio).'",
  cd_tarjeta = "'.floatval($tarjeta).'",
  cd_fconfirma = "'.date('Y-m-d H:i:s').'",
  cd_uconfirma = "'.$_SESSION['uid'].'",
  cd_estatus = "A"';
setq($sql);  

$idcd = getmax('cd_id', 'cajasd', false, false);

if($tarjeta > 0 ){
  $cuenta = busca('1', 'cuentas', 'cu_caja', 'cu_id');
  $corte = busca($cuenta, 'cortes', 'c_estatus ="A" AND c_cuenta', 'c_id');
  $sqlins = 'INSERT INTO cortesd SET cd_corte="'.$corte.'",
                                      cd_fgen="'.date('Y-m-d H:i:s').'",
                                      cd_ugen = "'.$_SESSION['uid'].'",
                                      cd_tipo = "C",
                                      cd_monto = "'.$tarjeta.'",
                                      cd_tipom = "C",
                                      cd_cuenta = "'.$cuenta.'",
                                      cd_observaciones = "Remisión '.$folio.'"
                                       ';
  setq($sqlins);                                       
}

if($total == $subtotal || $subtotal > $total) { 
  if($pend > 0){
    $sqlupd = 'UPDATE cxcobrar SET cx_estatus ="A", cx_abonado = "'.$subtotal.'" WHERE cx_id = "'.$idcxc.'"';
    setq($sqlupd);
    $sqlupd2 = 'UPDATE remisiones SET r_estatus = "A"  WHERE r_id = "'.$remision.'"';
    setq($sqlupd2);
  } else {
    $sqlupd = 'UPDATE cxcobrar SET cx_estatus ="F", cx_abonado = "'.$subtotal.'", cx_ffin = "'.date('Y-m-d H:i:s').'" WHERE cx_id = "'.$idcxc.'"';
    setq($sqlupd);
    $sqlupd2 = 'UPDATE remisiones SET r_estatus = "F"  WHERE r_id = "'.$remision.'"';
    setq($sqlupd2);
    $sqlremc = 'UPDATE remisionesc SET rc_estatus = "P" WHERE rc_estatus = "N" AND rc_tipoenvio = "C" AND rc_remision = "'.$remision.'"';
    setq($sqlremc);
    $sqlremc2 = 'UPDATE remisionesc SET rc_estatus = "A" WHERE rc_estatus = "N" AND rc_tipoenvio IN ("D", "O") AND rc_remision = "'.$remision.'"';
    setq($sqlremc2);
  }
}elseif($total > $subtotal){
  $sqlupd = 'UPDATE cxcobrar SET cx_estatus ="A", cx_abonado = "'.$subtotal.'" WHERE cx_id = "'.$idcxc.'"';
  setq($sqlupd);
  $sqlupd2 = 'UPDATE remisiones SET r_estatus = "A"  WHERE r_id = "'.$remision.'"';
  setq($sqlupd2);
}

?>
  <script src="../assets/js/jquery.min.js" type="text/javascript"></script>
  <script>
    function imprimirticket(idcd){
      //console.log('entro a la funcion');
      $.ajax({
        type: "POST",
        url: "ticketventa.php",
        data: {"idcd": idcd},
        success: function(data){
          console.log(data);
          var datax = JSON.parse(data);
          var nmbart = datax['articulos'].split('/');
          var cantart = datax['cantidad'].split('/');
          var preart = datax['precio'].split('/');
          var ventimp = window.open('about:blank', 'ImprimirTicket');
          ventimp.document.open();
          ventimp.document.write('<html><head><title>' + document.title + '</title>');
          //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
          ventimp.document.write('</head><body>');
          ventimp.document.write('<div style="width:302px;height:200px">');
          ventimp.document.write('<center><img src="'+datax['logo']+'" height="200px" width="200px" alt="'+datax['logo']+'">');
          ventimp.document.write('</div>');
          ventimp.document.write('<div width="100%" style="font-size:14px">'+datax['direccion']+'</div>');
          ventimp.document.write('<div width="100%" style="font-size:14px">'+datax['telefono']+'</div></center>');
          ventimp.document.write('<table width="302px"><tr>');
          ventimp.document.write('<td width="50%" colspan="3" align="center" style="font-size:14px">FECHA</td></tr>');
          ventimp.document.write('<tr><td width="50%" colspan="3" align="center" style="font-size:12px">'+datax['fecha']+'</td></tr>');
          ventimp.document.write('<tr><td colspan="3" height="10px"></td></tr>');
          ventimp.document.write('<tr><td width="50%" align="center" style="font-size:14px">Folio:</td><td align="left" colspan="2" style="font-size:14px">'+datax['folio']+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" align="center" style="font-size:14px">Genera:</td><td align="left" colspan="2" style="font-size:14px">'+datax['genera']+'</td></tr>');
          ventimp.document.write('<tr><td colspan="3" height="15px"></td></tr>');
          ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:14px">PRODUCTO</td><td align="center" width="20%" style="font-size:14px">CANTIDAD</td><td align="center" width="30%" style="font-size:14px">PRECIO</td></tr>');
          ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
          for(var i=0; i<nmbart.length;i++){
            ventimp.document.write('<tr><td width="50%" style="font-size:12px">'+nmbart[i]+'</td><td align="center" width="20%" style="font-size:12px">'+cantart[i]+'</td><td align="center" width="30%" style="font-size:12px">$'+preart[i]+'</td></tr>');
            ventimp.document.write('<tr><td colspan="3" height="5px"></td></tr>');
          }
          ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Efectivo recibido:</td><td align="right" style="font-size:14px">$'+datax['efectivorec']+'</tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Efectivo cobrado:</td><td align="right" style="font-size:14px">$'+datax['efectivo']+'</tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Cambio:</td><td align="right" style="font-size:14px">$'+datax['cambio']+'</tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Tarjeta:</td><td align="right" style="font-size:14px">$'+datax['tarjeta']+'</tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Total cobrado:</td><td align="right" style="font-size:14px">$'+datax['totalcd']+'</tr>');
          ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="100%" colspan="3" align="center" style="font-size:14px">CUENTA</td></tr>');
          if(datax['descuentop'] != "0.00"){
            ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">% Descuento:</td><td align="right" style="font-size:14px">'+datax['descuentop']+'</td></tr>');
            ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Subtotal:</td><td align="right" style="font-size:14px">$'+datax['subtotal']+'</td></tr>');
            ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Descuento:</td><td align="right" style="font-size:14px">$'+datax['descuento']+'</td></tr>');
          }
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Total:</td><td align="right" style="font-size:14px">$'+datax['totalrem']+'</tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Abonado:</td><td align="right" style="font-size:14px">$'+datax['abonado']+'</tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Saldo:</td><td align="right" style="font-size:14px">$'+datax['saldo']+'</tr>');
          
          ventimp.document.write('<tr><td colspan="3" height="20px"></td></tr>');
          ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:18px">Gracias por su compra</td></tr>');
          ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:18px">Este recibo no es un comprobante fiscal</td></tr>');
          ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:20px">'+datax['texto']+'</td></tr>');
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

    imprimirticket(<?php echo $idcd; ?>);

    opener.location.reload();
    
  </script>