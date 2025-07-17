<?php
include_once('../funciones.php');
session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
//ini_set('display_errors', 1);

$forecast = $_GET['id'];
$sql = 'SELECT * FROM pr_forecast WHERE prf_id = "'.$forecast.'"';
$result = setq($sql);
$row = $result->fetch_array();

echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">Añadir artículos a los checkpoints del forecast '.$row['prf_nmb'].' </div>';
    echo '<div class="col-12">
      <div class="col-3">
        <label>Selecciona el checkpoint a modificar: </label>
        <select id="checkpoint" class="form-control mx-auto" style="margin-left: auto; margin-right: 0;" required>';
          $sql = 'SELECT * FROM pr_checkpoints WHERE pc_forecast = "'.$forecast.'" AND pc_estatus = "F" AND pc_ffin > "'.date('Y-m-d').'"';
          $result = setq($sql);
          while($row = $result -> fetch_array()){
            echo '<option value="'.$row['pc_id'].'"> Checkpoint '.$row['pc_id'].'</option>';
          }
        echo '</select>
      </div>
    </div>';
    echo '<table class="table table-hover" id="myTable2">
    <thead class="thead bg-primary text-white">';
      echo '<tr>
        <th width="40%">Artículo</th>
        <th width="20%">Modelo</th>
        <th width="20%">Categoría</th>
        <th width="20%">Cantidad</th>
      </tr>
    </thead>';
  $categoria = busca('1', 'categorias', 'cat_inflable', 'cat_id'); 
  $sql = 'SELECT * FROM articulos INNER JOIN categorias ON cat_id = a_categoria WHERE a_tipoprod != "M" AND a_estatus = "A" AND a_categoria = "'.$categoria.'" ORDER BY a_nmb ASC';
  $result = setq($sql);
  while($row = $result->fetch_array()){
    $var = busca($row['a_id'], 'articulos_variantes', 'av_articulo', 'COUNT(*)');
    if($var){
      $sqlvar = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$row['a_id'].'"';
      $resultvar = setq($sqlvar);
      while($rowvar = $resultvar -> fetch_array()){
        $articulo = $row['a_nmb'].' '.$rowvar['av_nmb'];
        $modelo = $rowvar['av_modelo']; 

        ?>
        <tr>
        <td><?php echo$articulo;?> </td>
        <td><?php echo$modelo;?> </td>
        <td><?php echo$row['cat_nmb'];?> </td>
        <td><input onfocus="this.select();" type="number" id="cant<?php echo $rowvar['av_cb'];?>" class="form-control" min="0" value="0" onchange="changecant2('<?php echo $rowvar['av_cb'];?>', <?php echo $row['a_id'];?>, '<?php echo $rowvar['av_modelo'];?>')">  </td>
        </tr>
        <?php
      }
    } else {
      ?>
      <tr>
      <td><?php echo $row['a_nmb'];?> </td>
      <td></td>
      <td><?php echo $row['cat_nmb'];?> </td>
      <td><input onfocus="this.select();" type="number" id="cant<?php echo $row['a_cb']; ?>" class="form-control" min="0" value="0" onchange="changecant2('<?php echo $row['a_cb'];?>', <?php echo $row['a_id']; ?>, '')"> </td>
      </tr>
      <?php
    }
  }
echo '</table>';
      
    echo '</div>
    </div>';

?>

<script>
  $(document).ready(function () {
    var windowHeight = $(window).height();
    $("#myTable2").DataTable({
      paging: true,
      scrollY: windowHeight * 0.5,
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
      },
      responsivePriority: 1,
      lengthMenu: [[10, 25, 50, 100, -1], ["10", "25", "50", "100", "Todos"]],
      pageLength: -1,
    });
  });
  function changecant2(cb, id, modelo){
    var cant = document.getElementById('cant'+cb);
    var checkpoint = document.getElementById('checkpoint');
    
    $.ajax({
      url: "query/cantidadforecast.php",
      method: "POST",
      data: { "articulo": id,
              "cp": checkpoint.value,  
              "modelo": modelo,
              "cantidad": cant.value,
              "forecast": <?php echo $forecast; ?>
            },
    }).done(function(data){
      console.log(data);
      Swal.fire({
          icon: 'success',
          title: 'Añadido',
          text: 'El artículo se añadio exitosamente'
      }).then((result) => {
          if (result.isConfirmed || result.isDenied) {
              Swal.close();
          }
      });
      cant.value = 0;
    });       
  }

  function alertSweet(icono, titulo, mensaje) {
      Swal.fire({
          icon: icono,
          title: titulo,
          text: mensaje
      }).then((result) => {
          if (result.isConfirmed || result.isDenied) {
              Swal.close();
          }
      });
  }
</script>