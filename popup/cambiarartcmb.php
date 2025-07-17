<?php
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
  include_once('../funciones.php');
  //ini_set('display_errors', 1);

  //foreachdie();
  $cotizacion = $_GET['cotizacion'];
  $cotizaciond = $_GET['cotizaciond'];
  $articulo = $_GET['articulo'];
  $nmb = busca($articulo, 'articulos', 'a_id', 'a_nmb');
  $cantidad = $_GET['cantidad'];

  ?>
  <script>
      $(document).ready(function() {
        $('#producto').on('keyup', function() {
          if (event.keyCode == 38 || event.keyCode == 40){
              //console.log('chi');
          }else{
            var key = $(this).val();
            var dataString = 'producto='+key;
            var exc = $('#excepcion').val();
            $.ajax({
              type: "POST",
              url: "../query/suggestproducts2.php",
              data: {
                "producto": key,
                'from': "articulos",
                'excepcion' : exc,
              },
              success: function(data) {
                console.log(data);
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function(){
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  return false;
                });
              }
            });
          }
        });
      });

      function cambiarprod(){
        var prod = document.getElementById('producto');
        var ant = document.getElementById('anterior');
        var exc = document.getElementById('excepcion'); 
        var btn = document.getElementById('guardarbtn'); 

        btn.setAttribute('disabled', true);

        if(prod.value == ""){
          alert('Ingrese el nombre del articulo para que se realice el cambio.');
          btn.removeAttribute('disabled');
        } else {
          $.ajax({
            type: "POST",
            url: "../query/cambiararticulo.php",
            data: {
              "cambio": prod.value,
              'ant': ant.value,
              'exc': exc.value,
              'cotizacion': <?php echo $cotizacion; ?>,
              'cotizaciond': <?php echo $cotizaciond; ?>,
              'cantidad': <?php echo $cantidad; ?>,
            },
            success: function(data) {
              console.log(data);
              if(data == "1"){
                alert('El artículo ingresado no existe');
                btn.removeAttribute('disabled');
              } else if(data == "2"){
                alert('No se puede cambiar por el mismo artículo');
                btn.removeAttribute('disabled');
              } else if(data == "3"){
                alert('No se puede cambiar por el artículo seleccionado');
                btn.removeAttribute('disabled');
              } else {
                $.ajax({
                  url: "../query/setcomentario.php",
                  type: "POST",
                  data: {'cotizacion': <?php echo $cotizacion; ?> ,
                        'cotizaciond': <?php echo $cotizaciond; ?>,
                },
                })
                .done(function(dataq){
                  //console.log(dataq);
                  location.reload();
                  //window.close();
                })
                

              }
            }
          })
        }
      }
    </script>
  <?php
  echo '<div class="col-10">
  <center>
    <div class="col-11 alert alert-primary">
      Busque el artículo que deseea cambiar
    </div>';
    
  echo '</center>
  <input type="hidden" id="excepcion" name="excepcion" value="'.$articulo.'">
  <input type="hidden" id="anterior" name="anterior" value="'.$articulo.'">
  
  <table class="table">
    <tbody>
      <tr> 
      <td style="width:45%; text-align:end;" ><h4 style="color:grey !important ">'.$nmb.'  </h4></td>
      <td style="width:10%"><button class="btn btn-lg"><i class="fas fa-exchange-alt" style="font-size: 50px; color:black;"></i></button></td>
      <td style="width:45%">
        <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus />
        <div id="suggestions"></div>
      </td>
      </tr>
    </tbody>
  </table>
  <div class="col-12">
    <center>
      <button class="btn btn-success" onclick="cambiarprod();" id="guardarbtn"><i class="fas fa-save"> </i> Guardar</button>
    </center>
  </div>';
  ?>
      <script>
        let listGroup = document.getElementById('suggestions');
        // Asignar evento al campo de texto
        document.querySelector('#producto').addEventListener('keydown', e => {
          if(!listGroup) {
            return; // No existe la lista
          }    
          // Obtener todos los elementos
          let items = listGroup.querySelectorAll('a');
          var a = document.getElementById("suggestions");
          // Saber si alguno está activo
          let actual = Array.from(items).findIndex(item => item.classList.contains('active'));
          //let actual = 0;
          // Analizar tecla pulsada
          if(e.keyCode == 13) {
            // Tecla Enter, evitar que se procese el formulario
            e.preventDefault();
            // ¿Hay un elemento activo?
            if(items[actual]) {
              // Hacer clic
              items[actual].click();
            }
          } 
          if(e.keyCode == 38 || e.keyCode == 40) {
            // Flecha arriba (restar) o abajo (sumar)
            if(items[actual]) {
              // Solo si hay un elemento activo, eliminar clase
              items[actual].classList.remove('active');
              //items[actual].className += " active";
            }
            // Calcular posición del siguiente
            if((e.keyCode == 38)){
              a.scrollTop -= ((items[actual].clientHeight - 1));
              actual += -1;
            }else{
              if(actual > 1) a.scrollTop += ((items[actual-2].offsetHeight + 1));            
              actual += 1;
            }
            //actual += (e.keyCode == 38) ? -1 : 1;
            // Asegurar que está dentro de los límites
            if(actual < 0) {
              actual = 0;
            } else if(actual >= items.length) {
              actual = items.length - 1;
            } 
            // Asignar clase activa
            items[actual].classList.add('active');
            items[actual].setAttribute('autofocus','');
            //items[actual].className += " active";
          }
        });
        // En la función donde generas la lista debes activar evento clic para cada elemento
        // Para este ejemplo se hace manual
        listGroup.querySelectorAll('a').forEach(a => {
          a.addEventListener('click', e => {
            // Asignar valor al campo
            document.querySelector('#producto').value = e.currentTarget.textContent;
            // Aquí deberías cerrar la lista y/o eliminar el contenido
          });
        });
      </script>
      <?php
?>