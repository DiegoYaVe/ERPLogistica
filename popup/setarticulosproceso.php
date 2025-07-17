<?php
ini_set('display_errors', 0);
include_once('../funciones.php');
session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado

$procesoactual = $_POST['procesoactual']; //ID del proceso acual

$sql = 'SELECT * FROM pr_articulosop WHERE pra_op = "'.$ordenp.'" AND pra_pa = "'.$procesoactual.'" AND pra_estpa = "A"';
$result = setq($sql);

$nregistros = $result->num_rows;
/* $artname = busca($this->model->articulo, "articulos", "a_id", "a_nmb");
$var = busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "' . $this->model->modelo . '" AND av_articulo', 'COUNT(*)');
if ($var > 0) {
    $artname .= " " . busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "' . $this->model->modelo . '" AND av_articulo', 'av_nmb');
}

if ($this->model->tipo == "L") {
    $artipo = "LISO";
} else if ($this->model->tipo == "I") {
    $artipo = "IMPRESO";
} else {
    $artipo = $this->model->obstipo;
}

$rolpe = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_rol');
$rol = busca($rolpe, 'rolesprod', 'rp_id', 'rp_nmb');
$encargado = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_nmb') . " - " . $rol;

$cortador = busca($this->model->cortador, 'pr_empleados', 'pe_id', 'pe_nmb');

if ($this->model->estatus == "N") {
    $estatus = "Nuevo";
} else if ($this->model->estatus == "P") {
    $estatus = "En proceso";
} else {
    $estatus = "Finalizado";
} */

echo '
      <style>
      table, th, td {
        border: 1px solid #ddd; /* Borde gris tenue */
      }

      .text-end{
        text-align:right!important
      }

      .text-bold-400 {
        font-weight : 400;
      }

      .align-self-center{
        -ms-flex-item-align:center!important;
        align-self:center!important
      }

      .justify-content-between{
        -webkit-box-pack:justify!important;
        -ms-flex-pack:justify!important;
        justify-content:space-between!important
      }

      .px-md-1{
        padding-right:.25rem!important;
        padding-left:.25rem!important
      }

      .castle-success{
        color: #50cd89;
      }

      .castle-danger{
        color: #f1416c;
      }

      .castle-warning{
        color: #ffc700;
      }

      .castle-primary{
        color: #41b1f1;
      }
      </style>
      ';


echo '
      <div class="row mt-8">
        <div class="col-12 col-md-2">
        <span style="font-weight: bold;">Seleccionar todos: <input class="form-check-input" type="radio" onchange="selectAll(1);" id="checkAll" name="selall"></span>
        </div>
        <!-- 
        <div class="col-12 col-md-2">
        <span style="font-weight: bold;">Deseleccionar todos: <input class="form-check-input" type="radio" onchange="selectAll(0);" id="descheckAll" name="selall"></span>
        </div>
        -->
      </div>
      ';

//INICIA TABLA DE TODOS LOS ARTÍCULOS QUE HAY DE LA ORDEN DE PRODUCCIÓN
$anchocol = 10;
echo '
      <div class="row mt-2">
        <div class="col-12 col-md-12">
          <center>
          <table class="col-12 col-md-12 table-responsive" style="background: white;">
            <thead>
              <th class="thead-active bg-primary text-white" colspan="' . $anchocol . '"><center>Listado de artículos</center></th>
            </thead>
            <tbody>
            
            <tr>';


if ($nregistros == 0) {
    echo '
              <td colspan="' . $anchocol . '"><center>No hay artículos para mostrar</center></td>
              ';
} else {
    $i = 0;
    while ($row = $this->model->result->fetch_array()) {
        if ($i % $anchocol == 0) {
            echo '
                  </tr>
                  <tr>';
        }
        /* echo '
      <td><center><button class="btn btn-sm btn-secondary col-12 col-md-12">RED DE PROTECCIÓN TRAMPOLIN NARANJA '.($i +1).'</button></center></td>
      '; */
        if (!empty($row['pra_estpa']) && !empty($row['pra_estsp'])) {
            if ($row['pra_estpa'] == "F" && $row['pra_estsp'] == "N") {
                $clase = 'castle-danger'; //Artículo entre fases
                $txticono = 'Artículo entre fases';
            } else if ($row['pra_estpa'] == "F" && $row['pra_estsp'] == "F") {
                $clase = 'castle-success'; //Artículo finalizado
                $txticono = 'Artículo finalizado';
            } else if($row['pra_estpa'] == "A"){
                $clase = 'castle-warning'; //En ejecución
                $txticono = 'Artículo en ejecución';
            } else if($row['pra_estpa'] == "N"){
              $clase = '';
              $txticono = 'Artículo listo para iniciar el primer proceso';
            }
        } else {
            $clase = '';
            $txticono = 'Artículo listo para iniciar el primer proceso';
        }

        echo '<td><center><i class="fab fa-fort-awesome fs-1 fa-2x ' . $clase . '"" onclick="pressIcono(' . $row['pra_id'] . ');" id="icono' . $row['pra_id'] . '"></i>
              <input class="form-check-input check-all" type="checkbox" id="check' . $row['pra_id'] . '" name="boletos[]" value=""></center></td>
              <input type="hidden" id="class'.$row['pra_id'].'">';
        $i++;
    }
}
echo '
            </tr>
            </tbody>
          </table>
    </center>
        <div>
      </div>';
//FINALIZA TABLA DE TODOS LOS ARTÍCULOS QUE HAY DE LA ORDEN DE PRODUCCIÓN

echo '
      <script>

      function selectAll(k){
        var response = false;
        if(k == 1){
          response = true;
        }
        var elementosCheckAll = document.querySelectorAll(".check-all");

        // Recorre los elementos y marca cada uno como "checked"
        elementosCheckAll.forEach(function(elemento) {
          elemento.checked = response;
        });
      }

      function pressIcono(k){
        // Obtén el icono y el checkbox por su ID
        var checkbox = document.getElementById("check"+k);
          // Cambia el estado "checked" del checkbox
          checkbox.checked = !checkbox.checked;
      }
      </script>
      ';
?>