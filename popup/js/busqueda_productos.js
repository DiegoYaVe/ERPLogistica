$(buscar_datos());

function buscar_datos(consulta,iddoc,tipo,from){

var iddoc = $("#iddoc").val();
var from = $("#from").val();
var tipo = $("#tipodoc").val();
  $.ajax({
    url: 'buscarprods.php',
    type: 'POST',
    dataType: 'html',
    data: {'consulta': consulta,'iddoc': iddoc,'tipo': tipo,'from': from},
  })
  .done(function(respuesta){
    $("#datos").html(respuesta);
  })
}

function setcondicion(condicion){
  var iddoc = $("#iddoc").val();
  var tipo = $("#tipodoc").val();
  var from = $("#from").val();
//  var marca = document.getElementById("ch" + condicion).checked ;

//  var marca = $("#ch"+condicion).prop('checked');
  var descripcion = $("#desc"+condicion).val();
  window.opener.newunidad.clavesat.value =  descripcion;
  window.close();
}


$(document).on('keyup','#nmb', function(){
 var iddoc = $("#iddoc").val();
 var tipo = $("#tipodoc").val();
 var from = $("#from").val();
 var valor = $(this).val();

 if(valor != ""){
   buscar_datos(valor,iddoc,tipo,from);
 }
 else{
   buscar_datos('',iddoc,tipo,from);
 }
});

