$(buscar_datos());

function buscar_datos(consulta,empresa){
var empresa = $("#empresa").val();
var articulo = $("#articulo").val();
  $.ajax({
    url: 'lista_colores.php',
    type: 'POST',
    dataType: 'html',
    data: {'consulta': consulta,'empresa': empresa,'articulo': articulo},
  })
  .done(function(respuesta){
    $("#datos").html(respuesta);
  })
}

$(document).on('keyup','#nmb', function(){
 var cotizacion = $("#empresa").val();
 var valor = $(this).val();

 if(valor != ""){
   buscar_datos(valor,cotizacion);
 }
 else{
   buscar_datos('',cotizacion);
 }
});