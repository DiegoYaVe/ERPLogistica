$(buscar_datos());

function buscar_datos(consulta,cotizacion){
var cotizacion = $("#cotiza").val();
  $.ajax({
    url: '../query/buscar.php',
    type: 'POST',
    dataType: 'html',
    data: {'consulta': consulta,'cotizacion': cotizacion},
  })
  .done(function(respuesta){
    $("#datos").html(respuesta);
  })
  .fail(function(){
    console.log("error");
  })
}

function setcondicion(condicion){
  var cotizacion = $("#cotiza").val();
//  var marca = document.getElementById("ch" + condicion).checked ;
  var marca = $("#ch"+condicion).prop('checked');
  var descripcion = $("#desc"+condicion).val();
  $.ajax({
    url: '../query/insertcondicion.php',
    type: 'POST',
    dataType: 'html',
    data: {'condicion': condicion,'marcado': marca,'cotizacion': cotizacion,'descripcion':descripcion},
  })
  .done(function(respuesta){
    $("#queryin").html(respuesta);
  })
  .fail(function(){
    console.log("Error: query");
  })
}


$(document).on('keyup','#nmb', function(){
 var cotizacion = $("#cotiza").val();
 var valor = $(this).val();

 if(valor != ""){
   buscar_datos(valor,cotizacion);
 }
 else{
   buscar_datos('',cotizacion);
 }
});