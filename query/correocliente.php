<?php
ini_set('display_errors', 1);
$cotizacion = $_POST['cotizacion']; //ID de la cotización   
$cotizacion = 63;
$_GET['idcotiza'] = $cotizacion;

include('../funciones.php');
require_once("../lib/mailer/class.phpmailer.php");
require_once("../lib/mailer/class.smtp.php");
include_once('../formats/pdfcotizacion_save.php');
/* $_POST['cotizacion'] = 22; */

$sqlco = 'SELECT * FROM crm_cotizaciones WHERE cc_id = "' . $cotizacion . '"';
$resultco = setq($sqlco);
$rowco = $resultco->fetch_array();

$mail = new PHPMailer(); // defaults to using php "mail()"
$asunto = 'COTIZACIÓN ' . $rowco['cc_folio'];
$nombre = $rowco['cc_destino'];
/*
  $puestoi = $rowco['cc_agente'];
  $correoi = busca($rowco['cc_agente'],'usuarios','u_id','u_mailcorp');
  $telefonoi = busca($rowco['cc_agente'],'usuarios','u_id','u_telefono'); 
*/

$puestoi = $rowco['cc_agente'];
$telefonoi = busca($rowco['cc_agente'], 'usuarios', 'u_id', 'u_telefono');


/* $correoi = busca($rowco['cc_agente'],'usuarios','u_id','u_mailcorp'); */
/* $password = base64_decode(busca($rowco['cc_agente'],'usuarios','u_id','u_contraseñacorp')); */
$sql = 'SELECT u_mailcorp, u_passcorp, u_host, u_puerto, u_remitente, u_seguridad FROM usuarios WHERE u_id = "'.$puestoi.'"';
$result = setq($sql);
list($coemail, $adminpassword, $hostcorp, $puerto, $remitente, $seguridad) = $result -> fetch_array();

echo 'Correo remitente: '.$coemail.'<br>';

$correoi = $coemail;
$password = $adminpassword;
$dominio = explode('@', $correoi);
$mail->Username = $correoi;
$mail->Password = $password;
$mail->Host = $hostcorp;
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->IsSMTP();
$mail->SMTPSecure = $seguridad;



/* $mail->Host = $host;
$mail->Port = $puerto;
if($seguridad != 0) $mail->SMTPSecure = $seguridad; 
$mail->Host = 'tye-solutions.com';
$mail->Port = 587;
$mail->IsSMTP(); */

//$mail->SMTPDebug = 2;



if (busca(busca($rowco['cc_tablero'], 'crm_tableros', 'ct_id', 'ct_cliente'), 'clientes', 'c_idc', 'COUNT(*)') == 0)
    $mail->AddAttachment('../docs/cotizaciones/' . $rowco['cc_folio'] . '.pdf');

/* $adjunto = busca($rowco['cc_id'], 'crm_cotizaciones', 'cc_id', 'cc_adjunto');
if ($adjunto && !empty($adjunto))
    $mail->AddAttachment("../" . $adjunto); */
$body = '<style type="text/css">
            .tablecot th, .tablecot td {
              padding-top: 5px;
              padding-bottom: 2px;
              padding-left: 10px;
              padding-right: 10px;
            }
            .tablecot th{
              background:#D4D4D4;
              border-bottom: 2px solid #000000;
            }
            .title-table{
              font-size:16px;
            }
            .number-import{
              font-size: 15px;
              text-align: right;
            }
            .logotip{
              width: 45px;
              height: 38px;
            }
            .cabeza{
              font-size: 18px;
              color: #003D7A;
              margin-bottom: 8px;
              text-align: center;
              margin-top: 10px;
            }
            .cliente{
              margin-top: 10px;
              margin-bottom: 10px;
              font-size: 15px;
              color: #003D7A;
              line-height: 1.3;
            }
            .cotiza{
              margin-top: 10px;
              margin-bottom: 10px;
              font-size: 13px;
              color: #333333;
              line-height: 1.3;
            }
            .fif {
              display: inline-block; /* Or inline-block */
              margin-right: 30px;
              vertical-align: top; /* here */
              margin-top: 50px;
            }
          </style>';
/*
  $logo = busca($_SESSION['emp'],'empresas','e_id','e_logo');
  if(file_exists($logo)){
    $body.='<center><img src="'.$logo.'"  width="160" height="130" /></center>';
  }
*/
include_once('../modulos/clientes.php');
$client = new modelclientes();
$client->select(busca($rowco['cc_tablero'], 'crm_tableros', 'ct_id', 'ct_cliente'));

include_once('../modulos/config.php');
$empres = new modelconfig();
$empres->select($_SESSION['emp']);

if ($rowco['cc_direnvio'])
    $direnvio = busca($rowco['cc_direnvio'], 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi," ",cd_colonia," ",cd_municipio," ",e_nmb," ",cd_cp)');
else
    $direnvio = "";
$encabezado = '<div class="cabeza"><b>Cotización: </b>' . $rowco['cc_folio'] . '</div>';
$bodycliente = '<hr><div class="cliente" contenteditable="true">
                        <div class="fif">
                          <div><b>Cliente: </b>' . $rowco['cc_destino'] . '</div>
                          <div><b>Teléfono(s) </b>' . $rowco['cc_teldestino'] . '</div>
                          <div><b>Correo(s) </b>' . $rowco['cc_maildestino'] . '</div>
                        </div>
                        <div class="fif">
                            <div><b>Agente: </b>' . $rowco['cc_nmbagente'] . '  </div>
                        </div>
                        <div class="fif">
                            <div><b>Fecha envío: </b>' . date('d/m/Y H:i:s') . '</div>
                            <div><b>Vigencia </b>' . fecha_formato($rowco['cc_ffin'], false, true) . '</div>
                            ' . $direnvio . '
                          </div>
                      </div>';
$bodyproveedor = '<hr>';
$body .= utf8_decode($encabezado);
$body .= utf8_decode($bodycliente);
$body .= utf8_decode($bodyproveedor);
$body .= utf8_decode(nl2br($rowco['cc_mensaje']));

$nmbempresa = busca("1", "empresas", "e_id", "e_nmb");

$mail->setFrom($correoi, utf8_decode($nmbempresa));
$mail->AddAddress($rowco['cc_maildestino'], utf8_decode($nombre));

if ($rowco['cc_copiamail']) {
    $correos = explode(',', $rowco['cc_copiamail']);
    foreach ($correos as $copias) {
        $mail->addCC($copias);
    }
}

$mail->ConfirmReadingTo = $correoi;
$mail->Subject = utf8_decode($asunto);

if ($rowco['cc_tabla'] == "1") {
    $table = "";
    $sqltp = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "' . $rowco['cc_id'] . '" ORDER BY cdm_id ASC';
    $result = setq($sqltp);
    if ($result->num_rows > 0) {
        $table .= '<table widht="100%" class="tablecot" style="margin-top:5px;">
                      <thead><tr><td colspan="5" class="title-table" >Productos cotizados</td></tr>
                      <tr>
                      <th width="10%" class="title-table">Cantidad</th>
                      <th width="12%" class="title-table">Modelo</th>
                      <th width="50%" class="title-table">Descripcion</th>
                      <th width="13%" class="title-table">Precio unitario</th>
                      <th width="15%" class="title-table">Importe</th>
                    </tr><thead>';
        $result = setq($sqltp);
        $tasaiva = busca(1, 'configuracionesp', 'c_id', 'c_iva') / 100;
        $totiva = 0;
        while ($row = $result->fetch_array()) {
            $precio = $row['cdm_precio'];
            $importe = $row['cdm_cantidad'] * $precio;

            $table .= '<tr>
                  <td>' . $row['cdm_cantidad'] . '</th>
                  <td>' . busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_modelo') . '</th>
                  <td>' . $row['cdm_nmbarticulo'] . '</th>
                  <td class="number-import">' . number_format($precio, 2) . '</th>
                  <td class="number-import">' . number_format($importe, 2) . '</th>
                </tr>';
        }
        if ($rowco['cc_mtotal'] == "1") {
            if ($rowco['cc_diva'] == 1 || $rowco['cc_descuento'] > 0)
                $table .= '<tr>
                    <td colspan="3">&nbsp;</th>
                    <th>Subtotal</th>
                    <th class="number-import">' . number_format($rowco['cc_subtotal'], 2) . '</th>
                  </tr>';

            if ($rowco['cc_descuento'] > 0) {
                $impdesc = ($rowco['cc_subtotal'] * ($rowco['cc_descuento'] / 100));
                $table .= '<tr>
                      <td colspan="3">&nbsp;</th>
                      <th>Descuento</th>
                      <th class="number-import">' . number_format($impdesc, 2) . '</th>
                    </tr>';
            }
                    $table .= '
                    <tr>
                      <td colspan="3">&nbsp;</th>
                      <th>Costo envío</th>
                      <th class="number-import">' . number_format($rowco['cc_precioenvio'], 2) . '</th>
                    </tr>';
            if ($rowco['cc_diva'] == 1)
                $table .= '<tr>
                      <td colspan="3">&nbsp;</th>
                      <th>IVA</th>
                      <th class="number-import">' . number_format($rowco['cc_iva'], 2) . '</th>
                    </tr>
                    <tr>
                      <td colspan="3">&nbsp;</th>
                      <th>Total</th>
                      <th class="number-import">' . number_format($rowco['cc_importe'], 2) . '</th>
                    </tr>';
            else {
                $table .= '
                    <tr>
                      <td colspan="3">&nbsp;</th>
                      <th>Total</th>
                      <th class="number-import">' . number_format($rowco['cc_importe'], 2) . '</th>
                    </tr>';
            }
        }
    }
    $table .= '</table>';
    $body .= utf8_decode($table);
}
if ($rowco['cc_cuentas'] == "1") {
    $sql = 'SELECT * FROM cuentas WHERE  cu_enviacotiza = "1" ORDER BY cu_orden ASC';
    $result = setq($sql);
    if ($result->num_rows > 0) {
        $result = setq($sql);
        $cuentas = '<hr>
                      <div class="cabeza"><b>Nuestras Cuentas Bancarias</b></div>
                      <div class="cliente" contenteditable="true">';
        while ($row = $result->fetch_array()) {
            $cuentas .= '   <div class="fif">
                          <div><b>Banco: </b>' . $row['cu_banco'] . '</div>
                          <div><b>Cuenta: </b>' . $row['cu_cuenta'] . '</div>
                          <div><b>CLABE </b>' . $row['cu_clabe'] . '</div>';
            if ($row['cu_numtarjeta'] && $row['cu_numtarjeta'] != "0")
                $cuentas .= '<div><b>Tarjeta </b>' . $row['cu_numtarjeta'] . '</div>';
            $cuentas .= '</div><hr>';
        }
        $cuentas .= '</div>';
    }
}

$body .= utf8_decode($cuentas);
$body .= utf8_decode('<hr>Mensaje generado automáticamente por ' . busca(1, "empresas", "e_id", "e_nmb"));
$mail->MsgHTML($body);
//envío el mensaje, comprobando si se envió correctamente
if (!$mail->send()) {
    unlink('../docs/cotizaciones/' . $rowco['cc_folio'] . '.pdf');
    $respuesta = 0;
} else {
    $respuesta = 1;
}
//echo $respuesta;

?>
