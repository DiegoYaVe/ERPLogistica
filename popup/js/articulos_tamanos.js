$(buscar_datos());

function buscar_datos(consulta,empresa,articulo){
  $.ajax({
    url: 'listatamanos.php',
    type: 'POST',
    dataType: 'html',
    data: {'consulta': consulta,'empresa': empresa,'articulo': articulo},
  })
  .done(function(respuesta){
    $("#datos").html(respuesta);
  })
}

function setcondicion(idt){
  alert(idt + "Hola");
}

$(document).on('keyup','#nmb', function(){
 var cotizacion = $("#empresa").val();
 var articulo = $("#articulo").val();
 var valor = $(this).val();

 if(valor != ""){
   buscar_datos(valor,cotizacion,articulo);
 }
 else{
   buscar_datos('',cotizacion,articulo);
 }
});