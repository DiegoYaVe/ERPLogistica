<?php
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
  include_once("../funciones.php");
  ini_set('display_errors', 0);

  $remision = $_GET['remision'];
  $envio = array("O"=>"Ocurre","P"=>"Por definir","D"=>"A domicilio","C"=>"Recoge cliente");
  $remestatus = busca($remision, 'remisiones', 'r_id', 'r_estatus');
  echo '

  <div class="col-10">
  <form class="" action="?modulo=remisiones&accion=entregaparcial&remision='.$remision.'" method="POST" id="formulario">
    <div class="alert alert-primary col-12"> Selecciona los artículos que se van a embarcar </div>
      <table class="table ">
      <thead class="bg-primary text-white">
      <tr class="">
        <th class="bg-success number-align">ABONADO: </th>
        <th class="bg-success " colspan="4">$'.number_format(busca($remision,'cxcobrar','cx_referencia','cx_abonado'), 2).'</th>
      </tr>
      <tr>
        <th>Seleccionar</th>
        <th>Artículos</th>
        <th>Forma de envío</th>
        <th>Paquetería</th>
        <th>Sucursal</th>
      </tr>
      </thead>
      <tbody>
    ';


  $sql = 'SELECT * FROM remisionesc INNER JOIN articulos ON a_id = rc_articulo WHERE rc_remision = "'.$remision.'" AND rc_ligado is NULL AND rc_tipoenvio IN ("D", "O", "C") ';
  $result = setq($sql); 
  while($row = $result -> fetch_array()){
    $paqueteria = busca($row['rc_paqueteria'],'paqueterias','p_id','p_nmb');
    $sucursal = busca($row['rc_sucursal'],'paqueterias_sucursales','ps_id','ps_sucursal');
    $tipoenvio = $envio[$row['rc_tipoenvio']];
    $precio = busca($row['a_id'],'articulos_precios','ap_articulo','ap_precio');
    $rcid = $row['rc_id'];

    if($remestatus != "F"){
    if($row['rc_estatus'] == "N"){
      $checked = '';
      $disabled = '';
    }elseif($row['rc_estatus'] == "A"){
      $checked = 'checked';
      $disabled = '';
    }else{
      $checked = 'checked';
      $sqlrc = 'SELECT rc_tipoenvio, rc_estatus, rc_embarque FROM remisionesc WHERE rc_id = "'.$row['rc_id'].'"';
      $resultrc = setq($sqlrc);
      list($tenvio, $eenvio, $embarcado) = $resultrc->fetch_array();
      if($tenvio == "C"){
        if($eenvio == "P" && $embarcado == 0){
          $disabled = '';  
        } else{
          $disabled = 'disabled';
        }
      } else{
        $disabled = 'disabled';
      }
    }
    } else{
      $checked = 'checked';
      $disabled = 'disabled';
    }



    if($row['rc_tipoenvio'] == "C"){
      $paqueteria = "RECOGE CLIENTE";
    }
    
    $nmb = $row['a_nmb'];
    $numero = $row['rc_numero'];
    if($row['rc_modelo'] != ""){
      // Es variante;
      $sqlvar = 'SELECT * FROM articulos_variantes INNER JOIN articulos ON a_id = av_articulo WHERE av_modelo = "'.$row['rc_modelo'].'" ';
      $resultvar = setq($sqlvar);
      $row = $resultvar->fetch_array();
      
      $nmbcompleto = $nmb.' '.$row['av_nmb'].' '.$numero;
    } else{
      $nmbcompleto = $nmb.' '.$row['av_nmb'].' '.$numero;
    }



    echo '
    <tr>
      <td>
        <input class="form-check-input en-par" type="checkbox" onclick="verificarprecio('.$rcid.')" id="entregar'.$rcid.'" name="entregar'.$rcid.'" value="'.$rcid.'" '.$checked.' '.$disabled.'>
      </td>
      <td>'.$nmbcompleto.'</td>
      <td>'.$tipoenvio.'</td>
      <td>'.$paqueteria.'</td>
      <td>'.$sucursal.'</td>';
    echo '
    </tr>';

  }

  echo 
  '
  </tbody>
  </table>
    <div class="text-center">
      <button type="button" onclick="confirmarenvio('.$remision.');" class="btn btn-success"><i class="fa fa-save"></i> Enviar</button>
    </div>
  </form>
  <script class="">
  function confirmarenvio(id){
    Swal.fire({
      title: "¿Estás seguro de aplicar el envío parcial?",
      showDenyButton: false,
      showCancelButton: true,
      confirmButtonText: "Enviar",
      denyButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById("formulario").submit();
      } else if (result.isDenied) {
      }
    })
  }

  function verificarprecio(k){
    var elemento = document.getElementById("entregar"+k);

    const checkboxes = document.querySelectorAll(".en-par:not([disabled])");
    let checkedCheckboxesCount = 0;
    const checkedCheckboxesValues = [];
  
    checkboxes.forEach(function(checkbox) {
      if (checkbox.checked) {
        checkedCheckboxesCount++;
        checkedCheckboxesValues.push(checkbox.value);
      }
    });    

    var nreg = checkedCheckboxesCount;
    var nval = checkedCheckboxesValues.join(",");
    console.log("nreg: "+nreg);
    console.log("nval: "+nval);
  

    if(elemento.checked){
      $.ajax({
        method: "POST",
        url: "query/consultarprecio.php",
        data: {
          "id": k,
          "nreg": nreg,
          "nval": nval
        },
      }).success(function(data){
        if(data == 1){
          //No se puede marcar como envio parcial
          elemento.checked = false;
          //alert("No se encontró la remisión correspondiente a la entrega parcial.");
          Swal.fire(
            "Atención",
            "No se encontró la remisión correspondiente a la entrega parcial.",
            "error"
          )
        } else if(data == 3){
          elemento.checked = false;
          //alert("El importe abonado es menor que el requerido para hacer el envío parcial");
          Swal.fire(
            "Atención",
            "El importe abonado es menor que el requerido para hacer el envío parcial",
            "error"
          )
        }
      });
    }
  }
  </script>
  </div>';
?>
