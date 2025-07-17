<?php
/* ini_set('display_errors', 1); */
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolut_ceo');
session_start();
include('../funciones.php');
$from = $_POST['from'];
$iddoc = $_REQUEST['iddoc'];
$remision = busca($iddoc,'remisiones','r_id','r_almacen');

function llenarTabla($query, $from, $iddoc, $remision, $av){
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
      $salida .= '<input type="hidden" id="from" value="'.$from.'">';
      $i = 0;
      while($row = $resultado->fetch_assoc()){
        if($av == 2){
          $concat = $i;
          $sqladd = ' AND ap_modelo = "'.$row['av_modelo'].'"';
        } else {
          $sqladd = '';
        }

        if($from == "remisiones"){
          /* $sqlap = 'SELECT ap_costo,ap_precio,a_nmb FROM remisiones
                  INNER JOIN crm_clientes ON c_id = r_cliente
                  INNER JOIN articulos_precios ON ap_esquema = c_precio
                  INNER JOIN articulos ON ap_articulo = a_id
                  WHERE ap_activo = "1" AND r_id = "'.$iddoc.'"
                  AND ap_articulo = "'.$row[$id].'"'; */
          $sqlap = 'SELECT ap_costo, ap_precio FROM articulos_precios WHERE ap_activo = "1" AND ap_articulo = "'.$row[$id].'" '.$sqladd.'';                  
          echo '<script>
            document.getElementById("etiqueta").innerHTML = "Costo";
          </script>';
          $a = 2;
        }elseif($from == "ordenesc" || $from == "recepciones"){
          $sqlap = 'SELECT ap_costo,ap_idproducto,a_nmb FROM ordenesc
                    INNER JOIN articulo_proveedor ON ap_proveedor = o_proveedor
                    INNER JOIN articulos ON ap_articulo = a_id
                    WHERE ap_estatus = "1" AND  ap_activo = "1" AND o_id = "'.$iddoc.'"
                    AND ap_articulo = "'.$row[$id].'" ';
          echo '<script>
            document.getElementById("etiqueta").innerHTML = "Costo";
          </script>';
          $a = 1;
        }elseif($from == "articulos"){
          $sqlap = 'SELECT ap_costo,ap_precio,a_nmb FROM articulos INNER JOIN
                    articulos_precios ON ap_articulo = "'.$row[$id].'" 
                    WHERE a_estatus = "A" AND ap_activo = "1"';
          echo '<script>
            document.getElementById("etiqueta").innerHTML = "Costo";
          </script>';
        }
        $resultap = setq($sqlap);
        list($costo,$precio,$nmbprod) = $resultap->fetch_array();
        if($a == 1){


          $dolar = busca($row[$id],'articulo_proveedor','ap_estatus = "1" AND ap_activo = "1" AND ap_articulo','ap_usd');
          $iva  = busca($row[$id],'articulo_proveedor','ap_estatus = "1" AND ap_activo = "1" AND ap_articulo','ap_iva');
          $costo = busca($row[$id],'articulo_proveedor','ap_estatus = "1" AND ap_activo = "1" AND ap_articulo','ap_costo');
        
          
          if($dolar == "1"){
            $vusd = busca(1,'empresas','e_id','e_valorusd');
            $costo = $costo * $vusd;
            if($iva == "1"){
              $viva = busca('1','configuracionesp','c_id','c_iva')/100;
              $costo = $costo*(1+$viva);
            }
          }else{
            if($iva == "1"){
              $viva = busca('1','configuracionesp','c_id','c_iva')/100;
              $costo = $costo*(1+$viva);
            }
          }

          //$costo = busca($row['a_id'],'articulo_proveedor','ap_estatus = "1" AND ap_activo = "1" AND ap_articulo','ap_costo');

          if(!$costo) $costo = 0.00;

          //if(!$lineaneg) $lineaneg = busca($row['a_id'],'articulos','a_id','a_lineaneg');
        }else if($a == 2) $costo = $precio;
        if($av == 2){
          $artnmb = $row['a_nmb']." ".$row[$nmba];
        } else{
          $artnmb = $row[$nmba];
        }
        echo '<input type="hidden" id="name'.$row[$id].$concat.'" value="'.$artnmb.'">';
        echo '<input type="hidden" id="cb'.$row[$id].$concat.'" value="'.$row[$acb].'">';
        echo '<input type="hidden" id="costo'.$row[$id].$concat.'" value="'.number_format($costo,2,'.','').'">';
        echo '<input type="hidden" id="precio'.$row[$id].$concat.'" value="'.$precio.'">';
        //echo '<input type="hidden" id="lineaneg'.$row['a_id'].'" value="'.$lineaneg.'">';
        
        $existencia = existencia($row[$id],$remision);
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

$salida = "";
//$query = 'SELECT * FROM cfdi_unidades ORDER BY cu_nmb ASC';
    $salida = '<div class="table table-responsive table-hover">
                <table class="table table-striped table-bordered table-hover" width="100%">
                <thead class="thead bg-primary text-white h4">
                <tr>
                  <th width="10%" class="pb-1">Seleccionar</th>
                  <th width="15%" class="pb-1">CB</th>
                  <th width="15%" class="pb-1">Módelo</th>
                  <th width="50%" class="pb-1">Nombre</th>
                  <th width="50%" class="pb-1"><label id="etiqueta">Existencia<label></th>
                  <th width="10%" class="pb-1"></th>
                </tr>
               </thead>
               <tbody>';

  if(isset($_POST['consulta']) && !empty($_POST['consulta'])){

    $q = mb_strtoupper(trim($_POST['consulta']));
    if(strlen($q) > 2)
      /* $query = 'SELECT * FROM articulos WHERE (a_nmb LIKE "%'.$_POST['consulta'].'%"
                OR a_cb LIKE "%'.$_POST['consulta'].'%")
                ORDER BY a_nmb ASC LIMIT 0,30'; */

      $query = 'SELECT * FROM articulos WHERE (a_nmb LIKE "%'.$_POST['consulta'].'%" 
              OR a_cb LIKE "%'.$_POST['consulta'].'%") AND a_estatus = "A" AND 
                    (SELECT COUNT(*) FROM articulos_variantes WHERE av_articulo = a_id) < 1 
              ORDER BY a_nmb ASC LIMIT 0,30';

/*       $query2 = 'SELECT * FROM articulos_variantes INNER JOIN articulos ON a_id = av_articulo
                WHERE (av_nmb LIKE "%'.$_POST['consulta'].'%" 
                OR av_cb LIKE "%'.$_POST['consulta'].'%" 
                OR av_descripcion LIKE "%'.$_POST['consulta'].'%") 
                AND a_estatus = "A"'; */

      $query2 = 'SELECT * FROM articulos_variantes INNER JOIN articulos ON a_id = av_articulo
                WHERE (av_nmb LIKE "%'.strip_tags($key).'%"
                OR av_cb LIKE "%'.strip_tags($key).'%" 
                OR av_descripcion LIKE "%'.strip_tags($key).'%" 
                OR a_nmb LIKE "%'.strip_tags($key).'%" 
                OR a_modelo LIKE "%'.strip_tags($key).'%" 
                OR a_cb LIKE "%'.strip_tags($key).'%") 
                AND a_estatus = "A"';
      list($salida1, $sld1) = llenarTabla($query, $from, $iddoc, $remision, 1);
      list($salida2, $sld2) = llenarTabla($query2, $from, $iddoc, $remision, 2);
      if($sld1 == 0 && $sld2 == 0){
        $salida = '<tr><td colspan="3"><center>NO hay coincidencias con la busqueda</center></td></tr>';
      } else{
        $salida .= $salida1;
        $salida .= $salida2;
      }
  }
  else $salida.="<tr><td colspan='3'><center>Introduzca un valor para busqueda</center></td></tr>";
  $salida.='</tbody></table>';
  ?>
  <script>
  document.onkeydown = checkKey;
  function checkKey(e) {
      e = e || window.event;
      if (e.keyCode == '38') { //arrow up
        var event = document.createEvent("HTMLEvents");
        var evtName = (typeof(type) === "string") ? "key" + type : "keydown";
        event.initEvent(evtName, true, false);
        event.keyCode = "9";

        document.dispatchEvent(event);
//            simulateKey("9","press");
      }
      else if (e.keyCode == '40') {  //arrow down
        window.dispatchEvent(new KeyboardEvent('keydown', {
          key: "Tab",
          keyCode: 9,
          code: "Tab",
          which: 9,
          shiftKey: false,
          ctrlKey: false,
          metaKey: false
        }));
                      //alert("sioino");

      }
  }
  </script>
<?php

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
  var from = document.getElementById("from").value;
  var nameprod = document.getElementById("name" + prod).value;
  var cbprod = document.getElementById("cb" + prod).value;
  var costo = document.getElementById("costo" + prod).value;
  var precio = document.getElementById("precio" + prod).value;
  /* var lineaneg = document.getElementById("lineaneg" + prod).value;
  var linean = opener.document.getElementById("linean"); */
  opener.document.getElementById("producto").value = cbprod;
  opener.document.getElementById("importe").value = precio;
  console.log('precio: ', precio);
  if(opener.document.getElementById("costo")) opener.document.getElementById("costo").value = costo;
  /* linean.value = lineaneg; */
  opener.document.getElementById("cantidad").focus();
  opener.document.getElementById("cantidad").select();

  /*
  if(from !== "articulos"){
      if(lineaneg !== "" && lineaneg !== null){
      linean.setAttribute("readonly", "readonly");
      linean.removeAttribute("style");
      if(opener.document.getElementById("toolln")) opener.document.getElementById("toolln").setAttribute("hidden","true");
    }else{
      linean.removeAttribute("readonly");
      linean.value = "";
      linean.setAttribute("style","border: solid 3px red");
      opener.document.getElementById("toolln").removeAttribute("hidden");
    }
  } */
  window.close();
}
</script>