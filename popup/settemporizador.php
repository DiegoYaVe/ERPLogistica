<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
include('../modulos/ordenprod.php');
?>
<div class="container">
    <div class="col-12 alert alert-primary" style="text-align: center;" id="tiempoRestante">Tiempo restante: 05:00</div>
    <!-- <form class="row" method="post" action="index?modulo=temporizadorapps&accion=insert"> -->
    <form class="row" method="post">
        <!-- <input type="hidden" name="id" id="id" value="<?php /* echo $_GET['id']; */ ?>"> -->
        <div class="mb-5 col-md-5">
            <label>Retroalimentación:</label>
            <textarea id="retro2" name="retro2" class="form-control focus"
                placeholder="Escribe una breve retroalimentación"></textarea>
        </div>
        <div class="mb-5 col-md-3">
            <label>Cantidad:</label>
            <input class="form-control" placeholder="Ingrese una cantidad" type="number" step="1" min="1"
                name="cantidad2" id="cantidad2">
        </div>
        <div class="mb-5 col-md-4">
            <label>Adjunto:</label>
            <input type="file" name="archivo" id="archivo" class="form-control">
        </div>  

        <?php
        if($_GET['tipo'] != "P"){
            $ordenprod = new ordenprod();
            $ordenprod->verarticulos();
        }
        ?>

        <div class="mb-5 col-md-12 mt-5">
            <center><button type="button" class="btn btn-primary" onclick="guardarCambios();"><i class="fa fa-save"></i>
                    Guardar</button></center>
        </div>
    </form>
</div>
<script>
<?php
        if($_GET['tipo'] != "P"){
            echo '
            function obtenerValoresComoCadena() {
                // Selecciona todos los elementos checkbox con la clase "check-all" que estén marcados
                var checkboxes = document.querySelectorAll(".check-all:checked");
                
                // Crea una cadena para almacenar los valores separados por comas
                var valoresCadena = "";
                
                // Recorre los elementos checkbox y agrega sus valores a la cadena
                checkboxes.forEach(function(checkbox, index) {
                  // Agrega una coma si no es el primer elemento
                  if (index > 0) {
                    valoresCadena += ",";
                  }
                  valoresCadena += checkbox.value;
                });
                
                // Devuelve la cadena de valores separados por comas
                return valoresCadena;
              }
            ';
        }
    ?>

// Crear un nuevo Web Worker desde una cadena de URL
var running = false;

// main.js
const worker = new Worker('js/timeWorkerTemp.js');
var tiempoRestante =document.getElementById("tiempoRestante");

var txtComplemento = '';
var txtTiempo = '';

worker.onmessage = function (event) {
const {minutes, seconds, run} = event.data;

    if(minutes < 10){
        txtComplemento = "0"+minutes;
    } else{
        txtComplemento = minutes;
    }

    txtTiempo = txtComplemento;

    if(seconds < 10){
        txtComplemento = "0"+seconds;
    } else{
        txtComplemento = seconds;
    }

    txtTiempo = txtTiempo +":"+ txtComplemento;

    tiempoRestante.textContent = "Tiempo restante: "+txtTiempo;
    running = run;
    if(minutes == 0 && seconds == 0){
        // Obtén una referencia al botón por su clase
        var closeButton = document.querySelector('.fancybox-button.fancybox-close-small');

        // Simula un clic en el botón
        closeButton.click();
    }
};

function guardarCambios() {
    if(validarCampos()){
    var retro4 = document.getElementById("retro2").value;
    var archivo = $("#archivo")[0].files[0]; // Ten en cuenta que si estás enviando un archivo, debes manejarlo de manera diferente
    var cantidad4 = document.getElementById("cantidad2").value;
    var tipo = "<?php echo $_GET['tipo']?>";
    <?php
        if($_GET['tipo'] != "P"){
            echo '
            // Llama a la función para obtener los valores como una cadena
            var articuloSel = obtenerValoresComoCadena();
            ';
        }
    ?>

    // Crea un objeto FormData para enviar los datos
    var formData = new FormData();
    formData.append("retro", retro4);
    formData.append("archivo", archivo);
    formData.append("cantidad", cantidad4);
    formData.append("tipo", tipo);
    <?php
        if($_GET['tipo'] != "P"){
            echo '
            formData.append("articuloSel", articuloSel);
            ';
        }
    ?>

    // Realiza la solicitud AJAX 
    $.ajax({
        type: "POST",
        url: "../query/datostemporizador.php",
        dataType: 'json',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Maneja la respuesta del servidor aquí
            var adj = response.adj;
            
            // Dispara un evento personalizado con los datos
            var datosEnviadosEvent = new CustomEvent('datosEnviados', {
                detail: {
                    adj3: adj,
                    retro3: retro4,
                    cantidad3: cantidad4,
                    tipo: tipo 
                    <?php
                    if($_GET['tipo'] != "P"){
                        echo ', articuloSel: articuloSel';
                    }
                    ?>
                }
            });
            window.dispatchEvent(datosEnviadosEvent);
            $.fancybox.close();
        }
    });
    }
}

// Función para contar checkboxes chequeados y no deshabilitados
function contarCheckboxes() {
    // Obtener todos los checkboxes con la clase "check-all"
    var checkboxes = document.querySelectorAll('.check-all');

    // Filtrar los checkboxes para obtener solo los chequeados y no deshabilitados
    var checkboxesChequeados = Array.from(checkboxes).filter(function(checkbox) {
        return checkbox.checked && !checkbox.disabled;
    });

    // Obtener la cantidad de checkboxes chequeados y no deshabilitados
    var cantidadCheckboxesChequeados = checkboxesChequeados.length;

    // Mostrar la cantidad en la consola (puedes hacer lo que desees con esta información)
    return cantidadCheckboxesChequeados;
}

function validarCampos() {
  var retroalimentacion = document.getElementById("retro2").value;
  var cantidad = document.getElementById("cantidad2").value;
  var texto = '';
  <?php
  if($_GET['tipo'] != "P"){
    ?>
    var checkboxes = contarCheckboxes();

    /* console.log("Checkboxes: "+checkboxes+" y cantidad: "+cantidad); */

    if(cantidad != checkboxes){
    Swal.fire({
        text: "La cantidad ingresada no coincide con los artículos seleccionados. Vefifique.",
        icon: "warning"
    });
    return false; // Los campos están vacíos, la validación falla
    }
    <?php
  }
  ?>

  if (retroalimentacion.trim() === "" || cantidad.trim() === "") {
    if(retroalimentacion.trim() === "" && cantidad.trim() === ""){
        texto = 'El campo "Cantidad" y "Retroalimentación" no pueden estar vacíos';
    } else{
        if(cantidad.trim() === ""){
            texto = 'El campo "Cantidad" no puede estar vacío';
        } else{
            texto = 'El campo "Retroalimentación" no puede estar vacío';
        }
    }
    Swal.fire({
        text: texto,
        icon: "warning"
    });
    return false; // Los campos están vacíos, la validación falla
  }

  // Si llegamos aquí, los campos no están vacíos
  return true; // La validación es exitosa
}

// Este código debe estar en el script de la ventana emergente (popup)
window.addEventListener('beforeunload', function (event) {
    worker.postMessage({ action: 'stop' });
});

/* $.fancybox({
afterClose: function() {
    location.reload(); // recarga la página después de cerrar el fancybox
    console.log("Se cierra");
 }
          }); */
// Escucha el evento afterClose de FancyBox
$(document).on('afterClose.fb', function () {
    $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/estatusproceso.php",
            data: {
                "estatus": 0,
                "all": 0,
                "proceso": "<?php echo $_GET['proceso'] ?>",
                "ordenp": "<?php echo $_GET['idp']; ?>"
            },
            success: function (data) {
              if(data == 0){
                alert("Otro usuario está interactuando con este proceso.");
                window.close();
                window.location.href = "operador.php"; 
              }
            }
          });
    worker.postMessage({ action: 'stop' });
});

worker.postMessage({ action: 'start' });


</script>