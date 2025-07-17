$(document).ready(function(){
  var xu = $("#countu").val();
  var campos_max = xu;
  var x = 1;
  $('#invitado').click (function(e) {
    e.preventDefault();
    if (x <= campos_max) {
      $('#invitados').prepend('<div class="row col-lg-4" id="invitados">\
        <div class="col-12 col-md-12">\
          <div class="mb-5">\
            <label>Invitado a la actividad</label>\
            <select id="invi'+x+'" onclick="setuser('+x+');" name="inv[]" class="form-control">\
            </select>\
          </div>\
        </div>\
        <a class="remover_invitado" title="Quitar"><font color="red">Eliminar</font></a>\
        </div>');
        document.getElementById("invi"+x).focus();
      x++;
    }
  });
  $("#invitados").on("click",".remover_invitado",function(e) {
    e.preventDefault();
    $(this).parent("div").remove();
    x--;
  });
});

$(document).ready(function(){
  var campos_max = 7;
  var x = 1;
  $('#externo').click (function(e) {
    e.preventDefault();
    if (x < campos_max) {
      $('#externos').prepend('<div class="row col-lg-4" id="externos">\
        <div class="col-12 col-md-12">\
          <div class="mb-5">\
            <label>Invitado externo</label>\
            <input type="text" name="ext[]" id="ext'+x+'" placeholder="Nombre del invitado" class="form-control" onfocus="this.select();"/>\
          </div>\
        </div>\
        <div class="col-12 col-md-12">\
          <div class="mb-5">\
            <label>Correo del invitado</label>\
            <input type="email" name="corr[]" style="text-transform:lowercase;" id="corr[]" placeholder="Correo del invitado" class="form-control"/>\
          </div>\
        </div>\
        <div class="col-12 col-md-12">\
          <div class="mb-5">\
            <label>Número telefónico del invitado</label>\
            <input type="tel" maxlength="10" minlength="10" name="teli[]" id="teli[]" placeholder="Telefóno del invitado" class="form-control"/>\
          </div>\
        </div>\
        <a class="remover_externo" title="Quitar"><font color="red">Eliminar</font></a>\
        </div>');
        document.getElementById("ext"+x).focus();
      x++;
    }
  });
  $("#externos").on("click",".remover_externo",function(e) {
    e.preventDefault();
    $(this).parent("div").remove();
    x--;
  });
});