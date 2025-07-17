<?php
    include_once('../funciones.php');
    session_start();
    //ini_set('display_errors', 1);

    $colores = array('primary', 'danger', 'warning', 'info', 'success');
    $meses = array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $dias = array('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado');
    $sql = 'SELECT * FROM crm_clientes LIMIT 0,50';
    $result = setq($sql);


    $usuario = $_SESSION['uid'];
    $metamensual = busca($usuario, 'usuarios_orden', 'uo_uid', 'uo_metamensual');
    $grupo = busca($usuario, 'usuarios', 'u_id', 'u_grupo');

    $result = setq($sql);
    $lunes = date('Y-m-d', strtotime("this week"));
    $fecha_i = date('Y-m-d', strtotime($lunes));
    $fecha_f = date('Y-m-d', strtotime($lunes.'+ 6 days'));
    $begin = new DateTime($fecha_i);
    $end = new DateTime($fecha_f);
    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($begin, $interval ,$end);
    foreach ($daterange as $date) {
      $dia =$date->format('Y-m-d');
      $sql = 'SELECT * FROM remisiones WHERE DATE(r_faplica) >= "'.date('Y-m-d', strtotime($dia)).'"
              AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime($dia)).'" AND r_estatus IN ("A", "F", "FE")';
      $result = setq($sql);
      //if($_SESSION['uid'] == "ADMIN") $result = setq($sql, true);
      while($row = $result -> fetch_array()){
        if(!$datos[$date->format('w')][$row['r_encargado']]) $datos[$date->format('w')][$row['r_encargado']] = 0;
        $datos[$date->format('w')][$row['r_encargado']] += ($row['r_total']-$row['r_precioenvio']);
      }
    }

    if($grupo == "ADMIN" || $grupo == "VENTAS" || $grupo == "GERENCIA"){
      $html = '
      <div class="col-12 col-sm-12 col-xs-12">
        <h2 class="alert alert-primary">Vencimiento de ventas</h2>
        <div class="row col-12">
          ';
            $menos5dias = date('Y-m-d', strtotime('+5 days'));
            $menos3dias = date('Y-m-d', strtotime('+3 days'));
            $sqlrem ='SELECT * FROM remisiones WHERE r_estatus NOT IN ("F", "FE", "C") AND DATE(r_fliquidacion) <= "'.$menos3dias.'" AND DATE(r_fliquidacion) >= "'.date('Y-m-d').'"'; //remisiones que caducan despues de 5 días
            if($grupo == "VENTAS") $sqlrem.=' AND r_encargado = "'.$_SESSION['uid'].'"';
            $resultrem1 = setq($sqlrem);

            $sqlrem ='SELECT * FROM remisiones WHERE r_estatus NOT IN ("F", "FE", "C") AND DATE(r_fliquidacion) <= "'.$menos5dias.'" AND DATE(r_fliquidacion) > "'.$menos3dias.'"'; //remisiones que caducan entre 3 y 5 días
            if($grupo == "VENTAS") $sqlrem.=' AND r_encargado = "'.$_SESSION['uid'].'"';
            $resultrem2 = setq($sqlrem);

            $sqlrem ='SELECT * FROM remisiones WHERE r_estatus NOT IN ("F", "FE", "C") AND DATE(r_fliquidacion) > "'.$menos5dias.'"'; //remisiones que caducan despues de 5 días
            if($grupo == "VENTAS") $sqlrem.=' AND r_encargado = "'.$_SESSION['uid'].'"';
            $resultrem3 = setq($sqlrem);


            if($resultrem1->num_rows > 0 || $resultrem2->num_rows > 0 || $resultrem3->num_rows > 0){
              while($rowrem = $resultrem1 -> fetch_array()){
              $nmb = busca($rowrem['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');
              $apellidos = busca($rowrem['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');
              $cliente = $nmb.' '.$apellidos;
              $fecha1 = new DateTime(date('Y-m-d'));
              $fecha2 = new DateTime($rowrem['r_fliquidacion']);
              $diferencia = $fecha1->diff($fecha2);
              if($diferencia->days == 0){
                $caduca = 'Vence HOY';
              }else if($diferencia->days == 1){
                $caduca = 'Vence MAÑANA';
              } else {
                $caduca = 'Vence en '.$diferencia->days.' días';
              } 
              $vendedor=busca($rowrem['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb)');
              $html .= '
              <div class="col-6 col-md-3">
                <div class="d-flex align-items-center alert-danger rounded p-5 mb-7">
                  <!--begin::Icon-->
                  <span class="svg-icon svg-icon-danger me-5">
                    <!--begin::Svg Icon | path: icons/duotune/abstract/abs027.svg-->
                    <span class="svg-icon svg-icon-1">
                      <svg style="fill:#FF0000" xmlns="http://www.w3.org/2000/svg" height="16" width="11" viewBox="0 0 352 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path d="M242.7 256l100.1-100.1c12.3-12.3 12.3-32.2 0-44.5l-22.2-22.2c-12.3-12.3-32.2-12.3-44.5 0L176 189.3 75.9 89.2c-12.3-12.3-32.2-12.3-44.5 0L9.2 111.5c-12.3 12.3-12.3 32.2 0 44.5L109.3 256 9.2 356.1c-12.3 12.3-12.3 32.2 0 44.5l22.2 22.2c12.3 12.3 32.2 12.3 44.5 0L176 322.7l100.1 100.1c12.3 12.3 32.2 12.3 44.5 0l22.2-22.2c12.3-12.3 12.3-32.2 0-44.5L242.7 256z"/></svg>
                    </span>
                    <!--end::Svg Icon-->
                  </span>
                  <!--end::Icon-->
                  <!--begin::Title-->
                  <div class="flex-grow-1 me-2">
                    <a href="?modulo=tableros&accion=show&id='. $rowrem['r_tablero'].'" class="fw-bolder text-gray-800 text-hover-primary fs-6">'. $rowrem['r_folio'].' '.$cliente.'</a>
                    <span class="text-muted fw-bold d-block">'. $caduca.'</span>
                    <span class="text-muted fw-bold d-block"><b>Asesor: </b> '. $vendedor.'</span>
                  </div>
                  <!--end::Title-->
                  <!--begin::Lable-->
                  <span class="fw-bolder text-success py-1"><a href="?modulo=tableros&accion=show&id='. $rowrem['r_tablero'].'"><button class="btn btn-info" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-eye" style="font-size: 10px;"></i></button></a></span>
                  <!--end::Lable-->
                </div>  
              </div>
              ';
            }
           
            while($rowrem = $resultrem2 -> fetch_array()){
              $nmb = busca($rowrem['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');
              $apellidos = busca($rowrem['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');
              $cliente = $nmb.' '.$apellidos;
              $fecha1 = new DateTime(date('Y-m-d')); 
              $fecha2 = new DateTime($rowrem['r_fliquidacion']); 
              $diferencia = $fecha1->diff($fecha2);
              if($diferencia->days == 1){
                $caduca = 'Caduca MAÑANA';
              } else {
                $caduca = 'Vence en '.$diferencia->days.' días';
              }
              $vendedor=busca($rowrem['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb)'); 
              $html .= '
              <div class="col-6 col-md-3">
                <div class="d-flex align-items-center bg-light-warning rounded p-5 mb-7">
                  <!--begin::Icon-->
                  <span class="svg-icon svg-icon-warning me-5">
                    <!--begin::Svg Icon | path: icons/duotune/abstract/abs027.svg-->
                    <span class="svg-icon svg-icon-1">
                      <svg style="fill:#FFC900" xmlns="http://www.w3.org/2000/svg" height="16" width="6" viewBox="0 0 192 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path d="M176 432c0 44.1-35.9 80-80 80s-80-35.9-80-80 35.9-80 80-80 80 35.9 80 80zM25.3 25.2l13.6 272C39.5 310 50 320 62.8 320h66.3c12.8 0 23.3-10 24-22.8l13.6-272C167.4 11.5 156.5 0 142.8 0H49.2C35.5 0 24.6 11.5 25.3 25.2z"/></svg>
                    </span>
                    <!--end::Svg Icon-->
                  </span>
                  <!--end::Icon-->
                  <!--begin::Title-->
                  <div class="flex-grow-1 me-2">
                    <a href="?modulo=tableros&accion=show&id='. $rowrem['r_tablero'].'"" class="fw-bolder text-gray-800 text-hover-primary fs-6">'. $rowrem['r_folio'].' '.$cliente.'</a>
                    <span class="text-muted fw-bold d-block">'. $caduca.'</span>
                    <span class="text-muted fw-bold d-block"><b>Asesor: </b> '. $vendedor.'</span>
                  </div>
                  <!--end::Title-->
                  <!--begin::Lable-->
                  <span class="fw-bolder text-success py-1"><a href="?modulo=tableros&accion=show&id='. $rowrem['r_tablero'].'"><button class="btn btn-info" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-eye" style="font-size: 10px;"></i></button></a></span>
                  <!--end::Lable-->
                </div>  
              </div>              
              ';
            }
           
            while($rowrem = $resultrem3 -> fetch_array()){
              $nmb = busca($rowrem['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');
              $apellidos = busca($rowrem['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');
              $cliente = $nmb.' '.$apellidos;
              $fecha1 = new DateTime(date('Y-m-d')); 
              $fecha2 = new DateTime($rowrem['r_fliquidacion']);
              $diferencia = $fecha1->diff($fecha2);
              if($diferencia->days == 1){
                $caduca = 'Caduca MAÑANA';
              } else {
                $caduca = 'Vence en '.$diferencia->days.' días';
              } 
              $vendedor=busca($rowrem['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb)'); 
              $html .= '
              <div class="col-6 col-md-3">
                <div class="d-flex align-items-center bg-light-success rounded p-5 mb-7">
                  <!--begin::Icon-->
                  <span class="svg-icon svg-icon-success me-5">
                    <!--begin::Svg Icon | path: icons/duotune/abstract/abs027.svg-->
                    <span class="svg-icon svg-icon-1">
                      <svg style="fill:#43E67F" xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><path d="M173.9 439.4l-166.4-166.4c-10-10-10-26.2 0-36.2l36.2-36.2c10-10 26.2-10 36.2 0L192 312.7 432.1 72.6c10-10 26.2-10 36.2 0l36.2 36.2c10 10 10 26.2 0 36.2l-294.4 294.4c-10 10-26.2 10-36.2 0z"/></svg>
                    </span>
                    <!--end::Svg Icon-->
                  </span>
                  <!--end::Icon-->
                  <!--begin::Title-->
                  <div class="flex-grow-1 me-2">
                    <a href="?modulo=tableros&accion=show&id='. $rowrem['r_tablero'].'" class="fw-bolder text-gray-800 text-hover-primary fs-6">'. $rowrem['r_folio'].' '.$cliente.'</a>
                    <span class="text-muted fw-bold d-block">'. $caduca.'</span>
                    <span class="text-muted fw-bold d-block"><b>Asesor: </b> '. $vendedor.'</span>
                  </div>
                  <!--end::Title-->
                  <!--begin::Lable-->
                  <span class="fw-bolder text-success py-1"><a href="?modulo=tableros&accion=show&id='. $rowrem['r_tablero'].'"><button class="btn btn-info" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-eye" style="font-size: 10px;"></i></button></a></span>
                  <!--end::Lable-->
                </div>  
              </div>
              ';
            }
            } else {
              $html .= '
              <center>
                <div class="col-10 alert alert-success ms-2"> No hay ventas por caducar </div>
              </center>
              ';
            }
            $html .= '
        </div>
      </div>';
      
        $html .= '<div class="col-12 col-sm-12 col-xs-12">
        <h2 class="alert alert-primary">Artículos por definir</h2>
        <div class="row col-12">
          ';
            $menos5dias = date('Y-m-d', strtotime('+5 days'));
            $menos3dias = date('Y-m-d', strtotime('+3 days'));
            $menos30dias = date('Y-m-d', strtotime('-30 days'));
            $sqlrem ='SELECT * FROM remisiones INNER JOIN remisionesc ON r_id = rc_remision WHERE r_estatus IN ("A", "F") AND r_estatus NOT IN ("FE", "C") AND DATE(r_faplica) >= "'.$menos30dias.'" AND rc_tipoenvio = "P"'; //remisiones que caducan despues de 5 días
            if($grupo == "VENTAS") $sqlrem.=' AND r_encargado = "'.$_SESSION['uid'].'"';
            $sqlrem .= '  GROUP BY r_id';
            $resultrem1 = setq($sqlrem);
            
            
            if($resultrem1->num_rows > 0){
              while($rowrem = $resultrem1 -> fetch_array()){
                $nmb = busca($rowrem['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');
                $apellidos = busca($rowrem['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');
                $cliente = $nmb.' '.$apellidos;
                $fecha1 = new DateTime($menos3dias);
                $fecha2 = new DateTime(date('Y-m-d',strtotime($rowrem['r_faplica'])));

                $diferencia = $fecha1->diff($fecha2, false);
                if($diferencia->days == 0){
                  $caduca = 'Vence HOY';
                }else if($diferencia->days == 1){
                  $caduca = 'Vence MAÑANA';
                } else if($diferencia->days > 1 && $diferencia->invert == false){
                  $caduca = 'Vence en '.$diferencia->days.' días';
                } else if($diferencia->invert == true){
                  $caduca = 'Vencida';
                }
                $vendedor=busca($rowrem['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb)');
                $html .= '
                <div class="col-6 col-md-3">
                  <div class="d-flex align-items-center alert-danger rounded p-5 mb-7">
                    <!--begin::Icon-->
                    <span class="svg-icon svg-icon-danger me-5">
                      <!--begin::Svg Icon | path: icons/duotune/abstract/abs027.svg-->
                      <span class="svg-icon svg-icon-1">
                        <svg style="fill:#FF0000" xmlns="http://www.w3.org/2000/svg" height="16" width="11" viewBox="0 0 352 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path d="M242.7 256l100.1-100.1c12.3-12.3 12.3-32.2 0-44.5l-22.2-22.2c-12.3-12.3-32.2-12.3-44.5 0L176 189.3 75.9 89.2c-12.3-12.3-32.2-12.3-44.5 0L9.2 111.5c-12.3 12.3-12.3 32.2 0 44.5L109.3 256 9.2 356.1c-12.3 12.3-12.3 32.2 0 44.5l22.2 22.2c12.3 12.3 32.2 12.3 44.5 0L176 322.7l100.1 100.1c12.3 12.3 32.2 12.3 44.5 0l22.2-22.2c12.3-12.3 12.3-32.2 0-44.5L242.7 256z"/></svg>
                      </span>
                      <!--end::Svg Icon-->
                    </span>
                    <!--end::Icon-->
                    <!--begin::Title-->
                    <div class="flex-grow-1 me-2">
                      <a href="?modulo=remisiones&accion=prodpordefinir&tipo=2" class="fw-bolder text-gray-800 text-hover-primary fs-6">'. $rowrem['r_folio'].' '.$cliente.'</a>
                      <span class="text-muted fw-bold d-block">'. $caduca.'</span>
                      <span class="text-muted fw-bold d-block"><b>Asesor: </b> '. $vendedor.'</span>
                    </div>
                    <!--end::Title-->
                    <!--begin::Lable-->
                    <span class="fw-bolder text-success py-1"><a href="?modulo=remisiones&accion=prodpordefinir&tipo=2"><button class="btn btn-info" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-eye" style="font-size: 10px;"></i></button></a></span>
                    <!--end::Lable-->
                  </div>  
                </div>
                ';
              }
            } else {
              $html .= '
              <center>
                <div class="col-10 alert alert-success ms-2"> No hay articulos por definir </div>
              </center>
              ';
            }
            $html .= '
        </div>
      </div> ';
      
      $html .= '<div class="col-12 col-sm-12 col-xs-12">
        <h2 class="alert alert-primary">Ventas de la semana</h2>
        <table class="table table-responsive">
          <thead class="alert text-white" style="background: #343EDB">
          <th style="width: 15%">Asesor</th>
          <th style="width: 7%">Lu</th>
          <th style="width: 7%">Ma</th>
          <th style="width: 7%">Mi</th>
          <th style="width: 7%">Ju</th>
          <th style="width: 7%">Vi</th>
          <th style="width: 7%">Sa</th>
          <th style="width: 9%">Σ semana</th>
          <th style="width: 9%">Σ mes</th>
          <th style="width: 9%">% meta semanal</th>
          <th style="width: 9%">% meta mensual</th>
          <th style="width: 7%">% Al grupo</th>
          </thead>
          <tbody>
            ';
            //$sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_grupo = "VENTAS"';
            $sql = 'SELECT * FROM remisiones INNER JOIN usuarios ON u_id = r_uaplica WHERE DATE(r_faplica) BETWEEN "'.date('Y-m-01').'" AND "'.date('Y-m-d').'" AND u_grupo = "VENTAS" GROUP BY r_uaplica';
            $result = setq($sql);

            $numr = 0;

            $suma1 = 0;
            $suma2 = 0;
            $suma3 = 0;
            $suma4 = 0;
            $suma5 = 0;
            $suma6 = 0;
            $suma7 = 0;
            $sumasem = 0;
            $sumames = 0;
            $sumapsem= 0;
            $sumapmes= 0;
            $sumapgrupo= 0;
            while($row = $result -> fetch_array()){
              $numr++;
              if($numr%2 == 0) $back = "#949494";
              else $back = "#FFFFFF";
              $html .= '
              <tr style="background-color:'. $back .'">';
                  if(!$datos[1][$row['u_id']]) $datos[1][$row['u_id']] = 0;
                  if(!$datos[2][$row['u_id']]) $datos[2][$row['u_id']] = 0;
                  if(!$datos[3][$row['u_id']]) $datos[3][$row['u_id']] = 0;
                  if(!$datos[4][$row['u_id']]) $datos[4][$row['u_id']] = 0;
                  if(!$datos[5][$row['u_id']]) $datos[5][$row['u_id']] = 0;
                  if(!$datos[6][$row['u_id']]) $datos[6][$row['u_id']] = 0;

                  $suma = $datos[1][$row['u_id']] + $datos[2][$row['u_id']]+ $datos[3][$row['u_id']]+$datos[4][$row['u_id']]+$datos[5][$row['u_id']]+$datos[6][$row['u_id']];

                  $totacum = busca($row['u_id'], 'remisiones', 'DATE(r_faplica) >= "'.date('Y-m-d', strtotime('first day of this month')).'"
                            AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime('last day of this month')).'" AND
                            r_estatus IN ("A", "F", "FE") AND r_encargado', 'SUM(r_total)-SUM(r_precioenvio)');


                  $meta = busca($row['u_id'], 'usuarios_orden', 'uo_uid', 'uo_metamensual');
                  $metagrupo = busca(date('m'), 'config_metagrupal','cm_anio = "'.date('Y').'" AND cm_mes', 'cm_meta');
                  $metasemana = $meta/4;
                  $porcentaje = ($totacum / $meta)*100;
                  $porsemana = ($suma/$metasemana)*100;
                  $porgrupo = ($totacum/$metagrupo)*100;

                $suma1+=$datos[1][$row['u_id']];
                $suma2+=$datos[2][$row['u_id']];
                $suma3+=$datos[3][$row['u_id']];
                $suma4+=$datos[4][$row['u_id']];
                $suma5+=$datos[5][$row['u_id']];
                $suma6+=$datos[6][$row['u_id']];
                $suma7+=$datos[7][$row['u_id']];
                $sumasem+=$suma;
                $sumames+=$totacum;
                $sumapsem+=$porsemana;
                $sumapmes+=$porcentaje;
                $sumapgrupo+=$porgrupo;
                $url = '';
                if($grupo == "ADMIN" || $grupo == "GERENCIA"){
                  $url = 'data-fancybox data-type="ajax" data-src="popup/tablerosvendedor.php?vendedor='.$row['u_id'].'&mes='.date('m').'" href="javascript:;"';
                }
                  $html .= '
                  <td>
                    <a  '.$url.' class="d-flex text-dark text-hover-primary align-items-center">
                      '. $row['u_nmb'].' '.$row['u_apellidos'] .'
                    </a>
                  </td>
                <td class="number-align">$'. number_format($datos[1][$row['u_id']], 2).'</td>
                <td class="number-align">$'. number_format($datos[2][$row['u_id']], 2).'</td>
                <td class="number-align">$'. number_format($datos[3][$row['u_id']], 2).'</td>
                <td class="number-align">$'. number_format($datos[4][$row['u_id']], 2).'</td>
                <td class="number-align">$'. number_format($datos[5][$row['u_id']], 2).'</td>
                <td class="number-align">$'. number_format($datos[6][$row['u_id']], 2).'</td>
                <td class="number-align">$'. number_format($suma,2).'</td>
                <td class="number-align">$'. number_format($totacum,2).'</td>
                <td>'. number_format($porsemana, 2).' %</td>
                <td>'. number_format($porcentaje, 2).' %</td>
                <td>'. number_format($porgrupo, 2).' %</td>
                ';
                //}

            }
            $html .= '
            </tr>
              ';
          //if($grupo == "ADMIN" || $grupo == "GERENCIA"){
                  $promsem = $sumapsem/$numr;
                  $prommes = $sumapmes/$numr;
              $html .= '
            <tr style="border-top: 2px solid #000;">
              <th>Totales</th>
                  <td class="number-align">$'. number_format($suma1, 2).'</td>
                  <td class="number-align">$'. number_format($suma2, 2).'</td>
                  <td class="number-align">$'. number_format($suma3, 2).'</td>
                  <td class="number-align">$'. number_format($suma4, 2).'</td>
                  <td class="number-align">$'. number_format($suma5, 2).'</td>
                  <td class="number-align">$'. number_format($suma6, 2).'</td>
                  <td class="number-align">$'. number_format($sumasem,2).'</td>
                  <td class="number-align">$'. number_format($sumames,2).'</td>
                  <td>'. number_format($promsem, 2).' %</td>
                  <td>'. number_format($prommes, 2).' %</td>
                  <td>'. number_format($sumapgrupo, 2).' %</td>
            </tr>
            '; //Equivalencia
              $dia1delmes = date('j',strtotime('first monday of this month'));
              $metasem = $meta/4;
              switch($dia1delmes){
                case "1":
                  $fini[1] = date('Y-m-').'01';
                  $ffin[1] = date('Y-m-').'07';

                  $fini[2] = date('Y-m-').'08';
                  $ffin[2] = date('Y-m-').'14';

                  $fini[3] = date('Y-m-').'15';
                  $ffin[3] = date('Y-m-').'21';

                  $fini[4] = date('Y-m-').'22';
                  $ffin[4] = date('Y-m-d',strtotime('last day of month'));
                break;
                case "2":
                  $fini[1] = date('Y-m-').'01';
                  $ffin[1] = date('Y-m-').'08';

                  $fini[2] = date('Y-m-').'09';
                  $ffin[2] = date('Y-m-').'15';

                  $fini[3] = date('Y-m-').'16';
                  $ffin[3] = date('Y-m-').'22';

                  $fini[4] = date('Y-m-').'23';
                  $ffin[4] = date('Y-m-d',strtotime('last day of month'));
                break;
                case "3":
                  $fini[1] = date('Y-m-').'01';
                  $ffin[1] = date('Y-m-').'09';

                  $fini[2] = date('Y-m-').'10';
                  $ffin[2] = date('Y-m-').'16';

                  $fini[3] = date('Y-m-').'17';
                  $ffin[3] = date('Y-m-').'23';

                  $fini[4] = date('Y-m-').'24';
                  $ffin[4] = date('Y-m-d',strtotime('last day of month'));
                break;
                case "4":
                  $fini[1] = date('Y-m-').'01';
                  $ffin[1] = date('Y-m-').'10';

                  $fini[2] = date('Y-m-').'11';
                  $ffin[2] = date('Y-m-').'17';

                  $fini[3] = date('Y-m-').'18';
                  $ffin[3] = date('Y-m-').'24';

                  $fini[4] = date('Y-m-').'25';
                  $ffin[4] = date('Y-m-d',strtotime('last day of month'));
                break;
                case "5":
                  $fini[1] = date('Y-m-').'01';
                  $ffin[1] = date('Y-m-').'11';

                  $fini[2] = date('Y-m-').'12';
                  $ffin[2] = date('Y-m-').'18';

                  $fini[3] = date('Y-m-').'19';
                  $ffin[3] = date('Y-m-').'25';

                  $fini[4] = date('Y-m-').'26';
                  $ffin[4] = date('Y-m-d',strtotime('last day of month'));
                break;
                case "6":
                  $fini[1] = date('Y-m-').'01';
                  $ffin[1] = date('Y-m-').'12';

                  $fini[2] = date('Y-m-').'13';
                  $ffin[2] = date('Y-m-').'19';

                  $fini[3] = date('Y-m-').'20';
                  $ffin[3] = date('Y-m-').'26';

                  $fini[4] = date('Y-m-').'27';
                  $ffin[4] = date('Y-m-d',strtotime('last day of month'));
                break;
                case "7":
                  $fini[1] = date('Y-m-').'01';
                  $ffin[1] = date('Y-m-').'06';

                  $fini[2] = date('Y-m-').'07';
                  $ffin[2] = date('Y-m-').'13';

                  $fini[3] = date('Y-m-').'14';
                  $ffin[3] = date('Y-m-').'20';

                  $fini[4] = date('Y-m-').'21';
                  $ffin[4] = date('Y-m-d',strtotime('last day of month'));
                break;
              }
              $hoy = date('Y-m-d');
              $semana = NULL;
              for($i=1;$i<=4;$i++){
                if($hoy <= $ffin[$i]  && $hoy >= $fini[$i]){
                  $semana = $i;
                  break;
                }
              }
          //              echo $semana.'<<br>';} 
            $html .= '
          </tbody>
          <tfoot>

          </tfoot>
        </table>
      </div>
      ';
            //}
      //if($grupo == "ADMIN" || $grupo == "GERENCIA"){
        $sql = 'SELECT * FROM config_comisiones WHERE cc_estatus = "A"';
        $result = setq($sql);
        $numrows = $result->num_rows;
        $ancho = 100/($numrows+1);
        $numr = 0;
        $sumtot = 0;
        $html .= '
        <div class="col-12 col-sm-12 col-xs-12">
          <h2 class="alert alert-primary">Comisiones por venta</h2>
          <table class="table table-responsive">
            <thead class="alert text-white" style="background: #343EDB">
              <th width="'. $ancho .'%">Asesor</th>
              ';
              while($row = $result->fetch_array()){
                $html .= '
                  <th width="'. $ancho .'%">'. $row['cc_nmb'].'</th>
                ';
              }
              $html .= '
            </thead>
            <tbody>
            '; 
              $sql2 = 'SELECT * FROM usuarios WHERE u_grupo = "VENTAS" AND u_estatus = "A"';
              $result2 = setq($sql2);
              while($row2 = $result2 -> fetch_array()){
                $numr++;
                if($numr%2 == 0) $back = "#949494";
                else $back = "#FFFFFF";
                $html .= '
                  <tr>
                    <td style="background-color:'. $back .'">'. $row2['u_nmb'].' '.$row2['u_apellidos'].'</td>
                ';
                $totacumind = busca($row2['u_id'], 'remisiones', 'DATE(r_faplica) >= "'.date('Y-m-d', strtotime('first day of this month')).'"
                AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime('last day of this month')).'" AND
                r_estatus IN ("A", "F", "FE") AND r_encargado', 'SUM(r_total)-SUM(r_precioenvio)');
                $sumtot += $totacumind;
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  
                  $comision = ((($totacumind/$sumames) *($row['cc_montorepartido']*0.7)));
                  //if($_SESSION['uid'] == "ADMIN") echo 'vendedor: '.$row2['u_nmb'].' nmb: '.$row['cc_nmb'].' totacum: '.$totacum.' totacumind: '.$totacumind.' repartir: '.$row['cc_montorepartido'].' meta: '.$row['cc_metaventa'].' comision: '.$comision.'<br>';
                  $html .= '
                    <td style="background-color:'. $back .'" class="number-align">$'. number_format($comision, 2).'</td>
                  ';
                }
                
                $html .= '
                  </tr>
                ';
              }
              $numr++;
              if($numr%2 == 0) $back = "#949494";
              else $back = "#FFFFFF";
              $html.='<tr>
              <td style="background-color:'. $back .'"> LOGÍSTICA </td>';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                $comision = (($row['cc_montorepartido'] * 0.06));
                $html .= '
                  <td style="background-color:'. $back .'" class="number-align">$'. number_format($comision, 2).'</td>
                ';
              }
              $html .= '</tr>';

              $numr++;
              if($numr%2 == 0) $back = "#949494";
              else $back = "#FFFFFF";
              $html.='<tr>
              <td style="background-color:'. $back .'"> MARKETING </td>';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                $comision = (($row['cc_montorepartido'] * 0.04));
                $html .= '
                  
                  <td style="background-color:'. $back .'" class="number-align">$'. number_format($comision, 2).'</td>';
              }
              $html .= '</tr>';

              /* $numr++;
              if($numr%2 == 0) $back = "#949494";
              else $back = "#FFFFFF";
              $html.='<tr>
              <td style="background-color:'. $back .'"> GERENCIA </td>';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                $comision = (($row['cc_montorepartido'] * 0.2));
                $html .= '
                  
                  <td style="background-color:'. $back .'" class="number-align">$'. number_format($comision, 2).'</td>';
              }
              $html .= '</tr>'; */

                
              
            $html .= '
            </tbody>
            <tfoot>
              <tr style="border-top: 2px solid #000;">
                <td>Restante para meta</td>
                ';
                  $result = setq($sql);
                  while($row = $result->fetch_array()){
                    $restante = $row['cc_metaventa'] - $sumames; 
                    if($restante <= 0){
                      $style = 'bg-success';
                      $texto = '¡Completada!'; 
                    } else {
                      $style = 'number-align';
                      $texto = '$'.number_format($restante, 2); 
                    }
                    $html .= '
                      <td class="'. $style.'">'. $texto.'</td>
                    ';
                  }
                $html .= '
              </tr>
            </tfoot>
          </table>
        </div>  
        ';
      //}
      
      if($grupo == "ADMIN" || $grupo == "GERENCIA"){
        $sql = 'SELECT c_articulosminimos, c_comision FROM configuracionesp WHERE c_id = "1"';
        $result = setq($sql);
        list($minimo, $comisionxinf) = $result -> fetch_array();
        $html .= '
        <div class="col-12 col-sm-12 col-xs-12">
          <h2 class="alert alert-primary">Comisiones por inflables vendidos</h2>
          <table class="table table-responsive">
            <thead class="alert text-white" style="background: #343EDB">
              <th>Asesor</th>
              <th>Meta</th>
              <th>Inflables vendidos</th>
              <th>Comisión</th>
            </thead>
            <tbody>
            '; 
              $suminf = 0;
              $sumcon = 0;
              $sql2 = 'SELECT * FROM remisiones INNER JOIN usuarios ON u_id = r_uaplica WHERE DATE(r_faplica) BETWEEN "'.date('Y-m-01').'" AND "'.date('Y-m-d').'" AND u_grupo = "VENTAS" GROUP BY r_uaplica';
              $result2 = setq($sql2);
              while($row2 = $result2 -> fetch_array()){
                $numr++;
                if($numr%2 == 0) $back = "#949494";
                else $back = "#FFFFFF";
                $sqlinf = 'SELECT COUNT(*) cantidad FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN articulos ON a_id = rc_articulo
                  WHERE r_encargado = "'.$row2['u_id'].'" AND DATE(r_faplica) >= "'.date('Y-m-d', strtotime('first day of this month')).'"
                  AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime('last day of this month')).'" AND r_estatus IN ("A", "F", "FE") AND a_categoria IN(SELECT cat_id FROM categorias WHERE cat_inflable = "1") AND rc_ligado IS NULL'; 
                $resultinf = setq($sqlinf);
                list($cantidad) = $resultinf -> fetch_array();
                $meta = $minimo - $cantidad;
                $comision = 0;
                $fondo = 'bg-warning';
                if($meta > 0) $leyenda = 'Faltan '.$meta.' inflables';
                else {
                  $leyenda = '¡Completada!';
                  $comision = $cantidad * $comisionxinf;
                  $fondo = 'bg-success';
                }
                $suminf += $cantidad;
                $sumcom += $comision;
                $html .= '
                  <tr>
                    <td style="background-color:'. $back .'">'. $row2['u_nmb'].' '.$row2['u_apellidos'].'</td>
                    <td style="background-color:'. $back .'" class="'. $fondo.'">'. $leyenda.'</td>
                    <td style="background-color:'. $back .'">'. $cantidad.'</td>
                    <td style="background-color:'. $back .'">$'. number_format($comision, 2).'</td>
                  </tr>
                ';
              }
            $html .= '
            </tbody>
            <tfoot>
              <tr style="border-top: 2px solid #000;">
                <td colspan="2">Total</td>
                <td>'. $suminf.'</td>
                <td>$'. number_format($sumcom,2).'</td>
              </tr>
            </tfoot>
          </table>
        </div> 
        ';
      }

    //if($_SESSION['uid'] == "ADMIN"){
      $i = 0;
      $html .= '
      <div class="col-12 col-sm-12 col-xs-12">
        <h2 class="alert alert-primary">Ventas por categoria</h2>
      </div> 
      ';
        $html .= '<div class="col-12 col-sm-12 col-xs-12 row">

       ';
        $sqlvent = 'SELECT SUM(rd_cantidad) as cantidad, cat_nmb FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision INNER JOIN articulos ON a_id = rd_articulo INNER JOIN categorias ON cat_id = a_categoria
         WHERE DATE(r_faplica) BETWEEN "'.date('Y-m-d', strtotime('first day of this month')).'" AND "'.date('Y-m-d', strtotime('last day of this month')).'" AND r_estatus IN ("A", "F", "FE")';
        if($grupo == "VENTAS") $sqlvent .= ' AND r_uaplica = "'.$_SESSION['uid'].'"';
        $sqlvent .= ' GROUP BY a_categoria';
        $resultvent = setq($sqlvent);
        while($rowvent = $resultvent -> fetch_array()){
          $html.='<div class="col-xl-3">
                <!--begin::Statistics Widget 5-->
                <a class="card bg-'. $colores[$i] .' hoverable card-xl-stretch mb-xl-8">
                  <!--begin::Body-->
                  <div class="card-body">
                    <div class="text-white fw-bolder fs-2 mb-2 mt-5">'. $rowvent['cat_nmb'].'</div>
                    <div class="fw-bold text-white">'. number_format($rowvent['cantidad'], 0).' vendidos</div>
                  </div>
                  <!--end::Body-->
                </a>
                <!--end::Statistics Widget 5-->
              </div>
            ';
            $i++;
            if($i == 5) $i = 0;
        }
        $sqlpaq = 'SELECT SUM(rd_cantidad) as cantidad FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision INNER JOIN articulos ON a_id = rd_articulo INNER JOIN categorias ON cat_id = a_categoria
         WHERE DATE(r_faplica) BETWEEN "'.date('Y-m-d', strtotime('first day of this month')).'" AND "'.date('Y-m-d', strtotime('last day of this month')).'" AND a_tipoprod= "M" AND r_estatus IN ("A", "F", "FE")';
         if($grupo == "VENTAS") $sqlpaq .= ' AND r_uaplica = "'.$_SESSION['uid'].'"';
        $resultpaq = setq($sqlpaq);
        list($paquetes) = $resultpaq -> fetch_array();
        $html.='<div class="col-xl-3  ">
                <!--begin::Statistics Widget 5-->
                <a class="card bg-'. $colores[$i] .' hoverable card-xl-stretch mb-xl-8">
                  <!--begin::Body-->
                  <div class="card-body">
                    <div class="text-white fw-bolder fs-2 mb-2 mt-5">Paquetes</div>
                    <div class="fw-bold text-white">'. number_format($paquetes, 0).' vendidos</div>
                  </div>
                  <!--end::Body-->
                </a>
                <!--end::Statistics Widget 5-->
              </div>
            ';
        $i++;

        $html .= '</div>';
    //}
  }

  echo $html;

?>