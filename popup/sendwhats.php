<?php
  session_start();
  //ini_set('display_errors',1);
  include_once('../funciones.php');
?>
  <script>
    /* let input = document.querySelector("#whatsapp");
    let iti = window.intlTelInput(input, {
      initialCountry: "auto",
      geoIpLookup: function(callback) {
        $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
          const countryCode = (resp && resp.country) ? resp.country : "us";
          callback(countryCode);
        });
      },
      hiddenInput: "full_phone",
      formatOnDisplay:false,
      separateDialCode:true,
      utilsScript:"https://s3-us-west-2.amazonaws.com/s.cdpn.io/32471/utils.js",
    });
    iti.promise.then(function() {
      var fullNumber = iti.getSelectedCountryData().dialCode;
      document.getElementById("code").value = fullNumber;
    });

    input.addEventListener("input", function() {
      var fullNumber = iti.getSelectedCountryData().dialCode;
      document.getElementById("code").value = fullNumber;
    }); */
  </script>
<?php
  if(isset($_GET['id'])) {
    require_once('../modulos/cotizaciones.php');
    $cotiza = new modelcotizaciones();
    $cotiza->select($_GET['id']);
    $tel = $cotiza->teldestino;
    if(!$tel) $tel = '';
    $destino = $cotiza->destino;
    $uuid = busca($cotiza->id,'crm_cotizaciones','cc_id','cc_uuid');
    if(!$uuid) {
      $uuid = $cotiza->guidv4();

      $sql = 'UPDATE crm_cotizaciones SET cc_uuid = "'.$uuid.'" WHERE cc_id = "'.$cotiza->id.'"';
      setq($sql);
    }
    $mensaje = 'Hola '.$destino.' ';
    $titulo = 'Enviar cotización por WhatsApp';
    $col = '6';
  }elseif(isset($_GET['idact'])){
    $titulo = 'Confirmación de cita por WhatsApp';
    $uuid = '';
    $tablero = busca($_GET['idact'],'crm_actividades','ca_id','ca_tablero');
    $sql = 'SELECT crm_actividades.ca_nmb,crm_actividadesd.cad_miembro,crm_actividadesd.cad_telefono,
            crm_actividades.ca_fecha,crm_actividades.ca_hora FROM crm_actividades
            INNER JOIN crm_acciones ON crm_actividades.ca_accion = crm_acciones.ca_id
            INNER JOIN crm_actividadesd ON cad_actividad = crm_actividades.ca_id
            WHERE crm_actividades.ca_tablero = "'.$tablero.'" AND crm_actividades.ca_id = "'.$_GET['idact'].'"';
    if($tablero == 0) $sql .= 'AND cad_tipomiembro = "U"';
    else $sql .= 'AND cad_tipomiembro = "C"';
    $sql.=' ORDER BY crm_actividades.ca_fecha ASC,crm_actividades.ca_hora ASC';
    $resultac = setq($sql);
    list($nmbact,$nmbcliente,$telcliente,$fini,$hora) = $resultac->fetch_array();
    $tel = $telcliente;
    $mensaje = 'Hola '.$nmbcliente.', recuerda que tenemos una '.$nmbact.' agendada para el día '.date('d-m-Y',strtotime($fini)).' a las '.$hora."\n".'¿Confirmamos la cita?';
    $col = '9';
  }
  echo '<div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.'</div>
      <form method="post">
        <input type="hidden" value="0" name="liga" id="liga">
        <input type="hidden" value="'.$uuid.'" name="uuid" id="uuid">
        <div id="filter-panel" class="collapse filter-panel row">
          <div class="mb-5 col-md-2">
            <label><b>Cursiva</b></label><br>
            <label>_tu-texto_</label>
          </div>
          <div class="mb-5 col-md-2">
            <label><b>Negrita</b></label><br>
            <label>*tu-texto*</label>
          </div>
          <div class="mb-5 col-md-2">
            <label><b>Tachado</b></label><br>
            <label>~tu-texto~</label>
          </div>
          <div class="mb-5 col-md-2">
            <label><b>Monoespaciado</b></label><br>
            <label>```tu-texto```</label>
          </div>
        </div>
        <div class="row">
          <div class="mb-5 col-md-3">
            <label>Destino</label>
            <input id="code" name="code" hidden>
            <input type="tel" id="whatsapp" value="+52'.$tel.'" name="whatsapp" class="form-control" placeholder="Telefono" required>
          </div>
          <div class="mb-5 col-md-'.$col.'">
            <label>Mensaje</label>
            <a type="button" class=""  data-bs-toggle="collapse" data-bs-target="#filter-panel">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-info"></i>
            </a>
            <textarea name="wamessage" id="wamessage" rows="4" class="form-control" required>'.$mensaje.'</textarea>
          </div>
        ';
        if(isset($_GET['id'])){
          echo'<div class="col-6 col-md-3"  id="divopciones1">
            <div class="mb-5">
              <label for"fini">Adjuntar liga de cotización</label>
              <input type="checkbox" name="adcotiza" id="adcotiza" class="flipswitchsn" onchange="showliga();" />
            </div>
          </div></div>
          <center>
            <div class="mb-5 col-md-12">
            <button type="button" class="btn btn-success" onclick="showwhats(1);" ><i class="fab fa-whatsapp" style="color: #ffffff;"></i> Enviar</button>
            </div>
          </center>';
        }else{
          echo'</div><center>
            <div class="mb-5 col-md-12">
              <button type="button" class="btn btn-success" onclick="showwhats(2);" ><i class="fab fa-whatsapp" style="color: #ffffff;"></i> Enviar</button>
            </div>
          </center>';
        }
      echo'</form>
    </div>
  </div>
  
  <script>
    function showliga(){
      var check = document.getElementById("adcotiza");
      var desc = document.getElementById("wamessage").value;
      if(check.checked) document.getElementById("liga").value = 1;
      else document.getElementById("liga").value = 0;
    }
    function showwhats(show){
      if(show == "1"){
        var tel = "+"+document.getElementById("code").value+document.getElementById("whatsapp").value;
        var tel = tel.replaceAll(" ","");
        var msj = document.getElementById("wamessage").value;
        var msjSend = msj.replaceAll(" ","%20");
        var msjSend = msjSend.replaceAll("\n","%0A");
        var liga = document.getElementById("liga").value;
        var uuid = document.getElementById("uuid").value;
        var ligaCotiza = "Puedes descargar tu cotización en el siguiente enlace:%0A%0Ahttps://tye-solutions.com/fabrica/cot.php?uuid="+uuid;
        var ligaCotiza = ligaCotiza.replaceAll(" ","%20");
        if(liga === "1") msjFinal = msjSend+"%0A%0A"+ligaCotiza;
        else msjFinal = msjSend;
        sendWp(tel,msjFinal);
      }else{
        var tel = "+"+document.getElementById("code").value+document.getElementById("whatsapp").value;
        var tel = tel.replaceAll(" ","");
        var msj = document.getElementById("wamessage").value;
        var msjSend = msj.replaceAll(" ","%20");
        var msjSend = msjSend.replaceAll("\n","%0A");
        sendWp(tel,msjSend);
      }
    }
    function sendWp(tel,msjSend){
      var url = "https://api.whatsapp.com/send?phone="+tel+"&text="+msjSend;
      window.open(url,"_blank");
    }
  </script>';
?>