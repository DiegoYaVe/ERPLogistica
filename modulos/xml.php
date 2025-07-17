<?php
	//init_set('display_errors',1);
	class xml{
		var $model;
		var $view;
		function __construct(){
			$this->model = new Modelxml();
		}
		function index($ticket){
			$this->model->resultxml($ticket);
		}
	}

	class Modelxml{
		function resultxml($ticket){
			$factura = $ticket;
			$empf = busca($factura,'facturas','f_id','f_empresa');
      if(!$empf) $empf = 1;

			$folioinf = "F";
      $folion = busca($folioinf, 'foliosinf', 'f_empresa = "'.$empf.'" AND f_tipofac', 'f_folio');
      $folios = busca($folioinf, 'foliosinf', 'f_empresa = "'.$empf.'" AND f_tipofac', 'f_serie');
      $folio = $folios . $folion;

      if(busca($factura,'facturas','f_id','f_estatus') == "T"){
        die('<script>
          document.location.href="?modulo=facturas&accion=showfactura&factura='.$factura.'";
        </script>');
      }

      if(busca($empf,'facturas','f_estatus IN ("T","C") AND f_folio = "'.$folio.'" AND f_empresa','COUNT(*)') > 0){
        die(alert_back("Error: El Folio ".$folio.' ya esta usado',true));
      }

      if(busca($factura,'facturas','f_estatus IN ("T","C") AND f_id','COUNT(*)') > 0){
        die(alert_back("La Factura ha sido previamente Timbrada",true));
      }

      if(!$folion OR empty($folion)){
        die("SE REQUIERE UN FOLIO");
      }elseif(!$folios OR empty($folios)){
        die("SE REQUIERE UNA SERIE");
      }

      $sqlup = 'UPDATE facturas SET f_folio = "'.$folio.'", f_serie = "'.$folios.'", f_nfolio = "'.$folion.'"
                WHERE f_id = "'.$ticket.'"';
      setq($sqlup) or die($sqlup);

      if(file_exists('cfd/' . $empf . '/' . busca($empf,'empresas','e_id','e_rfc') . '.cer.pem')){
        $file = fopen('cfd/' . $empf . '/' . busca($empf,'empresas','e_id','e_rfc') . '.cer.pem', "r") or die("No se pudo abrir el Certificado!");
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

      if(file_exists('cfd/' . $empf . '/' . busca($empf,'empresas','e_id','e_rfc') . '.key.pem')){
        $filekey = fopen('cfd/' . $empf . '/' . busca($empf,'empresas','e_id','e_rfc') . '.key.pem', "r") or die("No se pudo abrir el Certificado!");

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

      $sql = 'SELECT * FROM facturasd WHERE d_factura ="' . $ticket . '" ORDER BY d_id ASC';
      $resulttd = setq($sql) or die(mysql_error());

      $cad0 = ''; $cad1 = ''; $cad2 = ''; $cad3 = ''; $cad4 = ''; $cad5 = ''; $cad6 = ''; $cad7 = ''; $cad8 = '';
      $subtotal = 0;
      $version = "4.0";
      $total = 0;
      $totalimpuestostras = 0;
      $totalimpuestosret = 0;
      $descuento = busca($ticket,'facturas','f_id','f_descuento');
      $metodopago = busca($ticket,'facturas','f_id','f_metodopago');
      $moneda = busca($ticket,'facturas','f_id','f_tipocambio');
      $valorm = busca($ticket,'facturas','f_id','f_valorm');
      $condiciones = busca($ticket,'facturas','f_id','f_condiciones');
      $formapago = strtoupper(busca(busca($ticket,'facturas','f_id','f_fpago'),'cfdi_fpago','cf_id','cf_nmb'));
      $lugarexpedicion = busca($empf, 'empresas', 'e_id', 'e_cp');
      $exportacion = "01";
      $regimenfis = busca($empf, 'empresas', 'e_id', 'e_regimen');
      $cliente = busca($factura,'facturas','f_id','f_cliente');
      $fiscales = busca($factura,'facturas','f_id','f_fiscales');

      if($moneda == "MXN")  $valorm = number_format($valorm,0);
      else  $valorm = number_format($valorm,4);

      $ftimbra= explode(' ',date('Y-m-d H:i:s',strtotime('- 60 seconds')));
      $fechasat = $ftimbra[0].'T'.$ftimbra[1];

      $amortizacion = "0";
      $ingoegr = "I";

      if($amortizacion == 1) $descuento = $montoamortizacion;

			$sql = 'SELECT COUNT(*) FROM carta_porte WHERE cp_factura = "'.$factura.'"';
      $result = setq($sql) or die($sql);
      list($cpnum) = $result->fetch_array();

      include_once ('modulos/clientes.php');
      $modelclient = new modelclientes();
      $modelclient->selectfisc($cliente,$fiscales);

      include_once('modulos/config.php');
      $model = new modelconfig();
      $model->select($empf);

      include_once("modulos/facturas.php");
      $facts = new ModelFacturas();
      $facts->selectfac($ticket);

      $xmlns = 'http://www.sat.gob.mx/cfd/4';
      $find_letters = array('Ñ','Á','É','Í','Ó','Ú','Ü','&');

			/*INICIO DE LA ESTRUCTURA XML*/
      $pos = strstr($modelclient->razonsocial, $find_letters);
//      if($pos || $modelclient->rfc == "XAXX010101000")
      $xml = new DOMDocument('1.0', 'ISO-8859-1');
  //    else $xml = new DOMDocument('1.0', 'UTF-8');

      /*ELEMENTO INICIAL CFDI:COMPROBANTE*/
      $root = $xml->createElementNS($xmlns, 'cfdi:Comprobante');
			if($cpnum > 0) $schema.= '  http://www.sat.gob.mx/CartaPorte20 http://www.sat.gob.mx/sitio_internet/cfd/CartaPorte/CartaPorte20.xsd';
      else $schema = "";
      $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
      $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd'.$schema);
      if($cpnum > 0) $root->setAttribute('xmlns:cartaporte20', 'http://www.sat.gob.mx/CartaPorte20');
      $root->setAttribute('Version', '4.0');
      if(!$folios) $folios="";
      $root->setAttribute('Serie', $folios);
      $root->setAttribute('Folio', $folion);
      $root->setAttribute('Fecha', $fechasat);
      $cad0 = '||'.$version.'|'.$folios.'|'.$folion.'|'.$fechasat;
      $cad2 = '|'.$formapago.'|'.$model->cfdcertificado;
      if($condiciones) $cad2 .= '|'.$condiciones;
      $xml->appendChild($root);
      /*FIN DE CFDI:COMPROBANTE*/

      $sqlr = 'SELECT DISTINCT(fr_tiporelacion)
                FROM factura_relacion WHERE fr_origen = "'.$factura.'"';
      $resultr = setq($sqlr) or die($sqlr);
      $resultrn = setq($sqlr) or die($sqlr);
      if($resultrn->num_rows > 0){
        while($rowr = $resultr->fetch_array()){
          $relacion = $xml->createElement('cfdi:CfdiRelacionados');
          $relacion->setAttribute('TipoRelacion', str_pad($rowr['fr_tiporelacion'],2,"0",STR_PAD_LEFT));
          $cad3 .= '|'.str_pad($rowr['fr_tiporelacion'],2,"0",STR_PAD_LEFT);
          $relacion = $root->appendChild($relacion);
          $sqlr = 'SELECT fr_uuid FROM factura_relacion WHERE fr_origen = "'.$factura.'" 
                    AND fr_tiporelacion = "'.$rowr['fr_tiporelacion'].'"';
          $resultrf = setq($sqlr) or die($sqlr);
          while($rowrf = $resultrf->fetch_array()){
            $relacionud = $xml->createElement('cfdi:CfdiRelacionado');
            $relacionud->setAttribute('UUID', $rowrf['fr_uuid']);
            $relacionud = $relacion->appendChild($relacionud);
            $cad3 .= '|'.$rowrf['fr_uuid'];
          }
        }
      }

      if($modelclient->rfc == "XAXX010101000" && busca($ticket,'facturas_global','fg_id','COUNT(*)') > 0){
        $sql = 'SELECT fg_periodicidad,fg_meses,fg_ano FROM facturas_global WHERE fg_id = "'.$_GET['id'].'"';
        $result = setq($sql);
        list($periodicidad,$meses,$ano) = $result->fetch_array();

        $Global = $xml->createElement('cfdi:InformacionGlobal');
        $Global->setAttribute('Periodicidad', $periodicidad);
        $Global->setAttribute('Meses', $meses);
        $Global->setAttribute('Año', $ano);
        $Global = $root->appendChild($Global);
        $cad4 = '|'.$periodicidad.'|'.$meses.'|'.$ano;
      }else $cad4 = '';

      /*ELEMENTO EMISOR*/
      $Emisor = $xml->createElement('cfdi:Emisor');
      $Emisor->setAttribute('Rfc', $model->rfc);
      $Emisor->setAttribute('Nombre', clearvmayus($model->razons));
      $Emisor->setAttribute('RegimenFiscal', $regimenfis);
      $Emisor = $root->appendChild($Emisor);
      $cad5 = '|'.$model->rfc.'|'.$model->razons.'|'.$regimenfis;
      /*FIN ELEMENTO EMISOR*/

      /*ELEMENTO RECEPTOR*/
      $Receptor = $xml->createElement('cfdi:Receptor');
      $Receptor->setAttribute('Rfc', $modelclient->rfc);
      $Receptor->setAttribute('Nombre', $modelclient->razonsocial);
      $Receptor->setAttribute('DomicilioFiscalReceptor', $modelclient->cp);
      $Receptor->setAttribute('RegimenFiscalReceptor', $modelclient->regimen);
      $Receptor->setAttribute('UsoCFDI', busca($factura,'facturas','f_id','f_uso'));
      $cad6 = '|'.$modelclient->rfc.'|'.$modelclient->razonsocial.'|'.$modelclient->cp.'|'.$modelclient->regimen.'|'.busca($factura,'facturas','f_id','f_uso');
      $Receptor = $root->appendChild($Receptor);
      /*FIN ELEMENTO RECEPTOR*/

      /*ELEMENTO CONCEPTOS*/
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

        if($rowtd['d_costo'] > 0){
          $importe=($rowtd['d_costo'] * $rowtd['d_cantidad'])-$rowtd['d_descuento'];
          $importefac = ($rowtd['d_costo'] * $rowtd['d_cantidad']);
        }

        //$importe=round($importe,6);
        $subtotal+=$importefac;
        $total+=$importe;

        $Concepto = $xml->createElement('cfdi:Concepto');
        $Concepto->setAttribute('ClaveProdServ', $rowtd['d_producto']);
        $Concepto->setAttribute('ClaveUnidad', $claveun);
        $cad7 .= '|'.$rowtd['d_producto'];
        if($rowtd['d_noid'] != NULL && $rowtd['d_noid'] != ""){
          $cad7 .= '|'.$rowtd['d_noid'];
          $Concepto->setAttribute('NoIdentificacion', $rowtd['d_noid']);
        }

        $Concepto->setAttribute('Cantidad', $rowtd['d_cantidad']);
        $Concepto->setAttribute('Unidad', $unidad);
        $Concepto->setAttribute('Descripcion', $descripcion);
        $Concepto->setAttribute('ValorUnitario', number_format($rowtd['d_costo'],6,'.',''));
        $Concepto->setAttribute('Importe', number_format($importe,6,'.',''));

        $cad7 .= '|'.$rowtd['d_cantidad'].'|'.$claveun.'|'.$rowtd['d_unidad'].'|'.$descripcion.'|'.number_format($rowtd['d_costo'],6,'.','').'|'.number_format($importefac,6,'.','');

        if($amortizacion == "1"){
          $amortizacion = "0";
          $Concepto->setAttribute('Descuento', number_format($montoamortizacion,2,'.',''));
          $cad7 .= '|'.number_format($montoamortizacion,2,'.','');
        }elseif($rowtd['d_descuento'] > 0){
          $Concepto->setAttribute('Descuento', number_format($rowtd['d_descuento'],2,'.',''));
          $cad7 .= '|'.number_format($rowtd['d_descuento'],2,'.','');
        }

        $sqlfi = 'SELECT * FROM facturasd_imp WHERE fi_factura = "'.$factura.'" 
                  AND fi_id = "'.$rowtd['d_id'].'" AND fi_tr = "T" AND fi_base > 0
                  ORDER BY fi_impuesto';
        $resultfi = setq($sqlfi) or die($sqlfi);
        $resultfin = setq($sqlfi) or die($sqlfi);
        $numimp = $resultfin->num_rows;

        if($numimp > 0){
          $impcon = $xml->createElement('cfdi:Impuestos');
          $trascon = $xml->createElement('cfdi:Traslados');
          $Concepto->setAttribute('ObjetoImp', "02");
          $cad7 .= '|02';
        } else{
          $Concepto->setAttribute('ObjetoImp', "01");
          $cad7 .= '|01';
        }

        $Concepto = $Conceptos->appendChild($Concepto);

        $numdim = 0;
        while($rowfi = $resultfi->fetch_array()){
          $numdim++;
          if($rowfi['fi_importe'] == 0) $rowfi['fi_tasac'] = 0;

          //if($rowfi['fi_impuesto'] == "002" && $rowfi['fi_tr'] == "T" && $rowfi['fi_factura'] == "62175") $rowfi['fi_importe']-=0.01;
          $totalimpuestostras+=number_format($rowfi['fi_importe'],6,'.','');
          $trascond = $xml->createElement('cfdi:Traslado');
          $trascond->setAttribute('Base', number_format($rowfi['fi_base'],2,'.',''));
          $trascond->setAttribute('Impuesto', $rowfi['fi_impuesto']);
          $trascond->setAttribute('TipoFactor', $rowfi['fi_tipof']);
          $trascond->setAttribute('TasaOCuota', number_format($rowfi['fi_tasac'],6,'.',''));
          $trascond->setAttribute('Importe', number_format($rowfi['fi_importe'],6,'.',''));
          $trascond = $trascon->appendChild($trascond);
          $cad7 .= '|'.number_format($rowfi['fi_base'],2,'.','').'|'.$rowfi['fi_impuesto'].'|'.$rowfi['fi_tipof'].'|'.number_format($rowfi['fi_tasac'],6,'.','').'|'.number_format($rowfi['fi_importe'],6,'.','');
        }

        if($numdim > 0)
          $trascon = $impcon->appendChild($trascon);

        if(busca($factura,'facturasd_imp','fi_tr = "R" AND fi_id = "'.$rowtd['d_id'].'" AND fi_factura','COUNT(*)')> 0){
          $retcon = $xml->createElement('cfdi:Retenciones');
          $sqlfi = 'SELECT * FROM facturasd_imp WHERE fi_factura = "'.$factura.'" 
                    AND fi_id = "'.$rowtd['d_id'].'" AND fi_tr = "R"
                    ORDER BY fi_impuesto';
          $resultfi = setq($sqlfi) or die($sqlfi);
          while($rowfi = $resultfi->fetch_array()){
          	$totalimpuestosret+=number_format($rowfi['fi_importe'],6,'.','');

            $trascond = $xml->createElement('cfdi:Retencion');
            $trascond->setAttribute('Base', number_format($rowfi['fi_base'],2,'.',''));
            $trascond->setAttribute('Impuesto', $rowfi['fi_impuesto']);
            $trascond->setAttribute('TipoFactor', $rowfi['fi_tipof']);
            $trascond->setAttribute('TasaOCuota', number_format($rowfi['fi_tasac'],6,'.',''));
            $trascond->setAttribute('Importe', number_format($rowfi['fi_importe'],6,'.',''));
            $trascond = $retcon->appendChild($trascond);
            $cad7 .= '|'.number_format($rowfi['fi_base'],2,'.','').'|'.$rowfi['fi_impuesto'].'|'.$rowfi['fi_tipof'].'|'.number_format($rowfi['fi_tasac'],6,'.','').'|'.number_format($rowfi['fi_importe'],6,'.','');
          }

          $retcon = $impcon->appendChild($retcon);
        }

        if($numimp > 0)
          $impcon = $Concepto->appendChild($impcon);
      }
      /*FIN ELEMENTO CONCEPTOS*/

      $total+=$totalimpuestostras;
      $total-=$totalimpuestosret;

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
        $cad7 .= '|'.number_format($totalimpuestosret,2,'.','');

        $sqlr = 'SELECT SUM(fi_importe) importe,fi_impuesto impuesto FROM facturasd_imp
                  WHERE fi_factura = "'.$factura.'" AND fi_tr = "R"
                  GROUP BY fi_impuesto';
        $resultr = setq($sqlr) or die($sqlr);
        while($rowr = $resultr->fetch_array()){
          $Retenciond = $xml->createElement('cfdi:Retencion');
          $Retenciond->setAttribute('Impuesto', $rowr['impuesto']);
          $Retenciond->setAttribute('Importe', number_format($rowr['importe'],2,'.',''));
          $Retenciond = $Retenciones->appendChild($Retenciond);

          $cad7 .= '|'.$rowr['impuesto'].'|'.number_format($rowr['importe'],2,'.','');
        }
      }
      /**************Traslados************************/

      if($totalimpuestostras > 0){
        $Tralados = $xml->createElement('cfdi:Traslados');
        $Tralados = $impuestos->appendChild($Tralados);

        $sqlt = 'SELECT SUM(fi_importe) importe,SUM(fi_base) sumabase,fi_impuesto impuesto,fi_tipof tipof,fi_tasac tasac
                  FROM facturasd_imp WHERE fi_factura = "'.$factura.'" AND fi_tr = "T"
                  GROUP BY fi_impuesto,fi_tipof,fi_tasac';
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
          $cad7 .= '|'.$rowtr['sumabase'].'|'.$rowtr['impuesto'].'|'.$rowtr['tipof'].'|'.number_format($rowtr['tasac'],6,'.','').'|'.number_format($rowtr['importe'],2,'.','');
        }
      }

      $cad7 .= '|'.number_format($totalimpuestostras,2,'.','');

      if($cpnum > 0){
        $Complemento = $xml->createElement('cfdi:Complemento');
        $Complemento = $root->appendChild($Complemento);

        //manejo de complementos extra
        if($cpnum){
          include_once('facturas.php');
          $facts = new ModelFacturas();
          $facts->resultcp($factura);

          $Cartapt = $xml->createElement('cartaporte20:CartaPorte');
          //$Cartapt->setAttribute('xmlns:cartaporte20', 'http://www.sat.gob.mx/CartaPorte20');
          $Cartapt->setAttribute('Version', $facts->Version);
          $Cartapt->setAttribute('TranspInternac', $facts->TranspInternac);
          $cad8 .= '|'.$facts->Version.'|'.$facts->TranspInternac;

          if($facts->TranspInternac != "No"){
            $Cartapt->setAttribute('EntradaSalidaMerc', $facts->EntradaSalidaMerc);
            $Cartapt->setAttribute('ViaEntradaSalida', $facts->ViaEntradaSalida);
            $cad8.='|'.$facts->EntradaSalidaMerc.'|'.$facts->ViaEntradaSalida;
          }

          $cad8 .= '|'.$facts->TotalDistRec;
          $Cartapt->setAttribute('TotalDistRec', $facts->TotalDistRec);
          $Cartapt = $Complemento->appendChild($Cartapt);

          $Cartaptu = $xml->createElement('cartaporte20:Ubicaciones');
          $Cartaptu = $Cartapt->appendChild($Cartaptu);

          while($rowu = $facts->resultubc->fetch_array()){
            $Cartaptub = $xml->createElement('cartaporte20:Ubicacion');
            $Cartaptub->setAttribute('TipoUbicacion', $rowu['cpu_TipoUbicacion']);
            $cad8 .= '|'.$rowu['cpu_TipoUbicacion'];

            if($rowu['cpu_IDUbicacion'] != 0) {
              $Cartaptub->setAttribute('IDUbicacion', $rowu['cpu_IDUbicacion']);
              $cad8 .= '|'.$rowu['cpu_IDUbicacion'];
            }

            $Cartaptub->setAttribute('RFCRemitenteDestinatario', $rowu['cpu_RFCRemitenteDestinatario']);
            $cad8 .= '|'.$rowu['cpu_RFCRemitenteDestinatario'];

            if(!empty($rowu['cpu_NombreRFC'])) {
              $Cartaptub->setAttribute('NombreRemitenteDestinatario', clearvmayus($rowu['cpu_NombreRFC']));
              $cad8 .= '|'.clearvmayus($rowu['cpu_NombreRFC']);
            }
            if(!empty($rowu['cpu_NumRegIdTrib'])) {
              $Cartaptub->setAttribute('NumRegIdTrib', $rowu['cpu_NumRegIdTrib']);
              $cad8 .= '|'.$rowu['cpu_NumRegIdTrib'];
            }
            if(!empty($rowu['cpu_ResidenciaFiscal'])) {
              $Cartaptub->setAttribute('ResidenciaFiscal', $rowu['cpu_ResidenciaFiscal']);
              $cad8 .= '|'.$rowu['cpu_ResidenciaFiscal'];
            }

            $Cartaptub->setAttribute('FechaHoraSalidaLlegada', str_replace(" ","T",$rowu['cpu_FechaHoraSalidaLlegada']));
            $cad8 .= '|'.str_replace(" ","T",$rowu['cpu_FechaHoraSalidaLlegada']);
            if($rowu['cpu_DistanciaRecorrida'] > 0) {
              $Cartaptub->setAttribute('DistanciaRecorrida', $rowu['cpu_DistanciaRecorrida']);
              $cad8 .= '|'.$rowu['cpu_DistanciaRecorrida'];
            }
            $Cartaptub = $Cartaptu->appendChild($Cartaptub);

            if(!empty($rowu['cpu_CodigoPostal'])){
              $Cartaptubdom = $xml->createElement('cartaporte20:Domicilio');
              if(!empty($rowu['cpu_Calle'])) {
                $Cartaptubdom->setAttribute('Calle', clearvmayus($rowu['cpu_Calle']));
                $cad8 .= '|'.clearvmayus($rowu['cpu_Calle']);
              }
              if(!empty($rowu['cpu_NumeroExterior'])) {
                $Cartaptubdom->setAttribute('NumeroExterior', $rowu['cpu_NumeroExterior']);
                $cad8 .= '|'.$rowu['cpu_NumeroExterior'];
              }
              if(!empty($rowu['cpu_NumeroInterior'])) {
                $Cartaptubdom->setAttribute('NumeroInterior', $rowu['cpu_NumeroInterior']);
                $cad8 .= '|'.$rowu['cpu_NumeroInterior'];
              }
              if(!empty($rowu['cpu_Colonia'])) {
                $Cartaptubdom->setAttribute('Colonia', $rowu['cpu_Colonia']);
                $cad8 .= '|'.$rowu['cpu_Colonia'];
              }
              if(!empty($rowu['cpu_Localidad'])) {
                $Cartaptubdom->setAttribute('Localidad', $rowu['cpu_Localidad']);
                $cad8 .= '|'.$rowu['cpu_Localidad'];
              }
              if(!empty($rowu['cpu_Referencia'])) {
                $Cartaptubdom->setAttribute('Referencia', $rowu['cpu_Referencia']);
                $cad8 .= '|'.$rowu['cpu_Referencia'];
              }
              if(!empty($rowu['cpu_Municipio'])) {
                $Cartaptubdom->setAttribute('Municipio', $rowu['cpu_Municipio']);
                $cad8 .= '|'.$rowu['cpu_Municipio'];
              }
              if(!empty($rowu['cpu_Estado'])) {
                $Cartaptubdom->setAttribute('Estado', $rowu['cpu_Estado']);
                $cad8 .= '|'.$rowu['cpu_Estado'];
              }
              if(!empty($rowu['cpu_Pais'])) {
                $Cartaptubdom->setAttribute('Pais', $rowu['cpu_Pais']);
                $cad8 .= '|'.$rowu['cpu_Pais'];
              }
              if(!empty($rowu['cpu_CodigoPostal'])) {
                $Cartaptubdom->setAttribute('CodigoPostal', $rowu['cpu_CodigoPostal']);
                $cad8 .= '|'.$rowu['cpu_CodigoPostal'];
              }
              $Cartaptubdom = $Cartaptub->appendChild($Cartaptubdom);
            }
          }

          $Cartaptm = $xml->createElement('cartaporte20:Mercancias');
          $Cartaptm->setAttribute('PesoBrutoTotal', $facts->PesoBrutoTotal);
          $Cartaptm->setAttribute('UnidadPeso', $facts->UnidadPeso);
          $cad8 .= '|'.$facts->PesoBrutoTotal.'|'.$facts->UnidadPeso;
          if($facts->PesoNetoTotal != 0) {
            $Cartaptm->setAttribute('PesoNetoTotal ', $facts->PesoNetoTotal );
            $cad8 .= '|'.$facts->PesoNetoTotal;
          }
          $Cartaptm->setAttribute('NumTotalMercancias', $facts->NumTotalMercancias);
          $cad8 .= '|'.$facts->NumTotalMercancias;
          if($facts->CargoPorTasacion != 0) {
            $Cartaptm->setAttribute('CargoPorTasacion', $facts->CargoPorTasacion);
            $cad8 .= '|'.$facts->CargoPorTasacion;
          }
          $Cartaptm = $Cartapt->appendChild($Cartaptm);

          while($rowm = $facts->resultmer->fetch_array()){
            $Cartaptmd = $xml->createElement('cartaporte20:Mercancia');
            if($rowm['cpm_BienesTransp'] == "15101515" || $rowm['cpm_BienesTransp'] == "15101514" || $rowm['cpm_BienesTransp'] == "15101505") $rowm['cpm_BienesTransp'] = "15101800";

            $Cartaptmd->setAttribute('BienesTransp', $rowm['cpm_BienesTransp']);
            $Cartaptmd->setAttribute('Descripcion', $rowm['cpm_Descripcion']);
            $Cartaptmd->setAttribute('Cantidad', $rowm['cpm_Cantidad']);
            $Cartaptmd->setAttribute('ClaveUnidad', $rowm['cpm_ClaveUnidad']);
            $cad8 .= '|'.$rowm['cpm_BienesTransp'].'|'.$rowm['cpm_Descripcion'].'|'.$rowm['cpm_Cantidad'].'|'.$rowm['cpm_ClaveUnidad'];
            if(!empty($rowm['cpm_Dimensiones'])) {
              $Cartaptmd->setAttribute('Dimensiones', $rowm['cpm_Dimensiones']);
              $cad8 .= '|'.$rowm['cpm_Dimensiones'];
            }
            $Cartaptmd->setAttribute('PesoEnKg', $rowm['cpm_PesoEnKg']);
            $cad8 .= '|'.$rowm['cpm_PesoEnKg'];
            //if(!empty($rowm['cpm_ValorMercancia'])) $Cartaptmd->setAttribute('ValorMercancia', $rowm['cpm_ValorMercancia']);
            //if(!empty($rowm['cpm_Moneda'])) $Cartaptmd->setAttribute('Moneda', $rowm['cpm_Moneda']);
            if(!empty($rowm['cpm_FraccionArancelaria'])) {
              $Cartaptmd->setAttribute('FraccionArancelaria', $rowm['cpm_FraccionArancelaria']);
              $cad8 .= '|'.$rowm['cpm_FraccionArancelaria'];
            }
            if(!empty($rowm['cpm_UUIDComercioExt'])) {
              $Cartaptmd->setAttribute('UUIDComercioExt', $rowm['cpm_UUIDComercioExt']);
              $cad8 .= '|'.$rowm['cpm_UUIDComercioExt'];
            }

            $Cartaptmd = $Cartaptm->appendChild($Cartaptmd);
          }
          $Cartaptaut = $xml->createElement('cartaporte20:Autotransporte');
          $Cartaptaut->setAttribute('PermSCT', $facts->tipopermiso);
          $Cartaptaut->setAttribute('NumPermisoSCT', $facts->numpermiso);
          $cad8 .= '|'.$facts->tipopermiso.'|'.$facts->numpermiso;
          $Cartaptaut = $Cartaptm->appendChild($Cartaptaut);

          $Cartaptaid = $xml->createElement('cartaporte20:IdentificacionVehicular');
          $Cartaptaid->setAttribute('ConfigVehicular', $facts->configtransporte);
          $Cartaptaid->setAttribute('PlacaVM', $facts->PlacaVM);
          $Cartaptaid->setAttribute('AnioModeloVM', $facts->AnioModeloVM);
          $cad8 .= '|'.$facts->configtransporte.'|'.$facts->PlacaVM.'|'.$facts->AnioModeloVM;
          $Cartaptaid = $Cartaptaut->appendChild($Cartaptaid);

          $Cartaptaas = $xml->createElement('cartaporte20:Seguros');
          $Cartaptaas->setAttribute('AseguraRespCivil', clearvmayus($facts->AseguraRespCivil));
          $Cartaptaas->setAttribute('PolizaRespCivil', clearvmayus($facts->PolizaRespCivil));
          $cad8 .= '|'.clearvmayus($facts->AseguraRespCivil).'|'.clearvmayus($facts->PolizaRespCivil);
          $Cartaptaas = $Cartaptaut->appendChild($Cartaptaas);

          if(!empty($facts->TipoRem1)){
            $Cartapttr = $xml->createElement('cartaporte20:Remolques');
            $Cartapttr = $Cartaptaut->appendChild($Cartapttr);

            $Cartapttm = $xml->createElement('cartaporte20:Remolque');
            $Cartapttm->setAttribute('SubTipoRem', clearvmayus($facts->TipoRem1));
            $Cartapttm->setAttribute('Placa', clearvmayus($facts->PlacaRem1));
            $cad8 .= '|'.clearvmayus($facts->TipoRem1).'|'.clearvmayus($facts->PlacaRem1);
            $Cartapttm = $Cartapttr->appendChild($Cartapttm);

            if(!empty($facts->TipoRem2)){
              $Cartapttm = $xml->createElement('cartaporte20:Remolque');
              $Cartapttm->setAttribute('SubTipoRem', clearvmayus($facts->TipoRem2));
              $Cartapttm->setAttribute('Placa', clearvmayus($facts->PlacaRem2));
              $cad8 .= '|'.clearvmayus($facts->TipoRem2).'|'.clearvmayus($facts->PlacaRem2);
              $Cartapttm = $Cartapttr->appendChild($Cartapttm);
            }
          }

          $Cartaptft = $xml->createElement('cartaporte20:FiguraTransporte');
          $Cartaptft = $Cartapt->appendChild($Cartaptft);

          $Cartaptftt = $xml->createElement('cartaporte20:TiposFigura');
          $Cartaptftt->setAttribute('TipoFigura', $facts->figuratransporte);
          $Cartaptftt->setAttribute('RFCFigura', $facts->RFCFigura);
          $Cartaptftt->setAttribute('NumLicencia', $facts->NumLicencia);
          $Cartaptftt->setAttribute('NombreFigura', clearvmayus($facts->NombreFigura));
          $cad8 .= '|'.$facts->figuratransporte.'|'.$facts->RFCFigura.'|'.$facts->NumLicencia.'|'.clearvmayus($facts->NombreFigura).'||';
          $Cartaptftt = $Cartaptft->appendChild($Cartaptftt);
        }
      }//Fin de la comprobacion de complementos
      else{
        $cad8 .= '||';
      }

			$cad2 .= '|'.number_format($subtotal,2,'.','');

      if($descuento > 0) $cad2 .= '|'.$descuento;

      $cad2 .= '|'.$moneda.'|'.$valorm.'|'.number_format($total,2,'.','').'|'.$ingoegr.'|'.$exportacion.'|'.$metodopago.'|'.$lugarexpedicion;

      $cadenaorig = $cad0.$cad1.$cad2.$cad3.$cad4.$cad5.$cad6.$cad7.$cad8;

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
        }else{
          $cadenaoriginal.=$cadenaorig[$i];
          $espacio = 0;
        }
      }

      //Finalmente, guardarlo en un directorio:
      if (!is_dir('cfd')) {
        @mkdir('cfd', 0777);
      }
      if (!is_dir('cfd/' . $empf.'/xml')) {
        @mkdir('cfd/' . $empf.'/xml', 0777);
      }

      $handle=fopen('cfd/' . $empf . '/xml/x.txt','w');  fwrite($handle, $cadenaoriginal); fclose($handle);
      /******************************************************/
      $file='cfd/' . $empf . '/' . busca($empf,'empresas','e_id','e_rfc') . '.key.pem';      // Ruta al archivo
      $cadena_original = $cadenaoriginal;

      $pkeyid = openssl_get_privatekey(file_get_contents($file));
      openssl_sign($cadena_original, $crypttext, $pkeyid, OPENSSL_ALGO_SHA256);
      openssl_free_key($pkeyid);
      $sello = base64_encode($crypttext);             // lo codifica en formato base64
      /******************************************************/

      $root->setAttribute('Sello', $sello);
      $root->setAttribute('FormaPago', $formapago);
      $root->setAttribute('NoCertificado', $model->cfdcertificado);
      $root->setAttribute('Certificado', $certificadoss);

      if($condiciones) $root->setAttribute('CondicionesDePago', $condiciones);

      $root->setAttribute('SubTotal', number_format($subtotal,2,'.',''));

      if($descuento > 0) $root->setAttribute('Descuento', $descuento);

      $root->setAttribute('Moneda', $moneda);
      $root->setAttribute('TipoCambio', $valorm);
      $root->setAttribute('Total',  number_format($total,2,'.',''));
      $root->setAttribute('TipoDeComprobante', $ingoegr);
      $root->setAttribute('Exportacion', $exportacion);
      $root->setAttribute('MetodoPago', $metodopago);
      $root->setAttribute('LugarExpedicion', $lugarexpedicion);

      $xml->formatOutput = true;

      $strings_xml = $xml->saveXML();

      if($ticket == "128872"){
        $xml->formatOutput = true;
      }

      $xml->save('cfd/' . $empf . '/xml/'.$folio . '.xml');
      /*
        if($ticket == "126985")
          die("Error en Generar XML intentar de nuevo: ".'cfd/' . $empresa . '/xml/'.$folio . '.xml');
      */
      // die("Error en Generar XML 40 intentar de nuevo: ".'cfd/' . $empf . '/xml/'.$folio . '.xml');
      return(true);
		}
	}