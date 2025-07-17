<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
$articulo = $_GET['articulo'];
$modelo = $_GET['modelo'];
$ordenp = $_GET['ordenp'];
// document.getElementById(\'guardar\').setAttribute(\'disabled\',\'disabled\');
echo '
          <div class="col-12 col-md-12 mb-2 card card-body">
          <form method="post"  action="index?modulo=ordenprod&accion=setmermas&id='.$ordenp.'" onsubmit="checksubmit();" id="formularioDos">
            <table width="100%" class="table table-stripped table-bordered table-hover" id="myTableG"> 
              <thead class="bg-primary text-white">
                <tr>
                  <th width="15%" class="p-2">Selección</th>
                  <th width="40%" class="p-2">Composición</th>
                  <th width="25%" class="p-2">Disponibles</th>
                  <th width="20%" class="p-2">Cantidad</th>
                </tr>
              </thead>
              <tbody>';
              $ordenesprod = '';
              if($articulo != 0){
                $sql = 'SELECT ac_mp AS materiaprima FROM articulos_composicion WHERE ac_articulo = "'.$articulo.'" AND ac_modelo = "'.$modelo.'" GROUP BY ac_mp ORDER BY ac_mp ASC';
                /* $sql = 'SELECT po_id FROM pr_ordenprod WHERE po_articulo = "'.$this->model->articulo.'" AND po_modelo = "'.$this->model->modelo.'"';  */     
              } else{    
                $sql = 'SELECT pom_materiaprima AS materiaprima FROM pr_ordenmateriaprima WHERE pom_ordenprod = "'.$ordenp.'" GROUP BY pom_materiaprima ORDER BY pom_materiaprima ASC';
                /* $sql = 'SELECT po_id FROM pr_ordenprod WHERE po_nmbarticulo = "'.$this->model->nmbarticulo.'"'; */
              }
              $result = setq($sql);
              if($result->num_rows){
                while($row = $result->fetch_array()){
                  //Contabilizamos la cantidad de usados de la materia prima
                  if($contador == 0){
                      $ordenesprod .= '"'.$row['materiaprima'].'"';
                  } else{
                      $ordenesprod .= ',"'.$row['materiaprima'].'"';
                  }
                  $contador++;
                }
              }
              $sqlmermas = 'SELECT SUM(am_cantidad) AS am_cantidad, am_mp, am_largo, am_ancho, am_alto, pmp_id, pmp_nmb, pmp_material, pmp_tipo, am_id, am_ordenp FROM articulos_mermas INNER JOIN pr_materiaprima ON pmp_id = am_mp WHERE am_mp IN ('.$ordenesprod.') AND am_estatus = "A" GROUP BY am_mp, am_largo, am_ancho, am_alto ORDER BY am_id';
              /* $sqlmermas = 'SELECT SUM(am_cantidad) AS am_cantidad, am_mp, am_largo, am_ancho, am_alto, pmp_id, pmp_nmb, pmp_material, pmp_tipo, am_id FROM articulos_mermas INNER JOIN pr_materiaprima ON pmp_id = am_mp WHERE am_ordenp IN ('.$ordenesprod.') AND am_estatus = "A" GROUP BY am_mp, am_largo, am_ancho, am_alto ORDER BY am_id'; */
              $result = setq($sqlmermas);
              $contador = 1;
              $dispg = 0;
              while($row = $result->fetch_array()){
                //En base a la op actual verificamos en articulos_mermas si existe el registro chequeado
                $existencia = $row['am_cantidad'];
                $usados = intval(busca($row['am_mp'], 'articulos_mermas_usados', 'amu_largo = "'.$row['am_largo'].'" AND amu_ancho = "'.$row['am_ancho'].'" AND amu_alto = "'.$row['am_alto'].'" AND amu_ammp', 'SUM(amu_cantidad)'));
                $disponibles = (intval($existencia) - intval($usados));

                $usados = intval(busca($row['am_mp'], 'articulos_mermas_usados', 'amu_ordenp = "'.$ordenp.'" AND amu_largo = "'.$row['am_largo'].'" AND amu_ancho = "'.$row['am_ancho'].'" AND amu_alto = "'.$row['am_alto'].'" AND amu_ammp', 'SUM(amu_cantidad)'));
                if($usados > 0){
                  $disponibles = (intval($existencia) - intval($usados));
                }

                $total = intval($disponibles) + intval($usados);                
                if($disponibles > 0 || $usados > 0){
                /* if(true){ */
                  $compo = "";
                  $cant = "";
                  $acciones = "";
                  $tipo = $row['pmp_tipo'];
                  $medidas = 'Medidas: ';
                  if($tipo == "A") $medidas .= decimal_format($row['am_largo']).' X '.decimal_format($row['am_ancho']);
                  else if($tipo == "V") $medidas .= decimal_format($row['am_ancho']).' X '.decimal_format($row['am_largo']).' X '.decimal_format($row['am_alto']);
                  else $medidas = '';
                  $mp = busca($row['am_mp'], 'pr_materiaprima', 'pmp_id', 'CONCAT(pmp_nmb," Material: ",pmp_material)');
                  $compo = $row['pmp_nmb'].' - Material: '. $row['pmp_material'].' - Unidad: '.$rtip[$row['pmp_tipo']]. $medidas . '<br>';
                  $chec = '';
                  if($usados > 0){
                    $chec = 'checked';
                    $propiedades = 'min="1" step="1" max="'.$total.'"';
                  } else{
                    $chec = '';                    
                    $propiedades = ' readonly';
                  }
                  if($total == 0){
                    $chec .= ' disabled';  
                  }

                  $cant = '<input type="number" '.$propiedades.' id="cantidadd'.$contador.'" name="cantidadd'.$contador.'" class="form-control" value="'.$usados.'">'."<br>"; 
                  echo '<tr>';
                  echo '<td>
                          <center><input class="form-check-input all-check" onclick="seleccionDos('.$contador.');" type="checkbox" name="check'.$contador.'" id="check'.$contador.'" value="" '.$chec.'></center>
                          <input hidden type="checkbox" name="back'.$contador.'" value="'.$row['largo'].','.$row['ancho'].','.$row['alto'].','.$row['mp'].'" checked>
                          <input type="hidden" name="ammp'.$contador.'" value="'.$row['am_mp'].'">
                          <input type="hidden" name="largo'.$contador.'" value="'.$row['am_largo'].'">
                          <input type="hidden" name="ancho'.$contador.'" value="'.$row['am_ancho'].'">
                          <input type="hidden" name="alto'.$contador.'" value="'.$row['am_alto'].'">
                        </td>';
                  echo '<td>'.$compo.'</td>';
                  echo '<td>
                  <span>'.$disponibles.'</span>
                  <span id="disponibles'.$contador.'" hidden>'.$total.'</span></td>';
                  echo '<td>'.$cant.'</td>';
                  echo '</tr>';     
                  $contador++;     
                  $dispg++;
                }
                }
                if($dispg == 0){
                  echo '<td colspan="4"><center>No hay elementos para mostar</center></td>';

                }
              echo '</tbody>
            </table>';
            echo '
            <div class="col-12 col-md-12">
              <center><button type="submit" id="xg" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button></center>
            </div>
            </form>
            ';
         echo '</div>
        ';
?>
        <script>
        function checksubmit2(){
          console.log("hola");
          document.getElementById("xg").setAttribute("disabled","disabled");
        }
        function seleccionDos(k) {
          var cantidad = document.getElementById("cantidadd" + k);
          var elemCheck = document.getElementById("check" + k);
          var disponibles = document.getElementById("disponibles" + k).textContent;
      
          if (elemCheck.checked) {
              console.log("Checked: "+cantidad.id);  
              cantidad.removeAttribute("readonly");
              cantidad.setAttribute("min", "1");
              cantidad.setAttribute("max", disponibles);
              cantidad.setAttribute("step", "1");
          } else {
              console.log("Unchecked: "+cantidad.id);  
              cantidad.readOnly = true;
              cantidad.removeAttribute("min");
              cantidad.removeAttribute("max");
              cantidad.removeAttribute("step");
          }
      }

      document.getElementById("formularioDos").addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault(); // Evitar que se envíe el formulario automáticamente
            document.getElementById('guardar').click(); // Simular clic en el botón "Guardar"
        }
      });


    /* function calcularCatidad(k){
      var cantidad = document.getElementById("cantidadd"+k).value;
      var existencia = document.getElementById("existencia"+k).value;
      var chec = document.getElementById("check"+k);
      var mensaje = '';

      if(chec.checked){
        if(cantidad > existencia){
          mensaje = "La cantidad ingresada no puede ser mayor que la existente."
          bandera = false;
        } else {
          bandera = true;
        }
      } else{
        bandera = true;
      }
    } */
      
        </script>