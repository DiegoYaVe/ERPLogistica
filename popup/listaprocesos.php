<?php
include_once('../funciones.php');


$sql = 'SELECT * FROM pr_procesos WHERE ppr_estatus = "A" ORDER BY ppr_fase DESC, ppr_planta DESC';
$result = setq($sql);

$ordenp = $_GET['ordenprod']; 
if(isset($_GET['ordenprod'])) $accion = '?modulo=ordenprod&accion=insertproceso&id='.$ordenp;
else $accion = '?modulo=articulos&accion=insertproceso&articulo='.$_GET['articulo'];


echo '
<form action="'.$accion.'" class="" method="POST">
<div class="table-responsive">
  <table class="table table-bordered table-striped">
    <thead class="bg-primary text-white p-1">
      <tr class="">
        <th class="">SELECCIONAR</th>
        <th class="">PLANTA</th>
        <th class="">FASE</th>
        <th class="">PROCESO</th>
        <th class="">¿ES OBLIGATORIO?</th>
        <th class="">TIPO CRON/MANUAL</th>
        <th class="">ORDEN</th>
      </tr>
    </thead>
    <tbody class="">';
    while($row = $result->fetch_array()){
      if(isset($_GET['ordenprod'])) $existe = busca($ordenp, 'pr_procesosop', 'pp_proceso = "'.$row['ppr_id'].'" AND pp_ordenp', 'pp_id');
      else $existe = busca($_REQUEST['articulo'],'articulos_produccion','apr_proceso = "'.$row['ppr_id'].'" AND apr_articulo','apr_id');
      if($existe){
        if(isset($_REQUEST['ordenprod'])) $sql2 = 'SELECT pp_bloqueo AS obligatorio, pp_tipoproceso AS tipo, pp_orden AS orden FROM pr_procesosop WHERE pp_proceso = "'.$row['ppr_id'].'" AND pp_ordenp = "'.$ordenp.'"';
        else $sql2 = 'SELECT apr_obligatorio AS obligatorio, apr_tipo AS tipo, apr_orden AS orden FROM articulos_produccion WHERE apr_proceso = "'.$row['ppr_id'].'" AND apr_articulo = "'.$_REQUEST['articulo'].'"';
        $result2 = setq($sql2);
        $row2 = $result2->fetch_array();
        $checked = 'checked';
        $disableobli = '';
        if($row2['obligatorio'] == "1") $obligcheck = 'checked';
        else $obligcheck = '';

        $disabletipo = '';
        if($row2['tipo'] == "M") $tipocheck = 'checked';
        else $tipocheck = '';

        $disabledor = '';
        $orden = $row2['orden'];
        
      }else{
        $checked = '';

        $disableobli = 'disabled';
        $obligcheck = '';

        $disabletipo = 'disabled';
        $tipocheck = '';

        $disabledor = 'disabled';
        $orden = '';
      }
      echo '
      <tr class="">
        <td class="">
          <input name="activo['.$row['ppr_id'].']" id="activarono'.$row['ppr_id'].'" type="checkbox" class="flipswitch2" onchange="cambiarorden('.$row['ppr_id'].');" '.$checked.'>
        </td>
        <td class="">'.busca($row['ppr_planta'],'pr_plantas','pp_id','pp_nmb').'</td>
        <td class="">'.busca($row['ppr_fase'],'pr_fases','pf_id','pf_nmb').'</td>
        <td class="">'.$row['ppr_nmb'].'</td>
        <td class="">
          <input type="checkbox" class="flipswitch2" name="obligatorio'.$row['ppr_id'].'"  '.$obligatorioch.' id="obligatorio'.$row['ppr_id'].'" '.$disableobli.' '.$obligcheck.'>
        </td>
        <td class="">
          <input type="checkbox" name="tipo'.$row['ppr_id'].'" id="tipo'.$row['ppr_id'].'" class="flipswitchtipocron" '.$disabletipo.' '.$tipocheck.'>
        </td>
        <td class="">
          <input id="orden'.$row['ppr_id'].'" name="orden'.$row['ppr_id'].'" value="'.$orden.'" type="number" class="form-control ordenclass" '.$disabledor.'>
        </td>
      </tr>
      ';


    }
    echo '
    <tr class="">
      <td class="text-center" colspan="7">
        <button type="submit" class="btn btn-info"><i class="fa fa-save"></i> Guardar</button>
      </td>
    </tr>
    <script class="">
      function cambiarorden(id){
        if(document.getElementById("activarono"+id).checked){
          document.getElementById("obligatorio"+id).removeAttribute("disabled");
          document.getElementById("orden"+id).removeAttribute("disabled");
          document.getElementById("tipo"+id).removeAttribute("disabled");
          var todos = document.getElementsByClassName("ordenclass");
          var max = 0;
          var ant = 0;
          for(var x = 0; x < todos.length; x++){
            if(parseFloat(todos[x].value) > parseFloat(ant)){
              max = parseFloat(todos[x].value);
              max++;
              ant = parseFloat(todos[x].value);
            }
            
          }
          if(max == 0){
            max = 1;
          }

          document.getElementById("orden"+id).value = max;
        }else{
          document.getElementById("orden"+id).setAttribute("disabled","disabled");
          document.getElementById("obligatorio"+id).setAttribute("disabled","disabled");
          document.getElementById("tipo"+id).setAttribute("disabled","disabled");
          
          document.getElementById("orden"+id).value = "";
        }
        
      }
    </script>

    </tbody>
  </table>
</div>
</form>

';

?>