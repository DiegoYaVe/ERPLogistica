<?php
//ini_set('display_errors', 1);
class xml {
  var $model;
  var $view;

  function xml() {
    $this->model = new Modelxml(); // crea modelo
  }

  function index($ticket) {
     $this->model->resultxml($ticket);
  }

}

class Modelxml {

  function resultxml($ticket) {
    $factura = $ticket;
    $empresa = busca($factura,'ncredito','n_id','n_empresa');

      $folioinf = "N";
      $folion = busca($folioinf, 'foliosinf', 'f_empresa = "'.$empresa.'" AND f_tipofac', 'f_folio');
      $folios = busca($folioinf, 'foliosinf', 'f_empresa = "'.$empresa.'" AND f_tipofac', 'f_serie');
      $folio = $folios . $folion;

    if(busca($factura,'ncredito','n_id','n_estatus') == "T"){
      die('<script>
              document.location.href="?modulo=ncredito&accion=showfactura&factura='.$factura.'";
            </script>');
    }

    if(busca($empresa,'ncredito','n_estatus IN ("T","C") AND n_folio = "'.$folio.'" AND n_empresa','COUNT(*)') > 0){
      die(alert_back("Error: El Folio ".$folio.' ya esta usado',true));
    }

    if(busca($factura,'ncredito','n_estatus IN ("T","C") AND n_id','COUNT(*)') > 0){
      die(alert_back("La Factura ha sido previamente Timbrada",true));
    }

    if(!$folion OR empty($folion)){
      die("sE REQUIERE UN FOLIO");
    }
    elseif(!$folios OR empty($folios)){
      die("sE REQUIERE UNA SERIE");
    }
    $sqlup = 'UPDATE ncredito SET n_folio = "'.$folio.'", n_serie = "'.$folios.'", n_nfolio = "'.$folion.'"
              WHERE n_id = "'.$ticket.'"';
    setq($sqlup) or die($sqlup);

    if(file_exists('cfd/' . $empresa . '/' . busca($empresa,'empresas','e_id','e_rfc') . '.cer.pem')){

    $file = fopen('cfd/' . $empresa . '/' . busca($empresa,'empresas','e_id','e_rfc') . '.cer.pem', "r") or die("No se pudo abrir el Certificado!");
    if (!isset($contenido))
      $contenido = NULL;
    while (!feof($file)) {
      $contenido.=fgets($file) . "<br />";
    }
    fclose($file);
    /*--------------------------------------------------------------------------------------*/
    $certificado = explode('-----', $contenido);
    $certificadoss = limpia_espacios($certificado[6]);
    /*--------------------------------------------------------------------------------------*/
    }
    else{
      die('Falta Certificado '.busca($empresa,'empresas','e_id','e_rfc').'<br>'.'cfd/' . $empresa . '/' . busca($empresa,'empresas','e_id','e_rfc') . '.cer.pem');
    }

    if(file_exists('cfd/' . $empresa . '/' . busca($empresa,'empresas','e_id','e_rfc') . '.key.pem')){
      $filekey = fopen('cfd/' . $empresa . '/' . busca($empresa,'empresas','e_id','e_rfc') . '.key.pem', "r") or die("No se pudo abrir el Certificado!");

    if (!isset($contenidokey))
      $contenidokey = NULL;
    while (!feof($filekey)) {
      $contenidokey.=fgets($filekey) . "<br />";
    }
    fclose($filekey);
    /*--------------------------------------------------------------------------------------*/
    $certificadokey = explode('-----', $contenidokey);
    /*--------------------------------------------------------------------------------------*/
    }
    else{
      die('Falta Key');
    }

    $subtotal = 0;
    $sqlt = 'SELECT * FROM ncredito WHERE n_id="' . $ticket . '"';
    $resultt = setq($sqlt) or die(mysql_error());
    $rowt = $resultt->fetch_array();
    $sql = 'SELECT * FROM ncreditod WHERE d_ncredito ="' . $ticket . '" ORDER BY d_id ASC';
    $resulttd = setq($sql) or die(mysql_error());
    $resulttdd = setq($sql) or die(mysql_error());

    $cadena = '';
    $subtotal = 0;
    $total = 0;
    $cadenaimpt = '';
    $cadenaimpl = '';
    $cadenaimplocales = '';
    $trasladoiva = 0;
    $reteniva = 0;
    $retencionisr = 0;
    $trasladoimpl = 0;
    $trasladoish = 0;
    $retencionimpl = 0;
    $cadenaimpr = "";
    $cadenaam = "";
    $basetra = 0;
    $baseret = 0;
    $cadenaimploca = "";
    $totalimpuestostras = 0;
    $totalimpuestosret = 0;
    $descuento = busca($ticket,'ncredito','n_id','n_descuento');

    $amortizacion = "0";

    $n=0;
    while ($rowtdd = $resulttdd->fetch_array()){
      $n++;
      if($rowtdd['d_costo'] > 0){
        $importe=($rowtdd['d_costo'] * $rowtdd['d_cantidad'])-$rowtdd['d_descuento'];
        $importefac = ($rowtdd['d_costo'] * $rowtdd['d_cantidad']);
      }

/*
      $importefac=round($importefac,6);
      $importe=round($importe,6);

*/
      $subtotal+=$importefac;
      $total+=$importe;
      $claveun = busca($rowtdd['d_unidad'],'unidades','u_empresa = "'.$_SESSION['emp'].'" AND u_nmb','u_clavesat');

      $cadena.='|'.$rowtdd['d_producto'];

      if($rowtdd['d_noid'] != NULL && $rowtdd['d_noid'] != "")
          $cadena.='|'.$rowtdd['d_noid'];

      $cadena.='|'.$rowtdd['d_cantidad'].'|'.$claveun.'|'.$rowtdd['d_unidad'];

      if($amortizacion == "0" && $rowtdd['d_descuento'] > 0) $cadenaam = '|'.$rowtdd['d_descuento'];

      $descripcion = str_replace("  "," ",$rowtdd['d_concepto']);
      $descripcion = str_replace(array("\n","\r","\t","\e","\v")," ",$descripcion);
//      $descripcion = clearvmayus($descripcion);
      $descripcion = str_replace("  "," ",$descripcion);
      $descripcion = str_replace("  "," ",$descripcion);
      $descripcion = str_replace("  "," ",$descripcion);

      $cadena.='|'.trim(substr($descripcion,0,999)).'|'.number_format($rowtdd['d_costo'],6,'.','').'|'.number_format($importefac,6,'.','').$cadenaam;

      $sqlfi = 'SELECT * FROM ncreditod_imp
                WHERE ni_ncredito = "'.$factura.'" AND ni_id = "'.$rowtdd['d_id'].'"
                AND ni_base > 0
                ORDER BY ni_tr DESC,ni_impuesto';
      $resultfi = setq($sqlfi) or die($sqlfi);
      if($resultfi->num_rows > 0) $cadena.='|02'; else $cadena.='|01';
      $resultfi = setq($sqlfi) or die($sqlfi);

      while($rowfi = $resultfi->fetch_array()){
//      if($rowfi['ni_impuesto'] == "002" && $rowfi['ni_tr'] == "T" && $rowfi['ni_ncredito'] == "62175") $rowfi['ni_importe']-=0.01;

      if($rowfi['ni_tr'] == "T"){
        $basetra+=$rowfi['ni_base'];
        $totalimpuestostras+=$rowfi['ni_importe'];
      }
      else{
        $totalimpuestosret+=round($rowfi['ni_importe'],2);
        $baseret+=$rowfi['ni_base'];
      }

      if($rowfi['ni_importe'] == 0) $rowfi['ni_tasac'] = 0;
        $cadena.='|'.$rowfi['ni_base'].'|'.$rowfi['ni_impuesto'].'|'.$rowfi['ni_tipof'].'|'.number_format($rowfi['ni_tasac'],6).'|'.number_format($rowfi['ni_importe'],6,'.','');
      }
    }// fin del while
    $total+=$totalimpuestostras;
    $total-=$totalimpuestosret;

  if($totalimpuestosret > 0){ $cadenatotr='|'.number_format($totalimpuestosret,2,'.',''); }
  else { $cadenatotr=''; }

  $sqlr = 'SELECT SUM(ni_importe) importe,ni_impuesto impuesto FROM ncreditod_imp
            WHERE ni_ncredito = "'.$factura.'" AND ni_tr = "R"
            GROUP BY ni_impuesto';
  $resultr = setq($sqlr) or die($sqlr);
  while($rowr = $resultr->fetch_array()){
    $cadenaimpr.='|'.$rowr['impuesto'].'|'.number_format($rowr['importe'],2,'.','');
  }

  $sqlt = 'SELECT SUM(ni_importe) importe,SUM(ni_base) sumabase,ni_impuesto impuesto,ni_tipof,ni_tasac
           FROM ncreditod_imp
           WHERE ni_ncredito = "'.$factura.'" AND ni_tr = "T"
           GROUP BY ni_impuesto';
  $resultt = setq($sqlt) or die($sqlt);
  while($rowt = $resultt->fetch_array()){
    if($rowt['importe'] == 0) $rowt['ni_tasac'] = 0;
    $cadenaimpt.='|'.$rowt['sumabase'].'|'.$rowt['impuesto'].'|'.$rowt['ni_tipof'].'|'.number_format($rowt['ni_tasac'],6,'.','').'|'.number_format($rowt['importe'],2,'.','');
  }

  $cadenatott='|'.number_format($totalimpuestostras,2,'.','');

  $cadrela = "";
  $sqlr = 'SELECT DISTINCT(fr_tiporelacion) FROM factura_relacion WHERE fr_origen = "'.$factura.'"';
  $resultr = setq($sqlr) or die($sqlr);
  while($rowr = $resultr->fetch_array()){
    $cadrela.='|'.str_pad($rowr['fr_tiporelacion'],2,"0",STR_PAD_LEFT);
    $sqlr = 'SELECT fr_uuid FROM factura_relacion WHERE fr_origen = "'.$factura.'" ';
    $resultrf = setq($sqlr) or die($sqlr);
    while($rowrf = $resultrf->fetch_array()){
      $cadrela.='|'.$rowrf['fr_uuid'];
    }
  }

  $ftimbra= explode(' ',date('Y-m-d H:i:s',strtotime('- 60 seconds')));
  $fechasat = $ftimbra[0].'T'.$ftimbra[1];

  $metodopago = busca($ticket,'ncredito','n_id','n_metodopago');
  $descuento = busca($ticket,'ncredito','n_id','n_descuento');
  $moneda = busca($ticket,'ncredito','n_id','n_tipocambio');
  $valorm = busca($ticket,'ncredito','n_id','n_valorm');

  if($moneda == "MXN")  $valorm = number_format($valorm,0);
  else  $valorm = number_format($valorm,4);

  $condiciones = busca($ticket,'ncredito','n_id','n_condiciones');
  if($condiciones) $condic = '|'.$condiciones;
  else $condic = "";

  $formapago = strtoupper(busca(busca($ticket,'ncredito','n_id','n_fpago'),'cfdi_fpago','cf_id','cf_nmb'));
  $lugarexpedicion = busca($empresa, 'empresas', 'e_id', 'e_cp');
  $exportacion = "01";
  $regimenfis = busca($empresa, 'empresas', 'e_id', 'e_regimen');
  $tipocambio = busca($ticket,'ncredito','n_id','n_tipocambio');
  $cliente = busca($factura,'ncredito','n_id','n_cliente');
  $fiscales = busca($factura,'ncredito','n_id','n_fiscales');

  if($amortizacion == 1) $descuento = $montoamortizacion;
  $ingoegr = "E";

  $version = "4.0";

  //Carta porte
  $sql = 'SELECT COUNT(*) FROM carta_porte WHERE cp_factura = "'.$factura.'"';
  $result = setq($sql) or die($sql);
  list($cpnum) = $result->fetch_array();
  if($cpnum > 0){
    $sqlcp = 'SELECT * FROM carta_porte WHERE cp_factura = "'.$factura.'"';
    $resultcp = setq($sqlcp) or die($sqlcp);
    while($rowcp = $resultcp->fetch_array()){
      $cadcartap=$rowcp['cp_version'].'|'.$rowcp['cp_TranspInternac'];
      if(!empty($rowcp['cp_EntradaSalidaMerc']) && $rowcp['cp_TranspInternac'] != "No") $cadcartap.='|'.$rowcp['cp_EntradaSalidaMerc'];
//      if(!empty($rowcp['cp_PaisOrigenDestino'])) $cadcartap.='|'.$rowcp['cp_PaisOrigenDestino'];
      if(!empty($rowcp['cp_ViaEntradaSalida']) && $rowcp['cp_TranspInternac'] != "No") $cadcartap.='|'.$rowcp['cp_ViaEntradaSalida'];
      if($rowcp['cp_TotalDistRec'] > 0) $cadcartap.='|'.$rowcp['cp_TotalDistRec'];

      $sqlu = 'SELECT * FROM carta_porte_ubicaciones WHERE cpu_factura = "'.$factura.'" ORDER BY cpu_TipoUbicacion DESC';
      $resultu = setq($sqlu);
      while($rowu = $resultu->fetch_array()){
        $cadcartap.='|'.$rowu['cpu_TipoUbicacion'];
        if($rowu['cpu_IDUbicacion'] != 0) $cadcartap.='|'.$rowu['cpu_IDUbicacion'];
        $cadcartap.='|'.$rowu['cpu_RFCRemitenteDestinatario'];
        if(!empty($rowu['cpu_NombreRFC'])) $cadcartap.='|'.clearvmayus($rowu['cpu_NombreRFC']);
        if(!empty($rowu['cpu_NumRegIdTrib'])) $cadcartap.='|'.$rowu['cpu_NumRegIdTrib'];
        if(!empty($rowu['cpu_ResidenciaFiscal'])) $cadcartap.='|'.$rowu['cpu_ResidenciaFiscal'];
        $cadcartap.='|'.str_replace(" ","T",$rowu['cpu_FechaHoraSalidaLlegada']);
        if($rowu['cpu_DistanciaRecorrida'] > 0) $cadcartap.='|'.$rowu['cpu_DistanciaRecorrida'];
        if(!empty($rowu['cpu_CodigoPostal'])){
          if(!empty($rowu['cpu_Calle'])) $cadcartap.='|'.clearvmayus($rowu['cpu_Calle']);
          if(!empty($rowu['cpu_NumeroExterior'])) $cadcartap.='|'.$rowu['cpu_NumeroExterior'];
          if(!empty($rowu['cpu_NumeroInterior'])) $cadcartap.='|'.$rowu['cpu_NumeroInterior'];
          if(!empty($rowu['cpu_Colonia'])) $cadcartap.='|'.$rowu['cpu_Colonia'];
          if(!empty($rowu['cpu_Localidad'])) $cadcartap.='|'.$rowu['cpu_Localidad'];
          if(!empty($rowu['cpu_Referencia'])) $cadcartap.='|'.$rowu['cpu_Referencia'];
          if(!empty($rowu['cpu_Municipio'])) $cadcartap.='|'.$rowu['cpu_Municipio'];
          if(!empty($rowu['cpu_Estado'])) $cadcartap.='|'.$rowu['cpu_Estado'];
          if(!empty($rowu['cpu_Pais'])) $cadcartap.='|'.$rowu['cpu_Pais'];
          if(!empty($rowu['cpu_CodigoPostal'])) $cadcartap.='|'.$rowu['cpu_CodigoPostal'];
        }
      }

      $cadcartap.='|'.$rowcp['cp_PesoBrutoTotal'];
      $cadcartap.='|'.$rowcp['cp_UnidadPeso'];
      if($rowcp['cp_PesoNetoTotal'] != 0) $cadcartap.='|'.$rowcp['cp_PesoNetoTotal'];
      $cadcartap.='|'.$rowcp['cp_NumTotalMercancias'];
      if($rowcp['cp_CargoPorTasacion'] != 0) $cadcartap.='|'.$rowcp['cp_CargoPorTasacion'];

      $sqlm = 'SELECT * FROM carta_porte_mercancias WHERE cpm_factura = "'.$factura.'" ORDER BY cpm_idm ASC';
      $resultm = setq($sqlm);
      while($rowm = $resultm->fetch_array()){
        if($rowm['cpm_BienesTransp'] == "15101515" || $rowm['cpm_BienesTransp'] == "15101514" || $rowm['cpm_BienesTransp'] == "15101505") $rowm['cpm_BienesTransp'] = "15101800";
        $cadcartap.='|'.$rowm['cpm_BienesTransp'];
        $cadcartap.='|'.$rowm['cpm_Descripcion'];
        $cadcartap.='|'.$rowm['cpm_Cantidad'];
        $cadcartap.='|'.$rowm['cpm_ClaveUnidad'];
        if(!empty($rowm['cpm_Dimensiones'])) $cadcartap.='|'.$rowm['cpm_Dimensiones'];
/*
        if($rowm['cpm_peligroso'] == "Sí"){
          $materialpeg = "Sí";
//          die($materialpeg.'<');

          $cadcartap.='|'.$materialpeg;
          $cadcartap.='|'.$rowm['cpm_CveMaterialPeligroso'];
          $cadcartap.='|'.$rowm['cpm_Embalaje'];
        }
*/
        $cadcartap.='|'.$rowm['cpm_PesoEnKg'];
//        if(!empty($rowm['cpm_ValorMercancia'])) $cadcartap.='|'.$rowm['cpm_ValorMercancia'];
//        if(!empty($rowm['cpm_Moneda'])) $cadcartap.='|'.$rowm['cpm_Moneda'];
        if(!empty($rowm['cpm_FraccionArancelaria'])) $cadcartap.='|'.$rowm['cpm_FraccionArancelaria'];
        if(!empty($rowm['cpm_UUIDComercioExt'])) $cadcartap.='|'.$rowm['cpm_UUIDComercioExt'];
      }
      if(!empty($rowcp['cp_tipopermiso'])) $cadcartap.='|'.$rowcp['cp_tipopermiso'];
      if(!empty($rowcp['cp_numpermiso'])) $cadcartap.='|'.$rowcp['cp_numpermiso'];

      if(!empty($rowcp['cp_configtransporte'])) $cadcartap.='|'.$rowcp['cp_configtransporte'];
      if(!empty($rowcp['cp_PlacaVM'])) $cadcartap.='|'.$rowcp['cp_PlacaVM'];
      if(!empty($rowcp['cp_AnioModeloVM'])) $cadcartap.='|'.$rowcp['cp_AnioModeloVM'];

      if(!empty($rowcp['cp_AseguraRespCivil'])) $cadcartap.='|'.clearvmayus($rowcp['cp_AseguraRespCivil']);
      if(!empty($rowcp['cp_PolizaRespCivil'])) $cadcartap.='|'.clearvmayus($rowcp['cp_PolizaRespCivil']);

      if(!empty($rowcp['cp_TipoRem1'])) $cadcartap.='|'.clearvmayus($rowcp['cp_TipoRem1']);
      if(!empty($rowcp['cp_PlacaRem1'])) $cadcartap.='|'.clearvmayus($rowcp['cp_PlacaRem1']);
      if(!empty($rowcp['cp_TipoRem2'])) $cadcartap.='|'.clearvmayus($rowcp['cp_TipoRem2']);
      if(!empty($rowcp['cp_PlacaRem2'])) $cadcartap.='|'.clearvmayus($rowcp['cp_PlacaRem2']);

      if(!empty($rowcp['cp_figuratransporte'])) $cadcartap.='|'.$rowcp['cp_figuratransporte'];
      if(!empty($rowcp['cp_RFCFigura'])) $cadcartap.='|'.$rowcp['cp_RFCFigura'];
      if(!empty($rowcp['cp_NumLicencia'])) $cadcartap.='|'.$rowcp['cp_NumLicencia'];
      if(!empty($rowcp['cp_NombreFigura'])) $cadcartap.='|'.clearvmayus($rowcp['cp_NombreFigura']);

    }
    $cadcartap.='|';
  }
  else $cadcartap = "";

  include_once("modulos/ncredito.php");
  $facts = new Modelncredito();
  $facts->selectncr($ticket);

  include_once("modulos/clientes.php");
  $modelclient = new modelclientes();
  $modelclient->selectfisc($facts->cliente,$facts->fiscales);

  include_once('modulos/config.php');
  $model = new modelconfig();
  $model->select($empresa);

  if($modelclient->rfc == "XAXX010101000" && busca($ticket,'ncredito_global','fg_id','COUNT(*)') > 0){
    $sql = 'SELECT fg_periodicidad,fg_meses,fg_ano FROM ncredito_global WHERE fg_id = "'.$_GET['id'].'"';
    $result = setq($sql);
    list($periodicidad,$meses,$ano) = $result->fetch_array();
    $cadglo='|'.$periodicidad.'|'.$meses.'|'.$ano;
  }
  else $cadglo='';

  include_once ('modulos/clientes.php');
  $modelclient = new modelclientes();
  $modelclient->selectfisc($cliente,$fiscales);

   $cadenaorig = "";
/*Pago*/ $cadenaorig.= '|'.$formapago.'|'.$model->cfdcertificado.$condic.'|'.number_format($subtotal,2,'.','');

if($descuento > 0)
/*Desc*/ $cadenaorig.= '|'.number_format($descuento,2,'.','');

/*Pag2*/ $cadenaorig.= '|'.$moneda.'|'.$valorm.'|'.number_format($total,2,'.','').'|'.$ingoegr.'|'.$exportacion.'|'.strtoupper($metodopago).'|'.$lugarexpedicion;
/*Rela*/ $cadenaorig.=$cadrela;

/*Glob*/ $cadenaorig.=$cadglo;
/*Emis*/ $cadenaorig.='|'.$model->rfc.'|'.$model->razons.'|'.$regimenfis;
/*Rece*/ $cadenaorig.='|'.$modelclient->rfc.'|'.$modelclient->razonsocial.'|'.$modelclient->cp.'|'.$modelclient->regimen;

//if(busca($cliente, 'clientes', 'c_id', 'c_rfc') == "XEXX010101000") $cadenaorig.='|JPN|XEXX010101000';

/*Rece*/ $cadenaorig.='|'.busca($ticket, 'ncredito', 'n_id', 'n_uso');

/*Conc*/ $cadenaorig.=$cadena;
/*Impr*/ $cadenaorig.=$cadenaimpr.$cadenatotr;
/*Impt*/ $cadenaorig.=$cadenaimpt;
/*Toim*/ $cadenaorig.=$cadenatott;
/*Impl*/ $cadenaorig.=$cadenaimploca.$cadenaimplocales;

if($factura == "130422")
  $cadcomp='|1.1|2|A1|0|FOB|0|20.00|55650.00|DUCJ740726HCLRRR09|DESCARTES 400|030|COA|MEX|25090|1-10-27 ICHINOSE|ICH|JPN|9271231|1|0602909999|1350.000|01|29.50|39825.00|2|0602909999|500.000|01|31.65|15825.00';

//*Limpio simbolos*/ $cadenaorig=clearvmayus($cadenaorig);

/*CartaP*/ $cadenaorig.=$cadcartap;

  $cadenaorig = '||'.$version.'|'.$folios.'|'.$folion.'|'.$fechasat.$cadenaorig.'||';

  $cadenaorig = str_replace("  "," ",$cadenaorig);
  $cadenaorig = str_replace(" |","|",$cadenaorig);
  $cadenaorig = str_replace("| ","|",$cadenaorig);
  $cadenaorig = str_replace(array("\n","\r","\t","\e","\v")," ",$cadenaorig);
  $cadenaorig = str_replace("  "," ",$cadenaorig);
  $cadenaoriginal = '';

  for($i=0; $i<strlen($cadenaorig); $i++){
    if($cadenaorig[$i] == " "){
      if($espacio == 0)
        $cadenaoriginal.=$cadenaorig[$i];
        $espacio = 1;
    }
    else{
      $cadenaoriginal.=$cadenaorig[$i];
      $espacio = 0;
    }
  }

  //Finalmente, guardarlo en un directorio:
  if (!is_dir('cfd')) {
      @mkdir('cfd', 0777);
  }
  if (!is_dir('cfd/' . $empresa.'/xml')) {
      @mkdir('cfd/' . $empresa.'/xml', 0777);
  }

  $handle=fopen('cfd/' . $empresa . '/xml/x.txt','w');  fwrite($handle, $cadenaoriginal); fclose($handle);
  /******************************************************/
  $file='cfd/' . $empresa . '/' . busca($empresa,'empresas','e_id','e_rfc') . '.key.pem';      // Ruta al archivo
  $cadena_original = utf8_encode($cadenaoriginal) ;

  $pkeyid = openssl_get_privatekey(file_get_contents($file));
  openssl_sign($cadena_original, $crypttext, $pkeyid, OPENSSL_ALGO_SHA256);
  openssl_free_key($pkeyid);
  $sello = base64_encode($crypttext);             // lo codifica en formato base64

  /******************************************************/
  $xmlns = 'http://www.sat.gob.mx/cfd/4';
//  $xml = new DOMDocument('1.0', 'utf-8');
  $find_letters = array('Ñ','Á','É','Í','Ó','Ú');

  $pos = strpos($modelclient->razonsocial, $find_letters);

//  DIE($modelclient->razonsocial.'--- '.$pos);

  if($pos || $modelclient->rfc == "XAXX010101000") $xml = new DOMDocument('1.0', 'ISO-8859-1');
  else $xml = new DOMDocument('1.0', 'UTF-8');

  $root = $xml->createElementNS($xmlns, 'cfdi:Comprobante');

  //Si existe complemento del ine

  if($cpnum > 0) $schema.= '  http://www.sat.gob.mx/CartaPorte20 http://www.sat.gob.mx/sitio_internet/cfd/CartaPorte/CartaPorte20.xsd';
  else $schema = "";
  $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
//  if($ncomp == 0){
    $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd'.$schema);
//  }

  if($cpnum > 0) $root->setAttribute('xmlns:cartaporte20', 'http://www.sat.gob.mx/CartaPorte20');

  //$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:implocal', 'http://www.sat.gob.mx/implocal');

  $root->setAttribute('Version', '4.0');
  if(!$folios){
    $folios="";
  }
  $root->setAttribute('Serie', $folios);
  $root->setAttribute('Folio', $folion);
  $root->setAttribute('Fecha', $fechasat);
  $root->setAttribute('FormaPago', $formapago);
  $root->setAttribute('NoCertificado', $model->cfdcertificado);
  $root->setAttribute('Sello', $sello);
  $root->setAttribute('Certificado', $certificadoss);

  if($condiciones)
    $root->setAttribute('CondicionesDePago', $condiciones);

  $root->setAttribute('SubTotal', number_format($subtotal,2,'.',''));

  if($descuento > 0)
    $root->setAttribute('Descuento', $descuento);

  $root->setAttribute('Moneda', $moneda);
  $root->setAttribute('TipoCambio', $valorm);
  $root->setAttribute('Total',  number_format($total,2,'.',''));
  $root->setAttribute('TipoDeComprobante', $ingoegr);
  $root->setAttribute('MetodoPago', $metodopago);
  $root->setAttribute('LugarExpedicion', $lugarexpedicion);
  $root->setAttribute('Exportacion', $exportacion);
  $xml->appendChild($root);

  $sqlr = 'SELECT DISTINCT(fr_tiporelacion)
           FROM factura_relacion WHERE fr_origen = "'.$factura.'"';
  $resultr = setq($sqlr) or die($sqlr);
  $resultrn = setq($sqlr) or die($sqlr);
  if($resultrn->num_rows > 0){
    while($rowr = $resultr->fetch_array()){
      $relacion = $xml->createElement('cfdi:CfdiRelacionados');
      $relacion->setAttribute('TipoRelacion', str_pad($rowr['fr_tiporelacion'],2,"0",STR_PAD_LEFT));
      $relacion = $root->appendChild($relacion);
      $sqlr = 'SELECT fr_uuid FROM factura_relacion
               WHERE fr_origen = "'.$factura.'" AND fr_tiporelacion = "'.$rowr['fr_tiporelacion'].'"';
      $resultrf = setq($sqlr) or die($sqlr);
      while($rowrf = $resultrf->fetch_array()){
        $relacionud = $xml->createElement('cfdi:CfdiRelacionado');
        $relacionud->setAttribute('UUID', $rowrf['fr_uuid']);
        $relacionud = $relacion->appendChild($relacionud);
      }
    }
 }

  if($modelclient->rfc == "XAXX010101000" && busca($ticket,'ncredito_global','fg_id','COUNT(*)') > 0){
    $sql = 'SELECT fg_periodicidad,fg_meses,fg_ano FROM ncredito_global WHERE fg_id = "'.$_GET['id'].'"';
    $result = setq($sql);
    list($periodicidad,$meses,$ano) = $result->fetch_array();

    $Global = $xml->createElement('cfdi:InformacionGlobal');
    $Global->setAttribute('Periodicidad', $periodicidad);
    $Global->setAttribute('Meses', $meses);
    $Global->setAttribute('Año', $ano);
    $Global = $root->appendChild($Global);
  }


  $Emisor = $xml->createElement('cfdi:Emisor');
  $Emisor->setAttribute('Rfc', $model->rfc);
  $Emisor->setAttribute('Nombre', clearvmayus($model->razons));
  $Emisor->setAttribute('RegimenFiscal', $regimenfis);
  $Emisor = $root->appendChild($Emisor);

  $Receptor = $xml->createElement('cfdi:Receptor');
  $Receptor->setAttribute('Rfc', $modelclient->rfc);
  $Receptor->setAttribute('Nombre', $modelclient->razonsocial);
  $Receptor->setAttribute('DomicilioFiscalReceptor', $modelclient->cp);
  $Receptor->setAttribute('RegimenFiscalReceptor', $modelclient->regimen);
  $Receptor->setAttribute('UsoCFDI', busca($factura,'ncredito','n_id','n_uso'));

/*
  if(busca($cliente, 'clientes', 'c_id', 'c_rfc') == "XEXX010101000"){
    $Receptor->setAttribute('ResidenciaFiscal', "JPN");
    $Receptor->setAttribute('NumRegIdTrib', "XEXX010101000");
  }

*/  $Receptor = $root->appendChild($Receptor);

  $Conceptos = $xml->createElement('cfdi:Conceptos');
  $Conceptos = $root->appendChild($Conceptos);
  $m=0;
  $comen = busca($ticket,'adendas','a_tipo = "C" AND a_factura','COUNT(*)');
  while ($rowtd = $resulttd->fetch_array()) {
     $m++;
     $descripcion = str_replace("  "," ",$rowtd['d_concepto']);
     $descripcion = str_replace(array("\n","\r","\t","\e","\v")," ",$descripcion);
     $descripcion = clearvmayus($descripcion);
     $descripcion = str_replace("  "," ",$descripcion);
     $descripcion = str_replace("  "," ",$descripcion);
     $descripcion = str_replace("  "," ",$descripcion);
     $descripcion = trim(substr($descripcion,0,999));

     $unidad = str_replace("  "," ",$rowtd['d_unidad']);
     $unidad = str_replace(array("\n","\r","\t","\e","\v")," ",$unidad);
     $unidad = clearvmayus($unidad);
     $unidad = str_replace("  "," ",$unidad);
     $claveun = busca($unidad,'unidades','u_empresa = "'.$_SESSION['emp'].'" AND u_nmb','u_clavesat');

     $importe=($rowtd['d_costo'] * $rowtd['d_cantidad']);

     $importe=round($importe,6);
     $subtotal+=$importe;
     $Concepto = $xml->createElement('cfdi:Concepto');
     $Concepto->setAttribute('ClaveProdServ', $rowtd['d_producto']);
     $Concepto->setAttribute('ClaveUnidad', $claveun);

     if($rowtd['d_noid'] != NULL && $rowtd['d_noid'] != "")
        $Concepto->setAttribute('NoIdentificacion', $rowtd['d_noid']);

     $Concepto->setAttribute('Cantidad', $rowtd['d_cantidad']);
     $Concepto->setAttribute('Unidad', $unidad);
     $Concepto->setAttribute('Descripcion', $descripcion);
     $Concepto->setAttribute('ValorUnitario', number_format($rowtd['d_costo'],6,'.',''));
     $Concepto->setAttribute('Importe', number_format($importe,6,'.',''));

     if($amortizacion == "1"){
       $amortizacion = "0";
       $Concepto->setAttribute('Descuento', number_format($montoamortizacion,2,'.',''));
     }

     elseif($rowtd['d_descuento'] > 0)
        $Concepto->setAttribute('Descuento', number_format($rowtd['d_descuento'],2,'.',''));


     $sqlfi = 'SELECT * FROM ncreditod_imp
               WHERE ni_ncredito = "'.$factura.'" AND ni_id = "'.$rowtd['d_id'].'" AND ni_tr = "T"
               AND ni_base > 0
               ORDER BY ni_impuesto';
     $resultfi = setq($sqlfi) or die($sqlfi);
     $resultfin = setq($sqlfi) or die($sqlfi);
     $numimp = $resultfin->num_rows;

     if($numimp > 0){
        $impcon = $xml->createElement('cfdi:Impuestos');
        $trascon = $xml->createElement('cfdi:Traslados');
        $Concepto->setAttribute('ObjetoImp', "02");
     }
     else
      $Concepto->setAttribute('ObjetoImp', "01");

    $Concepto = $Conceptos->appendChild($Concepto);

     $numdim = 0;
     while($rowfi = $resultfi->fetch_array()){
      $numdim++;
      if($rowfi['ni_importe'] == 0) $rowfi['ni_tasac'] = 0;

//      if($rowfi['ni_impuesto'] == "002" && $rowfi['ni_tr'] == "T" && $rowfi['ni_ncredito'] == "62175") $rowfi['ni_importe']-=0.01;

      $trascond = $xml->createElement('cfdi:Traslado');
      $trascond->setAttribute('Base', number_format($rowfi['ni_base'],2,'.',''));
      $trascond->setAttribute('Impuesto', $rowfi['ni_impuesto']);
      $trascond->setAttribute('TipoFactor', $rowfi['ni_tipof']);
      $trascond->setAttribute('TasaOCuota', number_format($rowfi['ni_tasac'],6,'.',''));
      $trascond->setAttribute('Importe', number_format($rowfi['ni_importe'],6,'.',''));
      $trascond = $trascon->appendChild($trascond);
    }

    if($numdim > 0)
        $trascon = $impcon->appendChild($trascon);

    if(busca($factura,'ncreditod_imp','ni_tr = "R" AND ni_id = "'.$rowtd['d_id'].'" AND ni_ncredito','COUNT(*)')> 0){
        $retcon = $xml->createElement('cfdi:Retenciones');
        $sqlfi = 'SELECT * FROM ncreditod_imp
                  WHERE ni_ncredito = "'.$factura.'" AND ni_id = "'.$rowtd['d_id'].'" AND ni_tr = "R"
                  ORDER BY ni_impuesto';
       $resultfi = setq($sqlfi) or die($sqlfi);
       while($rowfi = $resultfi->fetch_array()){
        $trascond = $xml->createElement('cfdi:Retencion');
        $trascond->setAttribute('Base', number_format($rowfi['ni_base'],2,'.',''));
        $trascond->setAttribute('Impuesto', $rowfi['ni_impuesto']);
        $trascond->setAttribute('TipoFactor', $rowfi['ni_tipof']);
        $trascond->setAttribute('TasaOCuota', number_format($rowfi['ni_tasac'],6,'.',''));
        $trascond->setAttribute('Importe', number_format($rowfi['ni_importe'],6,'.',''));
        $trascond = $retcon->appendChild($trascond);
      }

        $retcon = $impcon->appendChild($retcon);
     }

     if($numimp > 0)
        $impcon = $Concepto->appendChild($impcon);

    }

    if($totalimpuestosret > 0 || $totalimpuestostras > 0){
      $impuestos = $xml->createElement('cfdi:Impuestos');
      $impuestos->setAttribute('TotalImpuestosTrasladados', number_format($totalimpuestostras,2,'.',''));
    }
    if($totalimpuestosret > 0){
      $impuestos->setAttribute('TotalImpuestosRetenidos', number_format($totalimpuestosret,2,'.',''));
      $impuestos = $root->appendChild($impuestos);
    }

    if($totalimpuestosret > 0 || $totalimpuestostras > 0) $impuestos = $root->appendChild($impuestos);

  /**************Retenciones************************/
  if($totalimpuestosret > 0){
    $Retenciones = $xml->createElement('cfdi:Retenciones');
    $Retenciones = $impuestos->appendChild($Retenciones);

    $sqlr = 'SELECT SUM(ni_importe) importe,ni_impuesto impuesto FROM ncreditod_imp
              WHERE ni_ncredito = "'.$factura.'" AND ni_tr = "R"
              GROUP BY ni_impuesto';
    $resultr = setq($sqlr) or die($sqlr);
    while($rowr = $resultr->fetch_array()){
      $Retenciond = $xml->createElement('cfdi:Retencion');
      $Retenciond->setAttribute('Impuesto', $rowr['impuesto']);
      $Retenciond->setAttribute('Importe', number_format($rowr['importe'],2,'.',''));
      $Retenciond = $Retenciones->appendChild($Retenciond);

    }
  }
/**************Traslados************************/

if($totalimpuestostras > 0){
  $Tralados = $xml->createElement('cfdi:Traslados');
  $Tralados = $impuestos->appendChild($Tralados);

  $sqlt = 'SELECT SUM(ni_importe) importe,SUM(ni_base) sumabase,ni_impuesto impuesto,ni_tipof tipof,ni_tasac tasac
           FROM ncreditod_imp
            WHERE ni_ncredito = "'.$factura.'" AND ni_tr = "T"
            GROUP BY ni_impuesto,ni_tipof,ni_tasac';
  $resultt = setq($sqlt) or die($sqlt);
  while($rowtr = $resultt->fetch_array()){
    if($rowtr['importe'] == 0) $rowtr['tasac'] = 0;

    $Traladod = $xml->createElement('cfdi:Traslado');
    $Traladod->setAttribute('Base', $rowtr['sumabase']);
    $Traladod->setAttribute('Impuesto', $rowtr['impuesto']);
    $Traladod->setAttribute('TipoFactor', $rowtr['tipof']);
    $Traladod->setAttribute('TasaOCuota', number_format($rowtr['tasac'],6,'.',''));
    $Traladod->setAttribute('Importe', number_format($rowtr['importe'],2,'.',''));
    $Traladod = $Tralados->appendChild($Traladod);
  }
}

  $xml->formatOutput = true;

  //Guardar el xml como un archivo de String, es decir, poner los string en la variable $strings_xml:

  $strings_xml = $xml->saveXML();
  $xml->formatOutput = true;

  $xml->save('cfd/' . $empresa . '/xml/'.$folio . '.xml');


  die("Error en Generar XML intentar de nuevo: ".'cfd/' . $empresa . '/xml/'.$folio . '.xml');

 //die("Error en Generar XML 40 intentar de nuevo: ".'cfd/' . $empresa . '/xml/'.$folio . '.xml');
 return(true);

  }
}
?>