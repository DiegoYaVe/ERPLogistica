$(buscar_datos());

function buscar_datos(consulta,empresa,iddoc,tipo){
  $.ajax({
    url: 'buscarclientescomp.php', 
    type: 'POST',
    dataType: 'html',
    data: {'consulta': consulta},
  })
  .done(function(respuesta){
    $("#datos").html(respuesta);
  })
}

function setcondicion(condicion){
  var empresa = $("#empresa").val();
//  var marca = document.getElementById("ch" + condicion).checked ;

//  var marca = $("#ch"+condicion).prop('checked');
  var descripcion = $("#desc"+condicion).val();
  window.opener.newunidad.clavesat.value =  descripcion;
  window.close();
}


$(document).on('keyup','#nmb', function(){
 var valor = $(this).val();

 if(valor != ""){
   buscar_datos(valor);
 }
 else{
   buscar_datos('');
 }
});