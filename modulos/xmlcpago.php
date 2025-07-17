<?php
//ini_set('display_errors', 1);
class xml {

    var $model;
    var $view;

    function xml() {
        $this->model = new Modelxml(); // crea modelo
    }

    function index($factura) {
       $this->model->resultxml($factura);
    }

}

class Modelxml {

    function resultxml($factura) {
      $id = $factura;
      $emp = busca($factura,'cpago','c_id','c_empresa');
      $folioinf = "P";

      $folion = busca($folioinf, 'foliosinf', 'f_empresa = "'.$emp.'" AND f_tipofac', 'f_folio');
      $folios = busca($folioinf, 'foliosinf', 'f_empresa = "'.$emp.'" AND f_tipofac', 'f_serie');
      $folio = $folios . $folion;

        $sqlup = 'UPDATE cpago SET c_folio = "'.$folio.'"
                  WHERE c_id = "'.$factura.'"';
        setq($sqlup) or die($sqlup);

  include_once('modulos/config.php');
  $model = new modelconfig();
  $model->select($emp);

        if(file_exists('cfd/'.$emp.'/' . busca($emp,'empresas','e_id','e_rfc') . '.cer.pem')){
            $file = fopen('cfd/'.$emp.'/' . busca($emp,'empresas','e_id','e_rfc') . '.cer.pem', "r") or die("No se pudo abrir el Certificado!");

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
          die('Falta Certificado '.busca($emp,'empresas','e_id','e_certificado').'<br>'.'cfd/'.$emp.'/' . busca($emp,'empresas','e_id','e_certificado') . '.cer.pem');
        }

        if(file_exists('cfd/'.$emp.'/' . busca($emp,'empresas','e_id','e_rfc') . '.key.pem')){

        $filekey = fopen('cfd/'.$emp.'/' . busca($emp,'empresas','e_id','e_rfc') . '.key.pem', "r") or die("No se pudo abrir el Certificado!");

        if (!isset($contenidokey))
          $contenidokey = NULL;
        while (!feof($filekey)) {
          $contenidokey.=fgets($filekey) . "<br />";
        }
        fclose($filekey);
       /*--------------------------------------------------------------------------------------*/
        $certificadokey = explode('-----', $contenidokey);

        /*--------------------------------------------------------------------------------------*/
        }else{
          die('Falta Key');
        }
        $subtotal = 0;
        $total = 0;
        $sqlt = 'SELECT * FROM cpago WHERE c_id="' . $factura . '"';
        $resultt = setq($sqlt) or die(mysql_error());
        $rowt = $resultt->fetch_array();

        $sql = 'SELECT * FROM cpagod WHERE cd_cpago="' . $factura . '"';
        $resulttd = setq($sql) or die(mysql_error());
        $resulttdd = setq($sql) or die(mysql_error());

        $cadrela = "";
        $sqlr = 'SELECT DISTINCT(cr_tiporelacion) FROM cpago_relacion WHERE cr_origen = "'.$factura.'"';
        $resultr = setq($sqlr) or die($sqlr);
        while($rowr = $resultr->fetch_array()){
          $cadrela.='|'.$rowr['cr_tiporelacion'];
          $sqlr = 'SELECT cr_uuid FROM cpago_relacion
                   WHERE cr_origen = "'.$factura.'" AND cr_tiporelacion = "'.$rowr['cr_tiporelacion'].'"';
          $resultrf = setq($sqlr) or die($sqlr);
          while($rowrf = $resultrf->fetch_array()){
            $cadrela.='|'.$rowrf['cr_uuid'];
          }
        }

       $cadena='|84111506|1|ACT|Pago|0|0|01';

       $ftimbra= explode(' ',date('Y-m-d H:i:s',strtotime('- 60 seconds')));
       $fpago= explode(' ',date('Y-m-d H:i:s',strtotime($rowt['c_fecha'])));

       $fechasat = $ftimbra[0].'T'.$ftimbra[1];
       $fechasatp = $fpago[0].'T'.$fpago[1];

       $moneda = "XXX";
       $tipocam = 1;
       $lugarexpedicion = $model->cp;

       $regimenfis = $model->regimen;

       $cadregimen = $regimenfis;
       $cliente = busca($factura,'cpago','c_id','c_cliente');
       $fiscal = busca($factura,'cpago','c_id','c_fiscales');
       $leytipo = "P";
       $cpfis = busca(busca($fiscal, 'crm_fiscales', 'cf_id', 'cf_direccion'),'crm_direcciones','cd_id','cd_cp');
       $regimenrec = busca($fiscal, 'crm_fiscales', 'cf_id', 'cf_regimen');

      $cadenaorign = "";
      $cadenaorig = "";
/*Pago*/ $cadenaorign.= '|'.$model->cfdcertificado.'|'.number_format($subtotal,0,'.','');
/*Pag2*/ $cadenaorign.= '|'.$moneda.'|'.number_format($total,0,'.','').'|'.$leytipo.'|01|'.$lugarexpedicion.'';

/*CfRe*/ $cadenaorign.=$cadrela;
/*Emis*/ $cadenaorign.='|'.$model->rfc.'|'.$model->razons.'|'.$cadregimen;
/*Rece*/ $cadenaorign.='|'.busca($fiscal, 'crm_fiscales', 'cf_id', 'cf_rfc').'|'.busca($fiscal, 'crm_fiscales', 'cf_id', 'cf_razonsocial').'|'.$cpfis.'|'.$regimenrec.'|CP01';
/*Conc*/ $cadenaorign.=$cadena;


        $fechap = busca($factura,'cpago','c_id','c_fecha');
        $fechapago = explode(" ",$fechap);
        $fechapag = $fechapago[0].'T'.$fechapago[1];

        $formapagop = busca($factura,'cpago','c_id','c_fpago');
        $numoperacion = busca($factura,'cpago','c_id','c_confirmacion');
        $formapp = busca($formapagop,'cfdi_fpago','cf_id','cf_nmb');

        $monto = busca($factura,'cpagod','cd_tipo = "F" AND cd_cpago','SUM(cd_imppagado)');
        $notasc = busca($factura,'cpagod','cd_tipo = "N" AND cd_cpago','SUM(cd_imppagado)');
        if($notasc) $monto-=$notasc;

        $cadenaorig.='|'.$fechapag.'|'.$formapp.'|MXN|1|'.number_format($monto,2,'.','').'|'.$numoperacion;

        /* $bancocl = busca($factura,'cpago','c_id','c_bancoe');
        if($bancocl)
        $cadenaorig.='|'.$bancocl; */

        /* $cuentacl = busca($factura,'cpago','c_id','c_cuentae');
        if($cuentacl)
        $cadenaorig.='|'.$cuentacl; */

        $retiva = 0;
        $risr = 0;
        $tieps = 0;
        $baseiva = 0;
        $triva = 0;
        $baseiva0 = 0;
        $triva0 = 0;

        $sql = 'SELECT * FROM cpagod WHERE cd_cpago = "'.$factura.'" AND cd_tipo = "F" ORDER BY cd_id';
        $result = setq($sql) or die($sql);
        $resultn = setq($sql) or die($sql);
        $numpa = $resultn->num_rows;
        while($row = $result->fetch_array()){
          $cadenaorig.='|'.$row['cd_uuid'];
          if($row['cd_factura'] != 0)
            $cadenaorig.='|'.busca($row['cd_factura'],'facturas','f_id','f_serie').'|'.busca($row['cd_factura'],'facturas','f_id','f_nfolio');

          $cadenaorig.='|'.$row['cd_moneda'].'|1';
          $saldoin =$row['cd_impsaldoant']-$row['cd_imppagado'];
          $cadenaorig.='|'.$row['cd_nparc'].'|'.number_format($row['cd_impsaldoant'],2,'.','').'|'.number_format($row['cd_imppagado'],2,'.','').'|'.number_format($saldoin,2,'.','').'|01';

          if($row['cd_triva16'] > 0){
            $cadenaorig.='|'.number_format($row['cd_baseiva16'],2,'.','').'|002|Tasa|'.number_format(0.16,6,'.','').'|'.number_format($row['cd_triva16'],2,'.','');
          }
          $retiva+=$row['cd_retiva'];
          $risr+=$row['cd_retirs'];
          $tieps+=$row['cd_retieps'];
          $baseiva+=$row['cd_baseiva16'];
          $triva+=$row['cd_triva16'];
          $baseiva0+=$row['cd_baseiva0'];
          $triva0+=$row['cd_triva0'];
        }
        $versionp = "|2.0";
        $cadenaorign.=$versionp;

        $cadtotales = "";
        if($retiva > 0) $cadtotales.='|'.number_format($retiva,2,'.','');
        if($risr > 0) $cadtotales.='|'.number_format($risr,2,'.','');
        if($tieps > 0) $cadtotales.='|'.number_format($tieps,2,'.','');
        if($baseiva > 0) $cadtotales.='|'.number_format($baseiva,2,'.','');
        if($triva > 0) $cadtotales.='|'.number_format($triva,2,'.','');
        if($baseiva0 > 0) $cadtotales.='|'.number_format($baseiva0,2,'.','');
        if($triva0 > 0) $cadtotales.='|'.number_format($triva0,2,'.','');

//        $totalpagado = round($retiva,2)+round($risr,2)+round($tieps,2)+round($baseiva,2)+round($triva,2)+round($baseiva0,2)+round($triva0,2);

        $cadenaorign.=$cadtotales.'|'.number_format($monto,2,'.','');

        if($triva > 0 || $retiva > 0 || $risr > 0){
          if($triva > 0){
            $cadenaorig.='|'.number_format($baseiva,2,'.','').'|002|Tasa|'.number_format(0.16,6,'.','').'|'.number_format($triva,2,'.','');
          }
        }
    /*Comp*/ $cadenaorign.=$cadenaorig;


       $version = "4.0";
       $cadenaorign = str_replace("  "," ",$cadenaorign);
       $cadenaorign = str_replace(" |","|",$cadenaorign);
       $cadenaorign = str_replace("| ","|",$cadenaorign);
       $cadenaorign = str_replace(array("\n","\r","\t","\e","\v")," ",$cadenaorign);
       $cadenaorign = str_replace("  "," ",$cadenaorign);
       $cadenaorign = '||'.$version.'|'.$folios.'|'.$folion.'|'.$fechasat.$cadenaorign.'||';

       $cadenaoriginal = '';

       for($i=0; $i<strlen($cadenaorign); $i++){
         if($cadenaorign[$i] == " "){
           if($espacio == 0)
            $cadenaoriginal.=$cadenaorign[$i];

            $espacio = 1;
         }
         else{
           $cadenaoriginal.=$cadenaorign[$i];
           $espacio = 0;
         }
       }

//         die($cadenaoriginal);

      if (!is_dir('cfd/'.$emp.'')) {
          @mkdir('cfd/'.$emp.'', 0777);
      }
       if (!is_dir('cfd/'.$emp.'')) {
          @mkdir('cfd/'.$emp.'', 0777);
      }
      if (!is_dir('cfd/'.$emp.'/cpago')) {
          @mkdir('cfd/'.$emp.'/cpago', 0777);
      }
      if (!is_dir('cfd/'.$emp.'/cpago/xml')) {
          @mkdir('cfd/'.$emp.'/cpago/xml', 0777);
      }

        $handle=fopen('cfd/'.$emp.'/cpago/xml/x.txt','w');  fwrite($handle, $cadenaoriginal); fclose($handle);

        /******************************************************/
        $file='cfd/'.$emp.'/' . busca($emp,'empresas','e_id','e_rfc') . '.key.pem';      // Ruta al archivo
        $cadena_original = $cadenaoriginal;
        //echo $cadena_original.'<br>';
        $pkeyid = openssl_get_privatekey(file_get_contents($file));
        //echo 'llave: '.$pkeyid.'<br>';
        openssl_sign($cadena_original, $crypttext, $pkeyid, OPENSSL_ALGO_SHA256);
        openssl_free_key($pkeyid);
        $sello = base64_encode($crypttext);             // lo codifica en formato base64
        //echo "sello: ".$sello;
        /******************************************************/
        $xmlns = 'http://www.sat.gob.mx/cfd/4';
        $xml = new DOMDocument('1.0', 'utf-8');

        $root = $xml->createElementNS($xmlns, 'cfdi:Comprobante');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/Pagos20 http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd');
        $leytipo = "P";

        $root->setAttribute('Version', $version);
        $root->setAttribute('Serie', $folios);
        $root->setAttribute('Folio', $folion);
        $root->setAttribute('Fecha', $fechasat);
        $root->setAttribute('NoCertificado', $model->cfdcertificado);
        $root->setAttribute('Certificado', $certificadoss);
        $root->setAttribute('Sello', $sello);
        $root->setAttribute('SubTotal', number_format($subtotal,0,'.',''));
        $root->setAttribute('Moneda', $moneda);
        $root->setAttribute('Total',  number_format($total,0,'.',''));
        $root->setAttribute('TipoDeComprobante', $leytipo);
        $root->setAttribute('LugarExpedicion', clearvmayus($lugarexpedicion));
        $root->setAttribute('Exportacion', "01");
        $xml->appendChild($root);

        $sqlr = 'SELECT DISTINCT(cr_tiporelacion)
                 FROM cpago_relacion WHERE cr_origen = "'.$factura.'"';
        $resultr = setq($sqlr) or die($sqlr);
        $resultrn = setq($sqlr) or die($sqlr);
        if($resultrn->num_rows > 0){
          while($rowr = $resultr->fetch_array()){
            $relacion = $xml->createElement('cfdi:CfdiRelacionados');
            $relacion->setAttribute('TipoRelacion', $rowr['cr_tiporelacion']);
            $relacion = $root->appendChild($relacion);
            $sqlr = 'SELECT cr_uuid FROM cpago_relacion
                     WHERE cr_origen = "'.$factura.'" AND cr_tiporelacion = "'.$rowr['cr_tiporelacion'].'"';
            $resultrf = setq($sqlr) or die($sqlr);
            while($rowrf = $resultrf->fetch_array()){
              $relacionud = $xml->createElement('cfdi:CfdiRelacionado');
              $relacionud->setAttribute('UUID', $rowrf['cr_uuid']);
              $relacionud = $relacion->appendChild($relacionud);
            }
          }
       }

        $Emisor = $xml->createElement('cfdi:Emisor');
        $Emisor->setAttribute('Rfc', $model->rfc);
        $Emisor->setAttribute('Nombre', clearvmayus($model->razons));
        $Emisor->setAttribute('RegimenFiscal',clearvmayus($cadregimen));
        $Emisor = $root->appendChild($Emisor);

        $Receptor = $xml->createElement('cfdi:Receptor');
        $Receptor->setAttribute('Rfc', busca($rowt['c_fiscales'], 'crm_fiscales', 'cf_id', 'cf_rfc'));
        $Receptor->setAttribute('Nombre', busca($rowt['c_fiscales'], 'crm_fiscales', 'cf_id', 'cf_razonsocial'));
        $Receptor->setAttribute('DomicilioFiscalReceptor',clearvmayus($cpfis));
        $Receptor->setAttribute('RegimenFiscalReceptor',clearvmayus($regimenrec));
        $Receptor->setAttribute('UsoCFDI',clearvmayus($rowt['c_uso']));
        $Receptor = $root->appendChild($Receptor);

        $Conceptos = $xml->createElement('cfdi:Conceptos');
        $Conceptos = $root->appendChild($Conceptos);
        $m=0;
        $tot2 = 0;
        $tot3 = 0;

        $Concepto = $xml->createElement('cfdi:Concepto');
        $Concepto->setAttribute('ClaveProdServ', "84111506");
        $Concepto->setAttribute('Cantidad', "1");
        $Concepto->setAttribute('ClaveUnidad', "ACT");
        $Concepto->setAttribute('Descripcion', "Pago");
        $Concepto->setAttribute('ValorUnitario', number_format(0,0,'.',''));
        $Concepto->setAttribute('Importe', number_format(0,0,'.',''));
        $Concepto->setAttribute('ObjetoImp', "01");
        $Concepto = $Conceptos->appendChild($Concepto);

        $Complemento = $xml->createElement('cfdi:Complemento');
        $Complemento = $root->appendChild($Complemento);

//        $Pagos20 = $xml->createElement('pago20:Pagos');
//        $Pagos20->setAttribute('xsi:schemaLocation', '');
        $Pagos20 = $xml->createElement('pago20:Pagos');
        $Pagos20->setAttribute('xmlns:pago20', 'http://www.sat.gob.mx/Pagos20');
        $Pagos20->setAttribute('Version', "2.0");
        $Pagos20 = $Complemento->appendChild($Pagos20);

        $Totales = $xml->createElement('pago20:Totales');
        $Totales = $Pagos20->appendChild($Totales);

        $Pago = $xml->createElement('pago20:Pago');
        $Pago->setAttribute('FechaPago', $fechapag);
        $Pago->setAttribute('FormaDePagoP', $formapp);
        $Pago->setAttribute('MonedaP', "MXN");
        $Pago->setAttribute('TipoCambioP', "1");
        $Pago->setAttribute('Monto', number_format($monto,2,'.',''));
        $Pago->setAttribute('NumOperacion', $numoperacion);
        /* if($bancocl)
        $Pago->setAttribute('NomBancoOrdExt', $bancocl);

        if($cuentacl)
        $Pago->setAttribute('CtaOrdenante', $cuentacl); */

        $Pago = $Pagos20->appendChild($Pago);

        $sql = 'SELECT * FROM cpagod WHERE cd_cpago = "'.$factura.'" AND cd_tipo = "F"  ORDER BY cd_id';
        $result = setq($sql) or die($sql);
        $resultn = setq($sql) or die($sql);
        $numpa = $resultn->num_rows;
        $totalpagos = $row['cd_imppagado'];
        while($row = $result->fetch_array()){
          $docto = $xml->createElement('pago20:DoctoRelacionado');
          $docto->setAttribute('IdDocumento', $row['cd_uuid']);
          if($row['cd_factura'] != 0 AND $row['cd_tipo'] == "F"){
            $docto->setAttribute('Serie', busca($row['cd_factura'],'facturas','f_id','f_serie'));
            $docto->setAttribute('Folio', busca($row['cd_factura'],'facturas','f_id','f_nfolio'));
          }
          if($row['cd_factura'] != 0 AND $row['cd_tipo'] == "N"){
            $docto->setAttribute('Serie', busca($row['cd_uuid'],'ncredito','n_uuid','n_serie'));
            $docto->setAttribute('Folio', busca($row['cd_uuid'],'ncredito','n_uuid','n_nfolio'));
          }

          $docto->setAttribute('MonedaDR', $row['cd_moneda']);
          $docto->setAttribute('EquivalenciaDR', 1);

          if($row['cd_moneda'] != "MXN")
            $docto->setAttribute('TipoCambioDR', $row['cd_tipocambio']);

          $docto->setAttribute('NumParcialidad', $row['cd_nparc']);
          $docto->setAttribute('ImpSaldoAnt', number_format($row['cd_impsaldoant'],2,'.',''));
          $saldoin =$row['cd_impsaldoant']-$row['cd_imppagado'];
          $docto->setAttribute('ImpPagado', number_format($row['cd_imppagado'],2,'.',''));
          $docto->setAttribute('ImpSaldoInsoluto', number_format($saldoin,2,'.',''));
          $docto->setAttribute('ObjetoImpDR', "02");

          $totalpagos+=$row['cd_imppagado'];
          $docto = $Pago->appendChild($docto);

          $impp = $xml->createElement('pago20:ImpuestosDR');
          $impp = $docto->appendChild($impp);
          /* if($row['cd_retiva'] > 0 || $row['cd_retirs'] > 0){ */
            $impr = $xml->createElement('pago20:RetencionesDR');
            $impr->setAttribute('BaseDR', "01");
            $impr = $impp->appendChild($impr);
          /* } */
          /* if($row['cd_triva16'] > 0){ */
            $imptdr = $xml->createElement('pago20:TrasladosDR');
            $imptdr = $impp->appendChild($imptdr);

            $impt = $xml->createElement('pago20:TrasladoDR');
            $impt->setAttribute('BaseDR', number_format($row['cd_baseiva16'],2,'.',''));
            $impt->setAttribute('ImpuestoDR', "002");
            $impt->setAttribute('TipoFactorDR', "Tasa");
            $impt->setAttribute('TasaOCuotaDR', number_format(0.16,6,'.',''));
            $impt->setAttribute('ImporteDR', number_format($row['cd_triva16'],2,'.',''));
            $impt = $imptdr->appendChild($impt);
          /* } */
        }

      if($triva > 0 || $retiva > 0 || $risr > 0){
        $imptp = $xml->createElement('pago20:ImpuestosP');
        $imptp = $Pago->appendChild($imptp);

        if($triva > 0){
          $imptp20 = $xml->createElement('pago20:TrasladosP');
          $imptp20 = $imptp->appendChild($imptp20);

          $imptup = $xml->createElement('pago20:TrasladoP');
          $imptup->setAttribute('BaseP', number_format($baseiva,2,'.',''));
          $imptup->setAttribute('ImpuestoP', "002");
          $imptup->setAttribute('TipoFactorP', "Tasa");
          $imptup->setAttribute('TasaOCuotaP', number_format(0.16,6,'.',''));
          $imptup->setAttribute('ImporteP', number_format($triva,2,'.',''));
          $imptup = $imptp20->appendChild($imptup);
        }
      }


      if($retiva > 0) $Totales->setAttribute('TotalRetencionesIVA', number_format($retiva,2,'.',''));
      if($risr > 0) $Totales->setAttribute('TotalRetencionesISR', number_format($risr,2,'.',''));
      if($tieps > 0) $Totales->setAttribute('TotalRetencionesIEPS', number_format($tieps,2,'.',''));
      if($baseiva > 0) $Totales->setAttribute('TotalTrasladosBaseIVA16', number_format($baseiva,2,'.',''));
      if($triva > 0) $Totales->setAttribute('TotalTrasladosImpuestoIVA16', number_format($triva,2,'.',''));
      if($baseiva0 > 0) $Totales->setAttribute('TotalTrasladosBaseIVA0', number_format($baseiva0,2,'.',''));
      if($triva0 > 0) $Totales->setAttribute('TotalTrasladosImpuestoIVA0', number_format($triva0,2,'.',''));

      $Totales->setAttribute('MontoTotalPagos', number_format($totalpagos,2,'.',''));
      $xml->formatOutput = true;

      //Guardar el xml como un archivo de String, es decir, poner los string en la variable $strings_xml:

      $strings_xml = $xml->saveXML();

      //Finalmente, guardarlo en un directorio:
      $xml->save('cfd/'.$emp.'/cpago/xml/'.$folio . '.xml');

  //  die("prueba: cfd/$emp/cpago/xml/$folio.xml");

    return(true);
    }

}

?>