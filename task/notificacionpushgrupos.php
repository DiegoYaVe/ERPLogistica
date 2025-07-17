<?php
/* ini_set('display_errors', 1); */
include('../funciones.php');

$idlead = isset($_POST['idlead']) ? $_POST['idlead'] : '';
$estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '';
$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
$cotizacion = isset($_POST['cotizacion']) ? $_POST['cotizacion'] : '';
$cuerpo = isset($_POST['cuerpo']) ? $_POST['cuerpo'] : '';
$ingreso = isset($_POST['ingreso']) ? $_POST['ingreso'] : '';
$idcxc = isset($_POST['cxc']) ? $_POST['cxc'] : '';
$idguia = isset($_POST['idguia']) ? $_POST['idguia'] : '';

$nmb = '';
$sqlf = '';
$cond = true;

if(!isset($_POST['tipo'])){
//Consultamos los detalles del lead que se marcó como perdido
$sqlr = 'SELECT cl_nmb, cl_code, cl_telefono, cl_vendedor FROM crm_leads 
        WHERE cl_estatus = "' . $estatus . '" AND cl_id = "' . $idlead . '"';
$resultr = setq($sqlr);
list($nmb, $code, $telefono, $agente) = $resultr->fetch_array();

$cuerpo = 'NOMBRE: ' . $nmb . '  TELÉFONO: +' . $code . $telefono. '  VENDEDOR: '.$agente;
$notificationLink = '?modulo=leadsperdidos&accion=index';

} else if($tipo == "FCAPTURA"){
    $ncapturados = $_POST['ncapturados'];
    $cuerpo = $ncapturados." ".$titulo;
    $notificationLink = '?modulo=leadscapturados&accion=index';
} else if($tipo == "REGRESOVENTAS"){
    $sqlct = "SELECT cc_folio, cc_agente FROM crm_cotizaciones WHERE cc_id = '".$cotizacion."'";
    $resultct = setq($sqlct);
    list($titulo, $vendedor) = $resultct->fetch_array();
    $sqlf = " AND u_id = '".$vendedor."' ";
    $notificationLink = '?modulo=leadscapturados&accion=index';
} else if($tipo == "INGRESOAPROBADO"){
    $remision = busca($idcxc, 'cxcobrar', 'cx_id', 'cx_referencia');
    $titulo = busca($remision, 'remisiones', 'r_id', 'r_folio');
    $vendedor = busca($remision, 'remisiones', 'r_id', 'r_encargado');
    $sqlf = " AND u_id = '".$vendedor."' ";
} else if($tipo == "INGRESONOAPROBADO"){
    $idcxc = busca($ingreso, 'ingreso_cxcobrar', 'ic_ingreso', 'ic_cxcobrar');
    $remision = busca($idcxc, 'cxcobrar', 'cx_id', 'cx_referencia');
    $titulo = busca($remision, 'remisiones', 'r_id', 'r_folio');
    $vendedor = busca($remision, 'remisiones', 'r_id', 'r_encargado');
    $sqlf = " AND u_id = '".$vendedor."' ";
} else if($tipo == "REMISIONGUIA"){
    $cuerpo = "Nuevos productos ";

    /* $ing = busca($ingreso, 'ingresos', 'i_id', 'i_estatus');
    if($ing == "F"){ */
    $idcxc = busca($ingreso, 'ingreso_cxcobrar', 'ic_ingreso', 'ic_cxcobrar');
    $remision = busca($idcxc, 'cxcobrar', 'cx_id', 'cx_referencia');
    $titulo = busca($remision, 'remisiones', 'r_estatus = "F" AND r_id', 'r_folio');
    if(!empty($titulo)){
        $tipoP = intval(busca($remision, 'remisionesc', 'rc_tipoenvio = "P" AND rc_remision', 'COUNT(*)'));
        $tipoC = intval(busca($remision, 'remisionesc', 'rc_tipoenvio = "C" AND rc_remision', 'COUNT(*)'));
        $tipoDO = intval(busca($remision, 'remisionesc', 'rc_tipoenvio IN ("D", "O") AND rc_remision', 'COUNT(*)'));
        $bodyc = array();
        if($tipoP > 0){
            $bodyc[] = 'por definir forma de envío';
        }
        if($tipoC > 0){
            $bodyc[] = 'para embarcar';
        }
        
        if($tipoDO > 0){
            $bodyc[] = 'por asignar guía';
        }
        $elementos = count($bodyc);
        if($elementos == 1){
            $cuerpo .= $bodyc[0];
        } else if($elementos == 2){
            $cuerpo .= $bodyc[0]." y ".$bodyc[1];
        } else{
            $cuerpo .= $bodyc[0].", ".$bodyc[1]." y ".$bodyc[2];
        }
    } else{
        $cond = false;
    }
    /* } else{
        $cond = false;
    }
     */
} else if($tipo == "PORDEFINIR"){
    $remision = $_POST['remision'];
    $titulo = busca($remision, 'remisiones', 'r_id', 'r_folio');
} else if($tipo == "GUIAPARAEMBARCAR"){
    $nmbguia = busca($idguia, 'guias_articulos', 'ga_id', 'ga_nmb');
    $idpaq = busca($idguia, 'guias_articulos', 'ga_id', 'ga_paqueteria');
    $paqueteria = busca($idpaq, 'paqueterias', 'p_id', 'p_nmb');
    $cuerpo = "GUÍA: ".$nmbguia." - PAQUETERÍA: ".$paqueteria;
}

if($cond){
//Buscamos a todos los usuarios que son ADMINISTRADORES o GERENTES a los que se les puede notificar
$sql = "SELECT u_token FROM usuarios 
        WHERE u_grupo IN (".$grupos.") ".$sqlf."
        AND u_notificaciones = 1 AND u_estatus = 'A' AND u_token != ''";

$result = setq($sql);
if ($result->num_rows > 0) {

    $data = array();
    while ($row = $result->fetch_array()) {
        $data[] = $row['u_token'];
    }

    for ($i = 0; $i < count($data); $i++) {
        $serverkey = 'AAAA5f6Cr0I:APA91bG5YMlk5WkkRbzguEJByGsR9cnCAKwamh9PWo2VXKWVTaI5JAsC39wmWPtP9k7eA5dwTsRyiCBoO5WySvYTel-cvFX_CojnkDXm1RK81_70ai9qfTsyQQdMN9UoJ48loHlCoCeA';
        $url = 'https://fcm.googleapis.com/fcm/send';
        $field = [
            'to' => $data[$i],
            'notification' => array(
                'title' => $titulo,
                'body' => $cuerpo,
                'click_action' => $notificationLink
            ),
        ];

        $fields = json_encode($field);
        $header = array(
            'Authorization: key=' . $serverkey,
            'Content-Type: application/json'
        );


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        echo $result;
        echo $data[$i];
        curl_close($ch);
    }
}
}



?>