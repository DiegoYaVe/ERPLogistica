<?php
include_once('../funciones.php');


$sql = 'SELECT * FROM `articulo_proveedor` WHERE ap_estatus = "1" AND ap_idproducto IS NULL AND ap_proveedor = "3"';
$result = setq($sql);
while($row = $result->fetch_array()){
  $sqlup = 'UPDATE articulosw SET
            aw_tienda = "3"
            WHERE aw_id = "'.$row['ap_articulo'].'"';
  setq($sqlup);
}

?>