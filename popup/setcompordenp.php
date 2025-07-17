<?php 
//ini_set('display_errors',1);
include_once('../funciones.php');
include_once('../modulos/ordenprod.php');
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado

$ordenp = new modelordenprod();
$orden = $_REQUEST['ordenprod'];
$ordenp -> select($orden);

$titulo = $ordenp -> nmbarticulo;
$articulo = $ordenp -> articulo;

    echo '<div class="row mt-5 col-11">
          <div class="col-md-12">
            <div class="card">
              <div class="alert alert-primary">
               Composición de '.$titulo.'
              </div>';

    $rtip = array('A' => 'Área', 'V' => 'Volumen', 'P' => 'Pieza');

    echo '<form action="?modulo=ordenprod&accion=insertartcomposicion&id='.$orden.'" method="post" onsubmit="checksubmit();" id="formulario">';
        echo '<div class="row">';
        echo '<input type="hidden" value="'.$orden.'" name="orden" id="orden">';
        echo '<div class="col-12 col-md-7 mt-2 ms-5" >
          <div class="form-group">
            <label for="agregar" class">Materia prima</label><br>
            <div class="input-group">
              <select id="mp" name="mp" class="form-control" onchange="getmp();" required> ';
                $sqlmp = 'SELECT * FROM pr_materiaprima WHERE pmp_estatus = "A"';
                $resultmp = setq($sqlmp);
                while($rowmp = $resultmp->fetch_array()){
                  echo '<option value="'.$rowmp['pmp_id'].'">'.$rowmp['pmp_nmb'].' - Material: '. $rowmp['pmp_material'].' - Unidad: '.$rtip[$rowmp['pmp_tipo']].'</option>';
                }
              echo '</select>
            </div>
          </div>
        </div>';
        echo '<div class="mb-5 col-12 col-md-2 mt-2">  
                <label>Color:</label>
                <input type="color" id="color" value="#000000" class="form-control" data-toggle="tooltip" data-placement="top" name="color" style="height: 90%;" disabled>
              </div>';
        echo '<div class="col-12 col-md-2 mt-2 row">
        <div class="form-group">
          <label><b>Cantidad</b></label>
          <input type="number" min="1" step="1" value="1" name="cantidad" class="form-control" placeholder="Cantidad de artículos ligados" required></input >
        </div>
      </div>
      <div class="mb-5 col-12 col-md-2 mt-2 ms-5" id="largodiv" hidden>  
          <label>Largo:</label>
          <input type="number" min="0.01" step="0.01" id="largo" class="form-control" data-toggle="tooltip" data-placement="top" name="largo" value="0">
        </div>
        <div class="mb-5 col-12 col-md-2 mt-2" id="anchodiv" hidden>  
          <label>Ancho:</label>
          <input type="number" min="0.01" step="0.01" id="ancho" class="form-control" data-toggle="tooltip" data-placement="top" name="ancho" value="0">
        </div>
        <div class="mb-5 col-12 col-md-2 mt-2" id="altodiv" hidden>  
          <label>Alto:</label>
          <input type="number" min="0.01" step="0.01" id="alto" class="form-control" data-toggle="tooltip" data-placement="top" name="alto" value="0">
        </div>
      </div>
      ';
      echo '
      <center>
        <div class="col-6 col-md-2 mt-2">
          <div class="form-group" >
            <button class="btn btn-primary" type="button" onclick="guardar('.$orden.');" id="agregar"><i class="fas fa-plus"></i>Agregar</button>
          </div>
        </div>
      <center>';
  $sqlsel = 'SELECT * FROM pr_ordenmateriaprima INNER JOIN pr_materiaprima ON pmp_id = pom_materiaprima WHERE pom_ordenprod = "'.$orden.'"';
  $result = setq($sqlsel);
  if($result->num_rows > 0){
    echo '<div class="col-12 mt-3">
      <table class="table table-responsive">
        <thead class="bg-primary text-white">
          <tr>
          <th width="60%">Composición</th>
          <th width="30%">Cantidad</th>
          <th width="10%"></th>
          </tr>
        </thead>
        <tbody>';
        
          while($row = $result -> fetch_array()){
            $txt = $row['pmp_nmb'].' Material: '.$row['pmp_material'];
            if($row['pmp_tipo'] == "A") $txt .= ' Medidas: '.$row['pom_largo'].' X '.$row['pom_ancho']; 
            if($row['pmp_tipo'] == "V") $txt .= ' Medidas: '.$row['pom_largo'].' X '.$row['pom_ancho'].' X '.$row['pom_alto']; 
            echo '<tr>
              <td>'.$txt.'</td>
              <td><input type="number" min="1" step="1" value="'.$row['pom_cantidad'].'" name="cantidad'.$row['pom_id'].'" id="cantidad'.$row['pom_id'].'" class="form-control" onchange="updcomp('.$row['pom_id'].', \'1\', '.$orden.')" required></input ></td>
              <td><button type="button" class="btn btn-sm btn-danger" id="borrar" name="borrar" onclick="updcomp('.$row['pom_id'].', \'2\','.$orden.')"><i class="fas fa-times-circle"></i></button></td>
            </tr>';
          }
        echo '</tbody>
      </table>
    </div>';
  }

?>

<script>
  function getmp(){
    
    var mp = document.getElementById('mp');
    var largo = document.getElementById('largodiv');
    var ancho = document.getElementById('anchodiv');
    var alto = document.getElementById('altodiv');
    var largoin = document.getElementById('largo');
    var anchoin = document.getElementById('ancho');
    var altoin = document.getElementById('alto');
    var color = document.getElementById('color');
    $.ajax({
      type: "POST",
      url: "query/checkmp.php",
      data: {'mp': mp.value},
      success: function(dataRTN) {
        //console.log(mp.value);
        //console.log(dataRTN);
        data = JSON.parse(dataRTN);
        color.value= data['color'];
        if(data['tipo'] == "A"){
          largoin.setAttribute('min', '0.01');
          anchoin.setAttribute('min', '0.01');
          altoin.removeAttribute('min');
          largo.hidden = false;
          ancho.hidden = false;
          alto.hidden = true;
        } else if(data['tipo'] == "V"){
          largo.hidden = false;
          ancho.hidden = false;
          alto.hidden = false;
          largoin.setAttribute('min', '0.01');
          anchoin.setAttribute('min', '0.01');
          altoin.setAttribute('min', '0.01');
        } else if(data['tipo'] == "P"){
          largo.hidden = true;
          ancho.hidden = true;
          alto.hidden = true;
          largoin.removeAttribute('min');
          anchoin.removeAttribute('min');
          altoin.removeAttribute('min');
        }
      } 
    });
    
  } 
  getmp();
  function guardar(idordenprod){
    var form = document.getElementById('formulario'); 
    var boton = document.getElementById('agregar');   
    var elements = form.elements;
    
    boton.disabled = true;

    for (var i = 0; i < elements.length; i++) {
      if (!elements[i].checkValidity()) {
        //console.log('elemento', elements[i]);
        alert("Error al capturar sus datos, verifique su información para continuar.");
        elements[i].focus();
        event.preventDefault(); 
        boton.disabled = false;
        return;
      }
    }
    var formData = $("#formulario").serialize();
    $.ajax({
    type: "POST",
    url: "query/setmporden.php",
    data: formData,
    success: function(dataRTN) {
      var target="popup/setcompordenp.php?ordenprod="+idordenprod;
      $.fancybox.close();
      $.fancybox.open({
        src: target,
        type: 'ajax',
        opts: {
            // Opciones de configuración actualizadas, si es necesario
            afterLoad: function(instance, current) {
              document.getElementById("banderacierre").value = "1";
              setTimeout(function () {
                    getmp();
                    banderacambios();
                }, 500);
            }
        }
    });
    }
    });
  }

  $(document).ready(function() {
      // Configuración de FancyBox
      $("[data-fancybox]").fancybox({
          // Opciones de configuración de FancyBox
      });

      // Evento que se dispara cuando se cierra FancyBox
      $(document).on("afterClose.fb", function() {
          // Muestra una alerta al cerrar FancyBox
          if(document.getElementById("banderacierre").value == "0"){
            location.reload();
          }
      });
  });

  function updcomp(id, accion, idordenprod){
    if(accion == "2"){
      var conf = confirm('¿Seguro que deseas eliminar esta composición?');
      var cantidad = 0;
    } else {
      var cantidad = document.getElementById('cantidad'+id).value;
      var conf = true;      
    }
    if(conf){
      $.ajax({
      type: "POST",
      url: "query/setmporden.php",
      data: {
        "idpom": id,
        "accion": accion,
        "cantidad": cantidad
      },
      success: function(dataRTN) {
        var target="popup/setcompordenp.php?ordenprod="+idordenprod;
        $.fancybox.close();
        $.fancybox.open({
          src: target,
          type: 'ajax',
          opts: {
            // Opciones de configuración actualizadas, si es necesario
            afterLoad: function(instance, current) {
              document.getElementById("banderacierre").value = "1";
              setTimeout(function () {
                    getmp();
                    banderacambios();
                }, 500);
            }
          }
      });
      }
      });
    }
  }
</script>