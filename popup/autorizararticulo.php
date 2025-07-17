<?php 
include_once('../funciones.php');
//ini_set('display_errors', 1);

$remision = $_GET['id'];
$folio = busca($remision, 'remisiones', 'r_id', 'r_folio');

?>
<div class="col-10">
  <div class="alert alert-primary">
    Autorizar el envío de los articulo de la remisión <?php echo $folio; ?>
  </div>
  <center>
    <table class="table">
      <thead class="bg-primary text-white">
        <th width= "50%">Artículo</th>
        <th width= "50%">Cantidad</th>
      </thead>
      <tbody>
        <?php
          $sql = 'SELECT * FROM remision_faltantes WHERE rf_remision = "'.$remision.'" AND rf_estatus != "F"';
          $result = setq($sql);
          while($row = $result -> fetch_array()){
            $nmb = busca($row['rf_articulo'], 'articulos', 'a_id', 'a_nmb');
            if($row['rf_modelo'] != "") $nmb .= ' '.busca($row['rf_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['rf_modelo'].'" AND av_articulo', 'av_nmb');
            
            ?>
              <tr>
              <td > <?php echo $nmb; ?> </td>
              <td> <?php echo number_format($row['rf_cantidad'], 2); ?> </td>
              </tr>
            <?php
          }
        ?>
      </tbody>
    </table>
    <button class="btn btn-success" onclick="confirmar( <?php echo $remision; ?>);"><i class="fas fa-check"></i> Confirmar</button>
  </center>
</div>

<script>

 function confirmar(id){
    var confirma = confirm("¿Confirmas que se envíen los artículos?");

    if(confirma == true){
      window.location.href="?modulo=autorizar&accion=update&id="+id;
    }
 } 
</script>
