<?php
/* session_start(); */
ini_set('display_errors', 1);
include('../funciones.php');
require_once("../lib/mailer/class.phpmailer.php");
require_once("../lib/mailer/class.smtp.php");

function buscarYReemplazar($cadena, $frase_a_buscar, $reemplazo)
{
    return str_replace($frase_a_buscar, $reemplazo, $cadena);
}

function paqueteriaTipo($paqueteria, $ccid){
    $detalles = '';
    $guias = '';
    $paqueterianame = busca($paqueteria, 'paqueterias', 'p_id', 'p_nmb');
    $sqlg = 'SELECT ag_guia FROM articulos_guias WHERE ag_ccid = "'.$ccid.'"';
    $resultg = setq($sqlg);
    $i = 0;
    while($row = $resultg->fetch_array()){ 
        if($i = 0){
            $guias .= $row['ag_guia'];    
        } else{
            $guias .= ','.$row['ag_guia'];
        }
        $i++;
    }

    if(!empty($paqueterianame)){
        $detalles .= ' Paquetería: '.$paqueterianame.'.';
    }

    if(!empty($guias)){
        $detalles = ' Guía: '.$guias;
    }
    

    return $detalles;
}

function listadoProductos($cotizacion, $mostrarguia = false){

    $listaproductos = '<ul>';
    $query0 = 'SELECT DISTINCT ccc_cotizaciond AS ccc_cotizaciond FROM crm_cotizacionesc 
                WHERE ccc_cotizacion = "' . $cotizacion . '" 
                ORDER BY ccc_cotizaciond ASC;';
    $result0 = setq($query0);

    while ($row0 = $result0->fetch_array()) {
        //Buscamos el aticulo dentro los detalles de la cotización para determinar posteriormente su tipo (Combo o no)
        $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, cdm_cantidad, cdm_id, cdm_articulo FROM crm_cotizacionesd INNER JOIN articulos ON a_id = cdm_articulo WHERE cdm_id = "' . $row0['ccc_cotizaciond'] . '"';
        $resultcmb = setq($sqlcmb);

        list($aid, $anmb, $tipoprod, $cantidad, $cdmid, $cdmarticulo) = $resultcmb->fetch_array();
        $cantidad = intval($cantidad);
        /* $resp = 0;
        if($resp == 0){ */
        if ($tipoprod == "M") {
            for ($j = 1; $j <= intval($cantidad); $j++) {

                /* $queryd = 'SELECT * FROM crm_cotizacionesc WHERE ccc_cotizacion = "'.$cotizacion.'" AND ccc_cotizaciond = "'.$row0['ccc_cotizaciond'].'" AND ccc_combo = "'.$j.'"'; */
                $queryd = 'SELECT ccc_articulo, ccc_modelo, COUNT(*) as cantidad, ccc_id, ccc_cotizacion, ccc_cotizaciond, 
                        ccc_articulo, ccc_modelo, ccc_numero, ccc_tipoenvio, ccc_paqueteria, ccc_sucursal, 
                        ccc_combo, ccc_estatus, ccc_embarque
                        FROM crm_cotizacionesc
                        WHERE ccc_cotizacion = "' . $cotizacion . '" AND ccc_cotizaciond = "' . $row0['ccc_cotizaciond'] . '" AND ccc_combo = "' . $j . '"
                        GROUP BY ccc_articulo, ccc_modelo;';

                $resultd = setq($queryd);

                $listaproductos .= '<li>PAQUETE - ' . $anmb . '<ul>';
                while ($rowcb = $resultd->fetch_array()) {
                    $var = busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['ccc_modelo'] . '" AND av_articulo', 'COUNT(*)');

                    if ($var > 0) {
                        $artname = busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['ccc_modelo'] . '" AND av_articulo', 'av_nmb');
                    } else {
                        $artname = busca($rowcb['ccc_articulo'], "articulos", "a_id", "a_nmb");
                    }

                    $tantos = intval($rowcb['cantidad']);
                    if ($tantos > 1) {
                        if($mostrarguia){
                        for ($h = 1; $h <= $tantos; $h++) {
                            
                            $ccid = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '" . $cotizacion . "' AND ccc_cotizaciond = '" . $cdmid . "' AND ccc_modelo = '" . $rowcb['ccc_modelo'] . "' AND ccc_combo = '" . $j . "' AND ccc_numero = '" . $h . "' AND ccc_articulo", "ccc_id");
                            $paqueteria = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '" . $cotizacion . "' AND ccc_cotizaciond = '" . $cdmid . "' AND ccc_modelo = '" . $rowcb['ccc_modelo'] . "' AND ccc_combo = '" . $j . "' AND ccc_numero = '" . $h . "' AND ccc_articulo", "ccc_paqueteria");
                            $detalles = paqueteriaTipo($paqueteria, $ccid);
                            $listaproductos .= "<li>1 " . $artname . $detalles."</li>";
                        }
                        } else{
                            $listaproductos .= "<li>" . $tantos . " " . $artname . "</li>";
                        }
                    } else {
                        $ccid = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '" . $cotizacion . "' AND ccc_cotizaciond = '" . $cdmid . "' AND ccc_modelo = '" . $rowcb['ccc_modelo'] . "' AND ccc_combo = '" . $j . "' AND ccc_articulo", "ccc_id");
                        $paqueteria = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '" . $cotizacion . "' AND ccc_cotizaciond = '" . $cdmid . "' AND ccc_modelo = '" . $rowcb['ccc_modelo'] . "' AND ccc_combo = '" . $j . "' AND ccc_articulo", "ccc_paqueteria");
                        if($mostrarguia){
                            $detalles = paqueteriaTipo($paqueteria, $ccid);
                            $listaproductos .= "<li>" . $tantos . " " . $artname . $detalles . "</li>";
                        } else{
                            $listaproductos .= "<li>" . $tantos . " " . $artname . "</li>";
                        }
                        
                    }

                }
                $listaproductos .= '</ul></li>';
            }
        } else {
            if (true) {
                $ccid = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '" . $cotizacion . "' AND ccc_cotizaciond = '" . $cdmid . "' AND ccc_modelo = '' AND ccc_articulo", "ccc_id");
                $paqueteria = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '" . $cotizacion . "' AND ccc_cotizaciond = '" . $cdmid . "' AND ccc_modelo = '' AND ccc_articulo", "ccc_paqueteria");
                if($mostrarguia){
                    $detalles = paqueteriaTipo($paqueteria, $ccid);
                    $listaproductos .= "<li>" . $cantidad . " " . $anmb . $detalles . "</li>";
                } else{
                    $listaproductos .= "<li>" . $cantidad . " " . $anmb . "</li>";
                }
            }
        }
    }
    $listaproductos .= '</ul>';
    return $listaproductos;
}

$respuesta = 0;
$correoop = $_POST['correoop']; //ID del correo operativo

if (isset($_POST['remision'])) {
    $remision = $_POST['remision']; //ID de la cotización
    $vendedor = busca($remision, 'remisiones', 'r_id', 'r_encargado');
    $sqlco = 'SELECT * FROM remisiones INNER JOIN crm_clientes ON c_id = r_cliente WHERE r_id = "' . $remision . '"';
    $destino = 'c_nmb';
    $maildestino = 'c_correo1';
    $importe = 'r_total';
    $nmbagente = 'r_encargado';
    $nmb = 'r_nmb';
    $folio = 'r_folio';
    $ffin = 'r_falta';
    $estatus = 'r_estatus';
    $cotizacion = busca($remision, 'crm_cotizaciones', 'cc_remision', 'cc_id');
    $ruta = 'docs/remisiones/';
    $fpedido = 'r_faplica';
} else {
    $cotizacion = $_POST['cotizacion']; //ID de la cotización    
    $sqlco = 'SELECT * FROM crm_cotizaciones WHERE cc_id = "' . $cotizacion . '"';
    $vendedor = busca($cotizacion, 'crm_cotizaciones', 'cc_id', 'cc_agente');
    $destino = 'cc_destino';
    $maildestino = 'cc_maildestino';
    $importe = 'cc_importe';
    $nmbagente = 'cc_nmbagente';
    $nmb = 'cc_nmb';
    $folio = 'cc_folio';
    $ffin = 'cc_ffin';
    $estatus = 'cc_estatus';
    $ruta = 'docs/cotizaciones/';
    $fpedido = $ffin;
}
$sql = 'SELECT u_mailcorp, u_passcorp, u_host FROM usuarios WHERE u_id = "'.$vendedor.'"';
$result = setq($sql);
list($coemail, $adminpassword, $hostcorp) = $result -> fetch_array();

$resultco = setq($sqlco);
$rowco = $resultco->fetch_array();

$listaproductos = listadoProductos($cotizacion);
$productosconguias = listadoProductos($cotizacion, true);
$recibopago = $ruta.$row[$folio].'.pdf';
$iddirenvio = busca($cotizacion, 'crm_cotizaciones', 'cc_remision', 'cc_direnvio');
$direnviopedido = busca($iddirenvio, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT("Calle: "cd_calle," N. ext: ",cd_nume,"  N. int: ",cd_numi,", Colonia: ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P. ",cd_cp)');
$nombrecliente = $rowco[$destino];
$emailcliente = $rowco[$maildestino]; //Email destino
$ptotal = $rowco[$importe];
$nombrevendasignado = $rowco[$nmbagente];
$mailcontacto = busca("1", "empresas", "e_id", "e_correo");
$nmbempresa = busca("1", "empresas", "e_id", "e_nmb");
$nombrecotizacion = $rowco[$nmb];
$foliocotizacion = $rowco[$folio];
$fechacotizacion = $rowco[$ffin];
if ($rowco[$estatus] == "C" && isset($_POST['remision'])) {
    $fechacotizacion = $rowco['r_fcan'];
}
$fechapedido = $row[$fpedido];
$detallepagonoacreditado = 'Pago no acreditado. ';


$sqlcp = 'SELECT * FROM correosop WHERE c_id = "' . $correoop . '"';
$resultcp = setq($sqlcp);
$rowcp = $resultcp->fetch_array();
$ccp = array();
for ($j = 1; $j <= 10; $j++) {
    if ($rowcp['c_ccp' . $j] != '') {
        $ccp[] = $rowcp['c_ccp' . $j];
    }
}

/* $coemail = $rowcp['c_mail']; //Email emisor
$adminpassword = $rowcp['c_pass']; //Contraseña del mail emisor */
$asunto = $rowcp['c_titulo']; //Asunto del correo electrónico
$cuerpomail = $rowcp['c_descripcion']; //Cuerpo del mensaje

$cadena_actualizada = buscarYReemplazar($cuerpomail, "--%nombrecliente%--", isset($nombrecliente) ? $nombrecliente : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%emailcliente%--", isset($emailcliente) ? $emailcliente : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%listaproductos%--", isset($listaproductos) ? $listaproductos : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%ptotal%--", isset($ptotal) ? $ptotal : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%elementonombre%--", isset($nombrecotizacion) ? $nombrecotizacion : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%elementofolio%--", isset($foliocotizacion) ? $foliocotizacion : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%elementofecha%--", isset($fechacotizacion) ? $fechacotizacion : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%nombrevendasignado%--", isset($nombrevendasignado) ? $nombrevendasignado : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%mailcontacto%--", isset($mailcontacto) ? $mailcontacto : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%productosconguias%--", isset($productosconguias) ? $productosconguias : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%direnviopedido%--", isset($direnviopedido) ? $direnviopedido : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%recibopago%--", isset($recibopago) ? $recibopago : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%fechapedido%--", isset($fechapedido) ? $fechapedido : '');
$cadena_actualizada = buscarYReemplazar($cadena_actualizada, "--%detallepagonoacreditado%--", isset($detallepagonoacreditado) ? $detallepagonoacreditado : '');

for ($i = 0; $i < count($ccp); $i++) {
    $mail = new PHPMailer();

    $body = $cadena_actualizada;

    /* $mail->SMTPSecure = "ssl"; */
    $mail->Host = $host;
    $mail->Port = 587;
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->Username = $coemail;
    $mail->Password = $adminpassword;
    if(!empty($recibopago)){
        $mail->AddAttachment($recibopago);
    }
    //Defino el email y nombre del remitente del mensaje
    $mail->setFrom($coemail, utf8_decode($nmbempresa));
    //Defino la dirección de correo a la que se envía el mensaje
    $mail->addAddress($ccp[$i]);
    /*
    for ($i = 0; $i < count($ccp); $i++) {
        // Añado una dirección de correo en copia (CC)
        $mail->addCC($ccp[$i]);
    } */
    //Añado un asunto al mensaje
    $mail->Subject = utf8_decode($asunto);

    $mail->MsgHTML($body);
    if (!$mail->send()) {
        $respuesta = 0;
    } else {
        $respuesta = 1;
    }
}

echo $respuesta;



?>