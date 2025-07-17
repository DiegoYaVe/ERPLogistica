document.addEventListener("DOMContentLoaded", function() {
    var descuentodiv = document.getElementById("descuentosdiv");
    var formdescuento = document.getElementById("divdescuentos");
    var formcondiciones = document.getElementById("divcondiciones");
    var formcategorias = document.getElementById("divcategorias");
    var formtarifas = document.getElementById("divtarifas");
    var formimporte = document.getElementById("divimporte");
    var formmeta = document.getElementById("divmeta");
    var formmetagrupo = document.getElementById("divmetagrupo");
    var formcomision = document.getElementById("divcomision");
    var formtextoguia = document.getElementById("divtextoguia");
    var formtextoticket = document.getElementById("divtextoticket");
    var formdiasgarantia = document.getElementById("divdiasgarantia");
    var formcomisionesventas = document.getElementById("divcomisionesventas");
    
    

    var categoriasdiv = document.getElementById("categoriasdiv");
    var condicionesdiv = document.getElementById("condicionesdiv");
    var tarifasdiv = document.getElementById("tarifasdiv");
    var importediv = document.getElementById("importediv");
    var metadiv = document.getElementById("metadiv");
    var metagrupodiv = document.getElementById("metagrupodiv");
    var comisiondiv = document.getElementById("comisiondiv");
    var textoguiadiv = document.getElementById("textoguiadiv");
    var textoticketdiv = document.getElementById("textoticketdiv");
    var diasgarantiadiv = document.getElementById("diasgarantiadiv");
    var comisionesventasdiv = document.getElementById("comisionesventasdiv");

    descuentodiv.addEventListener("click", function() {
        formdescuento.hidden = false;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });

    diasgarantiadiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = false;
        formcomisionesventas.hidden = true;
    });
    
    condicionesdiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = false;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });
    
    categoriasdiv.addEventListener("click", function() {
        formdescuento.hidden = true; 
        formcondiciones.hidden = true;
        formcategorias.hidden = false;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });
    
    tarifasdiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = false;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });

    importediv.addEventListener("click", function() {    
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = false;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });

    metadiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = false;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });

    metagrupodiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = false;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });

    comisiondiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = false;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
   });

    textoguiadiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = false;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });

    textoticketdiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = false;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });

    textoticketdiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = false;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = true;
    });

    comisionesventasdiv.addEventListener("click", function() {
        formdescuento.hidden = true;
        formcondiciones.hidden = true;
        formcategorias.hidden = true;
        formtarifas.hidden = true;
        formimporte.hidden = true;
        formmeta.hidden = true;
        formmetagrupo.hidden = true;
        formcomision.hidden = true;
        formtextoguia.hidden = true;
        formtextoticket.hidden = true;
        formdiasgarantia.hidden = true;
        formcomisionesventas.hidden = false;
    });
});
