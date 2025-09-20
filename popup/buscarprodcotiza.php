<?php
  ini_set('display_errors', 0);
  //$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolut_ceo');
  include('../funciones.php');
  $iddoc = $_REQUEST['iddoc'];
  
  $cliente = busca(busca($iddoc,'crm_cotizaciones','cc_id','cc_tablero'),'crm_tableros','ct_id','ct_cliente');
  $salida = "";
  //$query = 'SELECT * FROM cfdi_unidades ORDER BY cu_nmb ASC';
  function llenarTabla($query, $av){
    $iddoc = $_REQUEST['iddoc'];
    $almacen = busca($iddoc,'crm_cotizaciones','cc_id','cc_almacen');
    if($av == 1){
      $id = 'a_id';
      $nmba = 'a_nmb';
      $acb = 'a_cb';
      $aestatus = 'a_estatus';
      $amodelo = 'a_modelo';
      $concat = "";
    } else{
      $id = 'av_articulo';
      $nmba = 'av_nmb';
      $acb = 'av_cb';
      $aestatus = 'av_estatus';
      $amodelo = 'av_modelo';
      $concat = 0;
    }
    
    $salida = "";
    if(isset($query)){
      $resultado = setq($query);
      $i = 0;
      if($resultado->num_rows > 0){
        $i = 0;
        while($row = $resultado->fetch_assoc()){
          if($av == 2){
            $concat = $i;
            $sqladd = ' AND ap_modelo = "'.$row['av_modelo'].'"';
            $artnmb = $row['a_nmb']." ".$row[$nmba];
          } else {
            $sqladd = '';
            $artnmb = $row[$nmba];
          }

          $sqlap = 'SELECT ap_costo, ap_precio FROM articulos_precios WHERE ap_activo = "1" AND ap_articulo = "'.$row[$id].'" '.$sqladd.'';
          /* $sqlap = 'SELECT ap_costo,ap_precio,a_nmb FROM crm_cotizaciones
                  INNER JOIN crm_tableros ON ct_id = cc_tablero
                  INNER JOIN crm_clientes ON c_id = ct_cliente
                  INNER JOIN articulos_precios ON ap_esquema = c_precio
                  INNER JOIN articulos ON ap_articulo = a_id
                  WHERE cc_id = "'.$iddoc.'" AND ap_activo = "1"
                  AND ap_articulo = "'.$row['a_id'].'" '.$sqladd.'';*/
          $resultap = setq($sqlap); 
          list($costo,$precio,$nmbprod) = $resultap->fetch_array();
          if(!$costo) $costo = 0;
          if(!$precio) $precio = 0;
          
          echo '<input type="hidden" id="name'.$row[$id].$concat.'" value="'.$row[$nmba].'">';
          echo '<input type="hidden" id="cb'.$row[$id].$concat.'" value="'.$row[$acb].'">';
          echo '<input type="hidden" id="costo'.$row[$id].$concat.'" value="'.number_format($costo,2,'.','').'">';
          echo '<input type="hidden" id="precio'.$row[$id].$concat.'" value="'.$precio.'">';
          //echo '<input type="hidden" id="lineaneg'.$row['a_id'].'" value="'.$lineaneg.'">';
          
          $existencia = existencia($row[$id],$almacen);
          if(!$existencia) $existencia = 0;
          
          elseif ($a == 2) $ln = $existencia;
          else $ln = '';
          if($row[$aestatus] == "I") $disp = 'disabled style="background:red"';
          else $disp = '';
          $salida.='<tr>
          <td class="align-middle">
            <button id="butt'.$row[$id].$concat.'" class="btn btn-primary" onclick="setprod('.$row[$id].$concat.');" '.$disp.'><i class="far fa-times-circle fa-lg"></i></button>
          </td>
          <td class="align-middle">'.$row[$acb].'</td>
          <td class="align-middle">'.$row[$amodelo].'</td>
          <td class="align-middle">'.$artnmb.'</td>
          <td class="align-middle">'.number_format($costo,2,'.','').'</td>
          <td class="number-align">';
          if($av == 2){
            $sqlim = 'SELECT ad_ruta
                    FROM articulos_descargas WHERE ad_modelo = "'.$row[$amodelo].'" AND ad_articulo = "'.$row[$id].'" ORDER BY ad_id ASC';
            $resultim = setq($sqlim);
            if($resultim->num_rows > 0){
              $resultim = setq($sqlim.' LIMIT 0,1');
              list($nmb) = $resultim->fetch_array();
              //$dirimg = 'images/img/productos/'.$_SESSION['emp'];
              $salida.='<img id="myimage" src="../img/'.$nmb.'" width="100px" height="100px" alt="Not Image">';
            }
            else $salida.='<img src="../img/noimage.png" alt="" style="width: 100px; height: 100px;" />';
          } else{
            $sqlim = 'SELECT i_nmb,i_ext
            FROM imagenes WHERE i_idproducto = "'.$row[$acb].'" ORDER BY i_idimg ASC';
            $resultim = setq($sqlim);
            if($resultim->num_rows > 0){
              $resultim = setq($sqlim.' LIMIT 0,1');
              list($nmb,$ext) = $resultim->fetch_array();
              //$dirimg = 'images/img/productos/'.$_SESSION['emp'];
              $salida.='<img id="myimage" src="../img/productos/'.$nmb.'.'.$ext.'" width="100px" height="100px" alt="Not Image">';
            }
            else $salida.='<img src="../img/noimage.png" alt="" style="width: 100px; height: 100px;" />';
          }
        

      $salida.='</td>
          </tr>';
          $i++;
        }
        $sld = 1;
      }
      else{
        /* $salida = '<tr><td colspan="3"><center>NO hay coincidencias con la busqueda</center></td></tr>'; */
        $sld = 0;
      }
    }
    return array($salida, $sld);
}


  $salida = '<div class="table table-responsive table-hover">
    <table class="table table-striped table-bordered table-hover" width="100%">
      <thead class="thead bg-primary text-white h4">
        <tr>
          <th width="10%" class="pb-1">Seleccionar</th>
          <th width="15%" class="pb-1">CB</th>
          <th width="15%" class="pb-1">Módelo</th>
          <th width="50%" class="pb-1">Nombre</th>
          <th width="50%" class="pb-1">Existencia</th>
          <th width="10%" class="pb-1"></th>
        </tr>
      </thead>
      <tbody>';
  if(isset($_POST['consulta']) && !empty($_POST['consulta'])){
    $q = mb_strtoupper(trim($_POST['consulta']));
    if(strlen($q) > 2)
      $query = 'SELECT * FROM articulos WHERE (a_nmb LIKE "%'.$_POST['consulta'].'%" 
              OR a_cb LIKE "%'.$_POST['consulta'].'%") AND a_estatus = "A" AND 
                    (SELECT COUNT(*) FROM articulos_variantes WHERE av_articulo = a_id) < 1 
              ORDER BY a_nmb ASC LIMIT 0,30';

      $query2 = 'SELECT * FROM articulos_variantes INNER JOIN articulos ON a_id = av_articulo
                WHERE (av_nmb LIKE "%'.$_POST['consulta'].'%" 
                OR av_cb LIKE "%'.$_POST['consulta'].'%" 
                OR av_descripcion LIKE "%'.$_POST['consulta'].'%") 
                AND a_estatus = "A"';
      list($salida1, $sld1) = llenarTabla($query, 1);
      list($salida2, $sld2) = llenarTabla($query2, 2);
      if($sld1 == 0 && $sld2 == 0){
        $salida = '<tr><td colspan="3"><center>NO hay coincidencias con la busqueda</center></td></tr>';
      } else{
        $salida .= $salida1;
        $salida .= $salida2;
      }
  }
  else $salida.="<tr><td colspan='3'><center>Introduzca un valor para busqueda</center></td></tr>";
  $salida.='</tbody></table>';
  
  echo $salida;
  //Finaliza tabla dinámica de registros
  ?>
  <script>
    function simulateKeyPress(character) {
      jQuery.event.trigger({
        type: 'keypress',
        which: character.charCodeAt(9)
      });
    }
    function setprod(prod){
      var nameprod = document.getElementById("name" + prod).value;
      var cbprod = document.getElementById("cb" + prod).value;
      var precioprod = document.getElementById("precio" + prod).value;
      opener.document.getElementById("producto").value = cbprod;
      opener.document.getElementById("cantidadorig").focus();
      opener.document.getElementById("cantidadorig").select();
      opener.document.getElementById("impunitario").value = precioprod;
      //opener.document.getElementById("submitfcotiza").click();
      window.close();
    }
    function setactive(){
    }
  </script>