<?php

ini_set('display_errors',1);

session_start();

require_once '../lib/excel/Classes/PHPExcel.php';

include ('../funciones.php');

 $estiloTituloReporte = array(

    'font' => array(

        'name'      => 'KALINGA',

        'bold'      => false,

        'italic'    => false,

        'strike'    => false,

        'size' =>11,

        'color'     => array(

            'rgb' => '000000'

        )

    ),

    'fill' => array(

      'type'  => PHPExcel_Style_Fill::FILL_SOLID,

      'color' => array(

            'argb' => '858BFF')

  ),

    'borders' => array(

        'allborders' => array(

            'style' => PHPExcel_Style_Border::BORDER_THIN

        )

    ),

    'alignment' => array(

        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

        'rotation' => 0,

        'wrap' => TRUE

    )

);



$estilodep = array(

  'font' => array(

      'name'      => 'KALINGA',

      'bold'      => false,

      'italic'    => false,

      'strike'    => false,

      'size' =>11,

      'color'     => array(

          'rgb' => '000000'

      )

  ),

  'fill' => array(

    'type'  => PHPExcel_Style_Fill::FILL_SOLID,

    'color' => array(

          'argb' => '1FFF00')

),

  'borders' => array(

      'allborders' => array(

          'style' => PHPExcel_Style_Border::BORDER_THIN

      )

  ),

  'alignment' => array(

      'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

      'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

      'rotation' => 0,

      'wrap' => TRUE

  )

);



$estiloret = array(

  'font' => array(

      'name'      => 'KALINGA',

      'bold'      => false,

      'italic'    => false,

      'strike'    => false,

      'size' =>11,

      'color'     => array(

          'rgb' => '000000'

      )

  ),

  'fill' => array(

    'type'  => PHPExcel_Style_Fill::FILL_SOLID,

    'color' => array(

          'argb' => 'FF8F00')

),

  'borders' => array(

      'allborders' => array(

          'style' => PHPExcel_Style_Border::BORDER_THIN

      )

  ),

  'alignment' => array(

      'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

      'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

      'rotation' => 0,

      'wrap' => TRUE

  )

);



$estilotot = array(

  'font' => array(

      'name'      => 'KALINGA',

      'bold'      => false,

      'italic'    => false,

      'strike'    => false,

      'size' =>11,

      'color'     => array(

          'rgb' => '000000'

      )

  ),

  'fill' => array(

    'type'  => PHPExcel_Style_Fill::FILL_SOLID,

    'color' => array(

          'argb' => 'B8B8B8')

),

  'borders' => array(

      'allborders' => array(

          'style' => PHPExcel_Style_Border::BORDER_THIN

      )

  ),

  'alignment' => array(

      'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

      'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

      'rotation' => 0,

      'wrap' => TRUE

  )

);



$estiloX = array(

    'font' => array(

        'name'      => 'KALINGA',

        'bold'      => false,

        'italic'    => false,

        'strike'    => false,

        'size' =>10,

        'color'     => array(

            'rgb' => '000000'

        )

    ),

    'fill' => array(

      'type'  => PHPExcel_Style_Fill::FILL_SOLID,

      'color' => array(

            'argb' => 'F70B0B')

  ),

    'borders' => array(

        'allborders' => array(

            'style' => PHPExcel_Style_Border::BORDER_THIN

        )

    ),

    'alignment' => array(

        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

        'rotation' => 0,

        'wrap' => TRUE

    )

);

$Cnaranja = array(

    'font' => array(

        'name'      => 'KALINGA',

        'bold'      => false,

        'italic'    => false,

        'strike'    => false,

        'size' =>10,

        'color'     => array(

            'rgb' => '000000'

        )

    ),

    'fill' => array(

      'type'  => PHPExcel_Style_Fill::FILL_SOLID,

      'color' => array(

            'argb' => 'F7AC0B')

  ),

    'borders' => array(

        'allborders' => array(

            'style' => PHPExcel_Style_Border::BORDER_THIN

        )

    ),

    'alignment' => array(

        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

        'rotation' => 0,

        'wrap' => TRUE

    )

);



$Cgris = array(

    'font' => array(

        'name'      => 'KALINGA',

        'bold'      => false,

        'italic'    => false,

        'strike'    => false,

        'size' =>10,

        'color'     => array(

            'rgb' => '000000'

        )

    ),

    'fill' => array(

      'type'  => PHPExcel_Style_Fill::FILL_SOLID,

      'color' => array(

            'argb' => 'CDCDCD')

  ),

    'borders' => array(

        'allborders' => array(

            'style' => PHPExcel_Style_Border::BORDER_THIN

        )

    ),

    'alignment' => array(

        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

        'rotation' => 0,

        'wrap' => TRUE

    )

);



$Cverde = array(

    'font' => array(

        'name'      => 'KALINGA',

        'bold'      => false,

        'italic'    => false,

        'strike'    => false,

        'size' =>10,

        'color'     => array(

            'rgb' => '000000'

        )

    ),

    'fill' => array(

      'type'  => PHPExcel_Style_Fill::FILL_SOLID,

      'color' => array(

            'argb' => '24F70B')

  ),

    'borders' => array(

        'allborders' => array(

            'style' => PHPExcel_Style_Border::BORDER_THIN

        )

    ),

    'alignment' => array(

        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

        'rotation' => 0,

        'wrap' => TRUE

    )

);

$Ccafe = array(

    'font' => array(

        'name'      => 'KALINGA',

        'bold'      => false,

        'italic'    => false,

        'strike'    => false,

        'size' =>10,

        'color'     => array(

            'rgb' => '000000'

        )

    ),

    'fill' => array(

      'type'  => PHPExcel_Style_Fill::FILL_SOLID,

      'color' => array(

            'argb' => '663300')

  ),

    'borders' => array(

        'allborders' => array(

            'style' => PHPExcel_Style_Border::BORDER_THIN

        )

    ),

    'alignment' => array(

        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

        'rotation' => 0,

        'wrap' => TRUE

    )

);



$estiloTituloColumnas = array(

    'font' => array(

        'name'      => 'KALINGA',

        'bold'      => false,

        'italic'    => false,

        'strike'    => false,

        'size' =>10,

        'color'     => array(

            'rgb' => '000000'

        )

    ),

    'fill' => array(

      'type'  => PHPExcel_Style_Fill::FILL_SOLID,

      'color' => array(

            'argb' => '858BFF')

  ),

    'borders' => array(

        'allborders' => array(

            'style' => PHPExcel_Style_Border::BORDER_THIN

        )

    ),

    'alignment' => array(

        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

        'rotation' => 0,

        'wrap' => TRUE

    )

);

$estiloInformacion = array(

    'font' => array(

        'name'  => 'KALINGA',

        'color' => array(

            'rgb' => '000000'

        )

    ),

    'fill' => array(

  'type'  => PHPExcel_Style_Fill::FILL_SOLID,

  'color' => array(

            'argb' => 'FFFFFF')

  ),

    'borders' => array(

    'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),

      'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),

      'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),

      'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)

    )

);

$estiloInformacion1 = array(

    'font' => array(

        'name'  => 'KALINGA',

        'color' => array(

            'rgb' => '000000'

        )

    ),

    'fill' => array(

  'type'  => PHPExcel_Style_Fill::FILL_SOLID,

  'color' => array(

            'argb' => 'FFFFFF')

  ),

    'borders' => array(

    'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),

      'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),

      'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),

      'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)

    ),

    'alignment' => array(

        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,

        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

        'rotation' => 0,

        'wrap' => TRUE

    )

);



if(empty($_GET['fini'])) $_GET['fini'] = date('Y-m-d',strtotime('first day of this month'));

if(empty($_GET['ffin'])) $_GET['ffin'] = date('Y-m-d',strtotime('last day of this month'));

if(empty($_GET['cuenta'])) $_GET['cuenta'] = NULL;



/*if(empty($_GET['almacen'])) $_GET['almacen'] = NULL;

if(empty($_GET['estatus'])) $_GET['estatus'] = NULL; */





if (PHP_SAPI == 'cli')

    die('Este archivo solo se puede ver desde un navegador web');



$objPHPExcel = new PHPExcel();



$objPHPExcel->getProperties()->setCreator("JD CEO") // Nombre del autor

    ->setLastModifiedBy("JD CEO") //Ultimo usuario que lo modific?

    ->setTitle("REPORTE DE CUENTA") // Titulo

    ->setSubject("REPORTE DE CUENTA") //Asunto

    ->setDescription("REPORTE DE CUENTA") //Descripci?n

    ->setKeywords("REPORTE DE CUENTA") //Etiquetas

    ->setCategory("REPORTE DE CUENTA"); //Categorias



$tituloReporte = "REPORTE DE CUENTA DESDE: ".fecha_formato($_GET['fini'],false,true).' '."HASTA: ".fecha_formato($_GET['ffin'],false,true);

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);

// Se agregan los titulos del reporte

$objPHPExcel->setActiveSheetIndex(0)

    ->mergeCells('A1:F1');



$titulosColumnas = array('Fecha','Generó','Descripción','Depositos','Retiros','Saldo');

$letra = "A";

foreach($titulosColumnas as $titc){

  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letra.'2',$titc);

  $letra++;

}



  $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($estiloTituloReporte);

  $objPHPExcel->getActiveSheet()->getStyle('A1:F2')->applyFromArray($estiloTituloReporte);



  $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:F1');



  //$a=0;

  $i = 2; //Numero de fila donde se va a comenzar a rellenar

  $col = "A";

  /*$sql = 'SELECT cd_tipo,cd_monto,cd_idref,cd_fecha fechagen,cd_gen,cd_tipom,cd_cuenta, cd_reverso

  FROM cortes INNER JOIN cortesd ON c_id=cd_corte WHERE c_cuenta="'.$_GET['cuenta'].'" AND cd_condonar="0" AND DATE(cd_fecha) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" 

  UNION SELECT "P",t_importe,t_id,t_fgen,t_ugen,"TR",t_destino FROM traspasos WHERE t_origen="'.$_GET['cuenta'].'" AND DATE(t_fgen) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" 

  UNION SELECT "C",t_importe,t_id,t_fgen,t_ugen,"TR",t_origen FROM traspasos WHERE t_destino="'.$_GET['cuenta'].'" AND DATE(t_fgen) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" ORDER BY DATE(fechagen) ASC, cd_idref ASC'; */

  $sql= 'SELECT cd_tipo,cd_monto,cd_idref,cd_fecha fechagen,cd_gen,cd_tipom,cd_cuenta,cd_reverso,cd_corte
  FROM cortes INNER JOIN cortesd ON c_id=cd_corte
  WHERE c_cuenta="'.$_GET['cuenta'].'" AND cd_condonar="0" AND DATE(cd_fecha) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" 
  UNION
  SELECT "P",t_importe,t_id,t_fgen,t_ugen,"TR",t_destino,NULL,NULL FROM traspasos
  WHERE 
  t_origen="'.$_GET['cuenta'].'" AND DATE(t_fgen) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" 
   UNION
  SELECT "C",t_importe,t_id,t_fgen,t_ugen,"TR",t_origen,NULL,NULL FROM traspasos
  WHERE t_destino="'.$_GET['cuenta'].'" AND DATE(t_fgen) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" 
  ORDER BY fechagen ASC';

  $resultrem = setq($sql);



  $totalsubtotal = 0;

  $totaldescuento = 0;

  $totalfinal = 0;

  $col = "A";

  $numrems = 0;

  //$corte = busca($_GET['cuenta'],'cortes','c_estatus = "A" AND c_cuenta','c_saldo');
  $corte = busca($_GET['cuenta'],'cortes','c_id = "'.$_GET['corte'].'" AND c_cuenta','c_saldo');
  //$corte = $_GET['corte'];
  //$fechacorte = busca($_GET['cuenta'],'cortes','c_estatus = "A" AND c_cuenta','c_fini');
  $fechacorte = busca($_GET['cuenta'],'cortes','c_id = "'.$_GET['corte'].'" AND c_cuenta','c_fini');
  $saldot = $saldot+$corte;

  $i++;

  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, fecha_formato($fechacorte,false,false));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, "");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, "SALDO INICIAL");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $corte);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $retiros);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, number_format($saldot,2));
  $objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.$i.'')->applyFromArray($estilodep);
  $totala += $corte;


  while($row = $resultrem->fetch_array()){

    $numrems++;

    $i++;

    if($row['cd_tipo']=="P"){
      $totalr=$totalr+$row['cd_monto'];
      $depositos='-';   $color1='';
      $retiros=''.number_format($row['cd_monto'],2); $color2='style="background-color:#E67D31"';
      $saldot=$saldot-$row['cd_monto'];
      $objPHPExcel->setActiveSheetIndex(0)->getStyle('E'.$i.'')->applyFromArray($estiloret); 

      if($row['cd_tipom'] == "TR") $desc = 'TRASPASO ENVIADO A '.busca($row['cd_cuenta'],'cuentas','cu_id','cu_nmb');
      elseif ($row['cd_tipom'] == "X"){
        $idr = busca($row['cd_reverso'],'cortesd','cd_corte="'.$row['cd_corte'].'" AND cd_id','cd_idref');
        $sqlcx = 'SELECT ic_cxcobrar,ic_ingreso FROM ingreso_cxcobrar WHERE ic_ingreso = "'.$idr.'" ';
        $resultcx = setq($sqlcx) or die($sqlcx);
        $desc = "REVERSO DEL MOVIMIENTO ";
        if($resultcx->num_rows == 0) $desc .= busca($idr,'ingresos','i_id','i_observaciones');
        else{
            while($rowcx = $resultcx->fetch_array()){
                $sqlxp = 'SELECT * FROM cxcobrar WHERE cx_id = "'.$rowcx['ic_cxcobrar'].'" ';
                $resultxp = setq($sqlxp) or die($sqlxp);
                $ix = 0;
                while($rowxp = $resultxp->fetch_array()){
                    if($rowxp['cx_tipo'] == "R") $desc .= busca($rowxp['cx_referencia'],'remisiones','r_id','r_nmb');
                    elseif($rowxp['cx_tipo'] == "S") $desc .= "SOPORTE ".$rowxp['cx_referencia'].' de '.busca($rowxp['cx_referencia'],'proyectos','p_id','p_nmb');
                    //elseif($rowxp['cx_tipo'] == "P") $desc .= busca($rowxp['cx_referencia'],'prestamos','pr_id','pr_descripcion');
                    elseif($rowxp['cx_tipo'] == "P") $desc = busca($rowxp['cx_referencia'],'ordenesp','op_id','op_nmb');
                    elseif($rowxp['cx_tipo'] == "I") $desc .= busca($rowxp['cx_referencia'],'comisiones','c_id','c_descripcion');
                    elseif($rowxp['cx_tipo'] == "V") $desc .= busca(busca($rowxp['cx_referencia'],'valesp','vp_id','vp_vale'),'vales','v_id','v_nmb').' - '.busca($rowxp['cx_idref'],'valesp','vp_id','vp_descripcion');
                    elseif($rowxp['cx_tipo'] == "T") $desc .= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cx_fini']));
                    elseif($rowxp['cx_tipo'] == "A") $desc .= "COTIZACION ".busca($rowxp['cx_referencia'],'cotizaciones','c_id','c_cotizacion');
                    elseif($rowxp['cx_tipo'] == "Z") $desc .= "POLIZA ".busca($rowxp['cx_referencia'],'remisiones','r_id','r_nmb');
                    if(!$desc) $desc .= busca($rowcx['ic_ingreso'],'ingresos','i_id','i_observaciones');
                }
            }
          }

      }
      else{
        $sqlcx = 'SELECT ec_cxpagar FROM egreso_cxpagar WHERE ec_egreso = "'.$row['cd_idref'].'"';
        $resultcx = setq($sqlcx) or die($sqlcx);
        $desc = "";
        while($rowcx = $resultcx->fetch_array()){
          $sqlxp = 'SELECT * FROM cxpagar WHERE cp_id = "'.$rowcx['ec_cxpagar'].'" ';
          $resultxp = setq($sqlxp) or die($sqlxp);
          $ix = 0;
          while($rowxp = $resultxp->fetch_array()){
            if($rowxp['cp_tipo'] == "O") $desc.= "ORDEN PAGO ".busca($rowxp['cp_idref'],'ordenesp','op_id','op_nmb');
            elseif($rowxp['cp_tipo'] == "C") $desc.= "COMPRAS ".busca($rowxp['cp_idref'],'ordenesc','o_id','o_folio');
            elseif($rowxp['cp_tipo'] == "F") $desc.= busca($rowxp['cp_idref'],'gastosf','gf_id','gf_nmb').' - '.date('d-m-Y',strtotime($rowxp['cp_fini']));
            //elseif($rowxp['cp_tipo'] == "R") $desc.= busca($rowxp['cp_idref'],'prestamos','pr_id','pr_descripcion');
            elseif($rowxp['cp_tipo'] == "G" || $rowxp['cp_tipo'] == "V") $desc.= busca($rowxp['cp_idref'],'gastos','g_id','g_descripcion');
            elseif($rowxp['cp_tipo'] == "T") $desc.= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cp_fini']));
            elseif($rowxp['cp_tipo'] == "M") $desc.= "ORDEN DE COMPRA MENOR";
            elseif($rowxp['cp_tipo'] == "D") $desc .= $rowxp['cp_observaciones'];
            elseif($rowxp['cp_tipo'] == "E") $desc .= $rowxp['cp_observaciones'];
            else {
              if(!$row['cd_observaciones']) $desc = busca($rowcx['ec_egreso'],'egresos','e_id','e_observaciones');
              else $desc = $row['cd_observaciones'];
            }
            $ix++;
          }
        }
      }
    }
    else{
      $objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.$i.'')->applyFromArray($estilodep);
      $totala=$totala+$row['cd_monto'];
      $depositos= ''.number_format($row['cd_monto'],2);  $color1='style="background-color:#49E67D"';
      $retiros='-';  $color2='';
      $saldot=$saldot+$row['cd_monto'];
      if($row['cd_tipom'] == "TR") $desc = 'TRASPASO COBRADO DESDE '.busca($row['cd_cuenta'],'cuentas','cu_id','cu_nmb');
      elseif($row['cd_tipom'] == "X"){
        $idr = busca($row['cd_reverso'],'cortesd','cd_corte="'.$row['cd_corte'].'" AND cd_id','cd_idref');
        $sqlcx = 'SELECT ec_cxpagar,ec_egreso FROM egreso_cxpagar WHERE ec_egreso = "'.$idr.'"';
        $resultcx = setq($sqlcx) or die($sqlcx);
        $desc = "REVERSO DEL MOVIMIENTO ";
        $div = '';
        if($resultcx->num_rows == 0) $desc .= busca($idr,'egresos','e_id','e_observaciones');
        else{
            while($rowcx = $resultcx->fetch_array()){
                $sqlxp = 'SELECT * FROM cxpagar WHERE cp_id = "'.$rowcx['ec_cxpagar'].'" ';
                $resultxp = setq($sqlxp) or die($sqlxp);
                $ix = 0;
                while($rowxp = $resultxp->fetch_array()){
                if($rowxp['cp_tipo'] == "O") $desc.= "ORDEN PAGO ".busca($rowxp['cp_idref'],'ordenesp','op_id','op_nmb');
                elseif($rowxp['cp_tipo'] == "C") $desc.= "COMPRAS ".busca($rowxp['cp_idref'],'ordenesc','o_id','o_folio');
                elseif($rowxp['cp_tipo'] == "F") $desc.= busca($rowxp['cp_idref'],'gastosf','gf_id','gf_nmb').' - '.date('d-m-Y',strtotime($rowxp['cp_fini']));
                //elseif($rowxp['cp_tipo'] == "R") $desc.= busca($rowxp['cp_idref'],'prestamos','pr_id','pr_descripcion');
                elseif($rowxp['cp_tipo'] == "G" || $rowxp['cp_tipo'] == "V") $desc.= busca($rowxp['cp_idref'],'gastos','g_id','g_descripcion');
                elseif($rowxp['cp_tipo'] == "T") $desc.= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cp_fini']));
                elseif($rowxp['cp_tipo'] == "M") $desc.= "ORDEN DE COMPRA MENOR";
                elseif($rowxp['cp_tipo'] == "D") $desc .= $rowxp['cp_observaciones'];
                elseif($rowxp['cp_tipo'] == "E") $desc .= $rowxp['cp_observaciones'];
                else {
                    if(!$row['cd_observaciones']) $desc = busca($rowcx['ec_egreso'],'egresos','e_id','e_observaciones');
                    else $desc = $row['cd_observaciones'];
                }
                $ix++;
                }
            }
        }
      }
      else{
        $sqlcx = 'SELECT ic_cxcobrar FROM ingreso_cxcobrar WHERE ic_ingreso = "'.$row['cd_idref'].'" ';
        $resultcx = setq($sqlcx) or die($sqlcx);
        $desc = "";
        while($rowcx = $resultcx->fetch_array()){
          $sqlxp = 'SELECT * FROM cxcobrar WHERE cx_id = "'.$rowcx['ic_cxcobrar'].'" ';
          $resultxp = setq($sqlxp) or die($sqlxp);
          $ix = 0;
          while($rowxp = $resultxp->fetch_array()){
            if($rowxp['cx_tipo'] == "R") $desc .= busca($rowxp['cx_referencia'],'remisiones','r_id','r_nmb');
            elseif($rowxp['cx_tipo'] == "S") $desc .= "SOPORTE ".$rowxp['cx_referencia'].' de '.busca($rowxp['cx_referencia'],'proyectos','p_id','p_nmb');
            //elseif($rowxp['cx_tipo'] == "P") $desc .= busca($rowxp['cx_referencia'],'prestamos','pr_id','pr_descripcion');
            elseif($rowxp['cx_tipo'] == "P") $desc = busca($rowxp['cx_referencia'],'ordenesp','op_id','op_nmb');
            elseif($rowxp['cx_tipo'] == "I") $desc .= busca($rowxp['cx_referencia'],'comisiones','c_id','c_descripcion');
            elseif($rowxp['cx_tipo'] == "V") $desc .= busca(busca($rowxp['cx_referencia'],'valesp','vp_id','vp_vale'),'vales','v_id','v_nmb').' - '.busca($rowxp['cx_idref'],'valesp','vp_id','vp_descripcion');
            elseif($rowxp['cx_tipo'] == "T") $desc .= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cx_fini']));
            elseif($rowxp['cx_tipo'] == "A") $desc .= "COTIZACION ".busca($rowxp['cx_referencia'],'cotizaciones','c_id','c_cotizacion');
            elseif($rowxp['cx_tipo'] == "Z") $desc .= "POLIZA ".busca($rowxp['cx_referencia'],'remisiones','r_id','r_nmb');
            if(!$desc) $desc .= busca($rowcx['ic_ingreso'],'ingresos','i_id','i_observaciones');
          }
        }
      }
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, fecha_formato($row['fechagen'],true,false));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, $row['cd_gen']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $desc);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $depositos);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $retiros);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, number_format($saldot,2));

    //$objPHPExcel->getActiveSheet()->getStyle("A".$i.':'."F".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');


    //$totalsubtotal+=$subtotal;

    //$totaldescuento+=$rowal['r_descuento'];

    //$totalfinal+=$rowal['r_total'];

  }





  $i++;

  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i,"TOTALES");

  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i,number_format($totala,2));

  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i,number_format($totalr,2));

  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i,number_format($saldot,2));

  $objPHPExcel->setActiveSheetIndex(0)->getStyle('C'.$i.'')->applyFromArray($estiloX);

  $objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.$i.'')->applyFromArray($estilodep);

  $objPHPExcel->setActiveSheetIndex(0)->getStyle('E'.$i.'')->applyFromArray($estiloret);

  $objPHPExcel->setActiveSheetIndex(0)->getStyle('F'.$i.'')->applyFromArray($estilotot);

  /*
  $i++;

  $corte = busca($_GET['cuenta'],'cortes','c_estatus = "A" AND c_cuenta','c_saldo');
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i,"SALDO INICIAL");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i,number_format($corte,2));

  $i++;

  $corte = busca($_GET['cuenta'],'cortes','c_estatus = "A" AND c_cuenta','c_saldo');
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i,"SALDO FINAL");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i,number_format($corte+$saldot,2));
  */


  

  //$objPHPExcel->getActiveSheet()->getStyle("A".$i.':'."F".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');

  

// formato de moneda

//  $objPHPExcel->getActiveSheet()->getStyle('K'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



$objPHPExcel->getActiveSheet()->setTitle('CUENTAS');



// Se activa la hoja para que sea la que se muestre cuando el archivo se abre

$objPHPExcel->setActiveSheetIndex(0);



// Inmovilizar paneles

//$objPHPExcel->getActiveSheet(0)->freezePane('A4');

$objPHPExcel->getActiveSheet()

    ->getPageSetup()

    ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

$objPHPExcel->getActiveSheet()

    ->getPageSetup()

    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);



    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);

    //$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(40);

foreach(range('A','Z') as $columnID) { $objPHPExcel->getActiveSheet()->getColumnDimension($columnID) ->setAutoSize(true); }

$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,3);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

header('Content-Disposition: attachment;filename="REPORTE DE CUENTA.xlsx"');

header('Cache-Control: max-age=0');



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objWriter->save('php://output');

exit;

?>