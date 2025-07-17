<?php
function consult(){
    $sql = 'SELECT *
    FROM pr_procesosop
    WHERE pp_bloqueo = 0 
      AND pp_orden < 4 
      AND pp_ordenp = 3
      AND pp_orden = (SELECT MAX(pp_orden) FROM pr_procesosop WHERE pp_ordenp = 3) - 1;
    ';
    $result = setq($sql);
    $
}
?>