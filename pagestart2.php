<?php
  /* ini_set('display_error', 1); */
  include_once('funciones.php');
  session_start();
  $meses = array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
  $colores = array('primary', 'danger', 'warning', 'info', 'success');
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
    while($row = $result -> fetch_array()){
      if(!$datos[$date->format('w')][$row['r_encargado']]) $datos[$date->format('w')][$row['r_encargado']] = 0;
      $datos[$date->format('w')][$row['r_encargado']] += ($row['r_total']-$row['r_precioenvio']);
    }
  }

  $totacum = busca(1, 'remisiones', 'DATE(r_faplica) >= "'.date('Y-m-d', strtotime('first day of this month')).'"
  AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime('last day of this month')).'" AND
  r_estatus IN ("A", "F", "FE") AND 1', 'SUM(r_total)-SUM(r_precioenvio)'); //Ventas del mes

  $metagrupo = busca(date('m'), 'config_metagrupal','cm_anio = "'.date('Y').'" AND cm_mes', 'cm_meta'); // Meta del mes 

  $porventa = $totacum *100 / $metagrupo;

?>
  <div class="card col-12">
    <div class="card-body">
      <section id="minimal-statistics">
      <div class="row">
          <div class="col-xs-12 mt-1 mb-3">
              <h4>Estadisticas de arranque</h4>
              <p>Ve a perseguir tus metas, así vamos en el mes de <?php echo  $meses[date('n')] ?>.</p>
              <hr>
          </div>
      </div>
      <div class="col-12 mb-10">
          <div class="row gy-5 g-xl-8">
            <!--begin::Col-->
            <div class="col-xl-12">
              <!--begin::List Widget 3-->
              <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0">
                  <h3 class="card-title fw-bolder text-dark">Ventas por asesor</h3>
                  <div class="card-toolbar">
                  </div>
                </div>
                <!--end::Header-->
                <div class="progress mt-2" style="background: #bcbcbc !important; height: 20px">
                <?php
                  $i=0;
                  $j=0;
                  $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_grupo = "VENTAS"';
                  $result = setq($sql);
                  while($row = $result -> fetch_array()){
                    if($j%2 == 0) $pos = 'top';
                    else $pos = 'bottom';
                    $totacumu = busca($row['u_id'], 'remisiones', 'DATE(r_faplica) >= "'.date('Y-m-d', strtotime('first day of this month')).'"
                              AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime('last day of this month')).'" AND
                              r_estatus IN ("A", "F", "FE") AND r_encargado', 'SUM(r_total)-SUM(r_precioenvio)');

                    $porgrupo = ($totacumu/$metagrupo)*100;
                    ?>
                    <div class="progress-bar bg-<?php echo $colores[$i]; ?>" role="progressbar" style="width: <?php echo $porgrupo?>%; heigth:20px" aria-valuenow="<?php echo $porgrupo?>" 
                      aria-valuemin="0" aria-valuemax="100"  data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="<?php echo $pos; ?>" title="<b>Asesor:</b> <?php echo $row['u_nmb'].' '.$row['u_apellidos'] ?> <br> 
                       <b>Vendido: </b> $<?php echo  number_format($totacumu, 2)?> <br>  <b> % de la meta:</b> <?php echo  number_format($porgrupo, 2)?>%">
                       <?php echo number_format($porgrupo, 2); ?>%
                    </div>
                    <?php
                    $i++;
                    $j++;
                    if($i==5) $i=0;
                  }
                  ?>
                </div>  
              </div>
              <!--end:List Widget 3-->
            </div>
            <!--end::Col-->
          </div>
        </div>
      <div class="row">
        <div class="col-8">
          <div class="card bg-light-success card-xl-stretch mb-xl-8 col-12">
            <div class="card-body my-3">
              <div class="py-1">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="card-title fw-bolder text-success fs-3 mb-3">Meta de venta</span>
                    <span class="card-title fw-bolder text-success fs-1 mb-3">$ <?php echo number_format($metagrupo, 2); ?></span>
                </div>
              </div>
              <div class="py-1">
                <span class="text-dark fs-1 fw-bolder me-2">$ <?php echo number_format($totacum, 2); ?></span>
                <span class="fw-bold text-muted fs-7">Ventas del mes</span>
              </div>
              <div class="progress h-30px bg-success bg-opacity-50 mt-7">
                <div class="progress-bar bg-success fs-4" role="progressbar" style="width: <?php echo number_format($porventa, 2); ?>%; height" aria-valuenow="<?php echo number_format($porventa, 2); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($porventa, 2); ?>% </div>
              </div>
            </div>
          </div>
          <div class="col-xl-12">
              <!--begin::List Widget 3-->
              <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0">
                  <h3 class="card-title fw-bolder text-dark">Ventas por categoría</h3>
                  <div class="card-toolbar">
                  </div>
                </div>
                <!--end::Header-->
                <div class="row">
                  <?php 
                    $i =0;
                    $sql = 'SELECT * FROM categorias WHERE cat_estatus = "A"';
                    $result = setq($sql);
                    while($row = $result -> fetch_array()){
                      $sqlven = 'SELECT SUM(rd_cantidad) cantidad, SUM(rd_precio*rd_cantidad) precio FROM remisiones INNER JOIN remisionesd ON rd_remision = r_id INNER JOIN articulos ON a_id = rd_articulo
                      WHERE DATE(r_faplica) >= "'.date('Y-m-d', strtotime('first day of this month')).'"
                      AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime('last day of this month')).'" AND
                      r_estatus IN ("A", "F", "FE") AND a_categoria = "'.$row['cat_id'].'" AND a_tipoprod != "M" GROUP BY a_categoria ';
                      $resultven = setq($sqlven);
                      list($cantidad, $precio) = $resultven ->fetch_array();
                    ?>
                      <div class="col-xl-4">
                        <!--begin::Statistics Widget 5-->
                        <a class="card bg-<?php echo $colores[$i] ?> hoverable card-xl-stretch mb-xl-8">
                          <!--begin::Body-->
                          <div class="card-body">
                            <div class="text-white fw-bolder fs-2 mb-2 mt-5"><?php echo $row['cat_nmb']; ?></div>
                            <div class="fw-bold text-white"><?php echo number_format($cantidad, 0); ?> vendidos</div>
                            <div class="fw-bold text-white">$<?php echo number_format($precio, 2); ?> </div>
                          </div>
                          <!--end::Body-->
                        </a>
                        <!--end::Statistics Widget 5-->
                      </div>
                    <?php
                    $i++;
                    if($i == 5) $i = 0;
                  }
                  $sqlven = 'SELECT SUM(rd_cantidad) cantidad, SUM(rd_precio*rd_cantidad) precio FROM remisiones INNER JOIN remisionesd ON rd_remision = r_id INNER JOIN articulos ON a_id = rd_articulo
                  WHERE DATE(r_faplica) >= "'.date('Y-m-d', strtotime('first day of this month')).'"
                  AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime('last day of this month')).'" AND
                  r_estatus IN ("A", "F", "FE") AND a_tipoprod = "M"';
                  $resultven = setq($sqlven);
                  list($cantidad, $precio) = $resultven ->fetch_array();
                  ?>
                  <div class="col-xl-4">
                    <!--begin::Statistics Widget 5-->
                    <a class="card bg-<?php echo $colores[$i] ?> hoverable card-xl-stretch mb-xl-8">
                      <!--begin::Body-->
                      <div class="card-body">
                        <div class="text-white fw-bolder fs-2 mb-2 mt-5">PAQUETES</div>
                        <div class="fw-bold text-white"><?php echo number_format($cantidad, 0); ?> paquetes vendidos</div>
                        <div class="fw-bold text-white">$<?php echo number_format($precio, 2); ?></div>
                      </div>
                      <!--end::Body-->
                    </a>
                    <!--end::Statistics Widget 5-->
                  </div>
                </div>
              </div>
              <!--end:List Widget 3-->
            </div>
          
        </div>
        <?php
        $sql10 = 'SELECT COUNT(*) cantidad, rc_modelo, a_nmb, rc_articulo FROM remisiones INNER JOIN remisionesc ON rc_remision = r_id INNER JOIN articulos ON a_id = rc_articulo 
        WHERE DATE(r_faplica) >= "'.date('Y-m-d', strtotime('first day of this month')).'" AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime('last day of this month')).'" AND
        r_estatus IN ("A", "F", "FE") AND a_categoria IN(SELECT cat_id FROM categorias WHERE cat_inflable = "1")
        GROUP BY rc_articulo, rc_modelo ORDER BY cantidad DESC LIMIT 0,10';
        $result10 = setq($sql10);
        $i = 1;
        $j = 0;
        ?>
        <div class="col-4">
          <div class="row gy-5 g-xl-8">
            <!--begin::Col-->
            <div class="col-xl-12">
              <!--begin::List Widget 3-->
              <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0">
                  <h3 class="card-title fw-bolder text-dark">Top 10 Inflables vendidos</h3>
                  <div class="card-toolbar">
                  </div>
                </div>
                <!--end::Header-->
                <?php 
              while($row10 = $result10 -> fetch_array()){
                $nmb = $row10['a_nmb'];
                if($row10['rc_modelo']) $nmb.= ' '.busca($row10['rc_modelo'], 'articulos_variantes' ,'av_articulo = "'.$row10['rc_articulo'].'" AND av_modelo', 'av_nmb');
                ?>
                <!--begin::Body-->
                <div class="card-body pt-0">
                  <!--begin::Item-->
                  <div class="d-flex align-items-center mb-2">
                  <span class="badge badge-light-<?php echo $colores[$j]; ?> fs-5 fw-bolder"><?php echo $i; ?></span>
                    <!--begin::Checkbox-->
                    <div class="form-check form-check-custom form-check-solid mx-5">
                    </div>
                    <!--end::Checkbox-->
                    
                      <!--begin::Description-->
                      <div class="flex-grow-1">
                        <span class="text-gray-800 fw-bolder fs-6"><?php echo $nmb; ?></span>
                        <span class="text-muted fw-bold d-block"><?php echo $row10['cantidad']; ?> vendidos</span>
                      </div>
                      <!--end::Description-->
                      <!--begin::Bullet-->
                    <span class="bullet bullet-vertical h-40px bg-<?php echo $colores[$j]; ?>"></span>
                    <!--end::Bullet-->
                      
                    
                  </div>
                  <!--end:Item-->
                  <!--end:Item-->
                </div>
                <!--end::Body-->
                <?php
                  $i++;
                  $j++;
                  if($j==5) $j=0; 
                }
                ?>
              </div>
              <!--end:List Widget 3-->
            </div>
            <!--end::Col-->
          </div>
        </div>
      </div>
  </section>
  </div>
</div>
  

  