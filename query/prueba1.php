<?php
include_once('../funciones.php');
if($estatusproceso == "F"){

  $proceso = 18; //proceso actual finalizado
  $ordenp = 17; //orden de producción

  $sqlorden = 'SELECT pp_orden FROM pr_procesosop WHERE pp_id = "'.$proceso.'" ';
  $resultorden = setq($sqlorden);
  list($ordenactual) = $resultorden->fetch_array();

  $sqlsig = 'SELECT pp_id, pp_orden FROM pr_procesosop WHERE pp_orden > '.$ordenactual.' AND pp_ordenp = "'.$ordenp.'" ORDER BY pp_orden ASC LIMIT 0,1 ';
  $resultsig = setq($sqlsig);
  list($idsp,$ordens) = $resultsig->fetch_array();

  $sqlsig2 = 'SELECT pp_id FROM pr_procesosop WHERE pp_orden > '.$ordens.' AND pp_ordenp = "'.$ordenp.'" ORDER BY pp_orden ASC LIMIT 0,1 ';
  $resultsig2 = setq($sqlsig2);
  list($idsp2) = $resultsig2->fetch_array();

  if($idsp != "" && isset($idsp)){ // Si existe proceso siguiente al finalizado ingresa a actualizar aritulos, sino no;

    $sql = 'SELECT * FROM pr_articulosop WHERE pra_op = "'.$ordenp.'" AND pra_pa = "'.$proceso.'" ';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $sqlup = 'UPDATE pr_articulosop SET
                pra_pa = pra_sp,
                pra_estpa = pra_estsp,
                pra_sp = "'.$idsp2.'",
                pra_estsp = "N"
                WHERE pra_id = "'.$row['pra_id'].'" AND pra_op = "'.$ordenp.'" ';
      setq($sqlup,true);
    }
    

  }else{

    // ACTUALIZA ORDEN DE PRODUCCION A F PORQUE ES PROCESO FINAL
    $sql = 'UPDATE pr_ordenprod SET
            po_estatus = "F" 
            WHERE po_id = "'.$ordenp.'"
            ';
    setq($sql);

  }
  

}


?>