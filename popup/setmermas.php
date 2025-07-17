<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
$articulo = $_GET['articulo'];
$modelo = $_GET['modelo'];
$ordenp = $_GET['ordenp'];

echo '
          <div class="col-12 col-md-12 mb-2 card card-body">
          <form action="?modulo=ordenprod&accion=insertmerma&id=' . $ordenp . '" method="post" onsubmit="checksubmit();" id="formularioDos">
            <table width="100%" class="table table-stripped table-bordered table-hover" id="myTableG"> 
              <thead class="bg-primary text-white">
                <tr>
                  <th width="10%" class="p-2">Selección</th>
                  <th width="54%" class="p-2">Composición</th>
                  <th width="18%" class="p-2">Disponibles</th>
                  <th width="18%" class="p-2">Cantidad</th>
                </tr>
              </thead>
              <tbody>';
              $cantidadp = intval(busca($ordenp, 'pr_ordenprod', 'po_id', 'po_cantidad'));
              $rtip = array('A' => 'Área', 'V' => 'Volumen', 'P' => 'Pieza');
              if($articulo != 0){
                $sql = 'SELECT pmp_id, pmp_nmb, pmp_material, pmp_tipo, ac_id AS id, ac_mp AS mp, ac_largo AS largo, ac_ancho AS ancho, ac_alto AS alto, SUM(ac_cantidad) AS cantidad FROM articulos_composicion INNER JOIN pr_materiaprima ON pmp_id = ac_mp WHERE ac_articulo = "'.$articulo.'" AND ac_modelo = "'.$modelo.'" GROUP BY ac_mp, ac_largo, ac_ancho, ac_alto ORDER BY ac_id;';
              } else{
                $sql = 'SELECT pmp_id, pmp_nmb, pmp_material, pmp_tipo, pom_id AS id, pom_materiaprima AS mp, pom_largo AS largo, pom_ancho AS ancho, pom_alto AS alto, SUM(pom_cantidad) AS cantidad FROM pr_ordenmateriaprima INNER JOIN pr_materiaprima ON pmp_id = pom_materiaprima WHERE pom_ordenprod = "'.$ordenp.'" GROUP BY pom_materiaprima, pom_largo, pom_ancho, pom_alto ORDER BY pom_id;';
              }
              $result = setq($sql);
              $contador = 1;
              while($row = $result->fetch_array()){
                //En base a la op actual verificamos en articulos_mermas si existe el registro chequeado
                $cantidad = busca($ordenp, 'articulos_mermas', 'am_mp = "'.$row['mp'].'" AND am_largo = "'.$row['largo'].'" AND am_ancho = "'.$row['ancho'].'" AND am_alto = "'.$row['alto'].'" AND am_ordenp', 'am_cantidad');
                $max = (intval($row['cantidad']) * $cantidadp);
                if(!empty($cantidad)){
                  $check = 'checked';
                  $propiedades = ' min="1" step="1" max="'.$max.'"';
                } else{
                  $check = '';
                  $cantidad = 0;
                  $propiedades = ' readonly';
                }
                $compo = "";
                $cant = "";
                $acciones = "";
                $tipo = $row['pmp_tipo'];
                $medidas = ' - Medidas: ';
                if($tipo == "A") $medidas .= decimal_format($row['largo']).' X '.decimal_format($row['ancho']);
                else if($tipo == "V") $medidas .= decimal_format($row['ancho']).' X '.decimal_format($row['largo']).' X '.decimal_format($row['alto']);
                else $medidas = '';
                $mp = busca($row['mp'], 'pr_materiaprima', 'pmp_id', 'CONCAT(pmp_nmb," Material: ",pmp_material)');
                $compo = $row['pmp_nmb'].' - Material: '. $row['pmp_material'].' - Unidad: '.$rtip[$row['pmp_tipo']] . "" . $medidas . '<br>';
                    
                $acciones = '<button class="btn btn-sm btn-danger" onclick="delcomp('.$row['id'].');"><i class="fas fa-times-circle" style="color:#fff"></i>Borrar</button>';

                $cant = '<input type="number" '.$propiedades.' value="'.$cantidad.'" id="cantidadd'.$contador.'" name="cantidadd'.$contador.'" class="form-control" placeholder="Cantidad de artículos ligados">'."<br>"; 
                $cant .= '<input type="hidden" value="'.$cantidad.'" name="cantidadoriginal'.$contador.'" >'; 
                
                $cant .= '<input type="hidden" value="'.$max.'" id="maximo'.$contador.'" >'; 

                echo '<tr>';
                echo '<td><center>
                            <input class="form-check-input all-check" onclick="seleccionDos('.$contador.');" type="checkbox" name="check'.$contador.'" id="check'.$contador.'" value="'.$row['largo'].','.$row['ancho'].','.$row['alto'].','.$row['mp'].'" '.$check.'>
                            <input hidden type="checkbox" name="back'.$contador.'" value="'.$row['largo'].','.$row['ancho'].','.$row['alto'].','.$row['mp'].'" checked>
                          </center>
                      </td>';
                echo '<td>'.$compo.'</td>';
                echo '<td>'.$max.'</td>';
                echo '<td>'.$cant.'</td>';
                echo '</tr>';     
                $contador++;     
                }
              echo '</tbody>
            </table>';
            echo '
            <div class="col-12 col-md-12">
              <center><button type="submit" id="btnSave" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button></center>
            </div>
            </form>
            ';
         echo '</div>
        ';
?>
        <script>
        function seleccionDos(k) {
          var cantidad = document.getElementById("cantidadd" + k);
          var elemCheck = document.getElementById("check" + k);
          var maximo = document.getElementById("maximo" + k).value;
      
          if (elemCheck.checked) {
              console.log("Checked: "+cantidad.id);  
              cantidad.removeAttribute("readonly");
              cantidad.setAttribute("min", "1");
              cantidad.setAttribute("step", "1");
              cantidad.setAttribute("max", maximo);
          } else {
              console.log("Unchecked: "+cantidad.id);  
              cantidad.readOnly = true;
              cantidad.removeAttribute("min");
              cantidad.removeAttribute("step");
              cantidad.removeAttribute("max");
          }
      }

      document.getElementById("formularioDos").addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault(); // Evitar que se envíe el formulario automáticamente
            document.getElementById('btnSave').click(); // Simular clic en el botón "Guardar"
        }
      });
      
        </script>