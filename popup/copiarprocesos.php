<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$articulo = $_GET['articulo'];
$nmb = busca($articulo, 'articulos', 'a_id', 'a_nmb');


echo '
    <div class="container col-8">  
      <div class="col-12 alert alert-primary">Copiar proceso de produccion para '.$nmb.'</div>
        <table class="table " id="myTable">
          <thead>
            <tr>
              <th>Artículo</th>
              <th>Cantidad de procesos</th>
              <th>Copiar</th>
            </tr>
          </thead>
          <tbody>';
            $sql ='SELECT apr_articulo, COUNT(*) AS cantidad FROM articulos_produccion WHERE apr_articulo != "'.$articulo.'" GROUP BY apr_articulo';
            $result = setq($sql);
            while($row = $result -> fetch_array()){
              $nmbart = busca($row['apr_articulo'], 'articulos', 'a_id', 'a_nmb');
              echo '<tr>
                <td>'.$nmbart.' </td>
                <td>'.$row['cantidad'].' </td>
                <td><button class="btn btn-primary" onclick="verificar('.$row['apr_articulo'].');"><i class="far fa-copy" ></i>Copiar</button></td>
              </tr>';
            }
          echo '</tbody>
        </table>
      </div>
    </div>';

?>

<script>
  function verificar(art){
    var conf = confirm("¿Desea copiar los procesos del artículo seleccionado?")
    if(conf){
      window.location.href="?modulo=articulos&accion=copiarproduccion&id=<?php echo $articulo; ?>&articulo="+art;
    }
  }

  $(document).ready(function () {
    var windowHeight = $(window).height();
    $("#myTable").DataTable( {
      paging: true,
      processing: true,
      serverside: true,
      scrollY: windowHeight * 0.5,
      language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
      },
      pageLength: "50",
      responsivePriority: 1,
    });
  });

</script>