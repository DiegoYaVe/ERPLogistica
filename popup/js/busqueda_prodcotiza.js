$(buscar_datos());

function buscar_datos(consulta,iddoc,tipo){
var iddoc = $("#iddoc").val();
  $.ajax({
    url: 'buscarprodcotiza.php',
    type: 'POST',
    dataType: 'html',
    data: {'consulta': consulta,'iddoc': iddoc},
  })
  .done(function(respuesta){
    $("#datos").html(respuesta);
  })
}

function setcondicion(condicion){
  var iddoc = $("#iddoc").val();
  var descripcion = $("#desc"+condicion).val();
  window.opener.newunidad.clavesat.value =  descripcion;
  window.close();
}


$(document).on('keyup','#nmb', function(){
 var iddoc = $("#iddoc").val();
 var valor = $(this).val();

 if(valor != ""){
   buscar_datos(valor,iddoc);
 }
 else{
   buscar_datos('',iddoc);
 }
});