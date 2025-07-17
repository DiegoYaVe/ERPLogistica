<?php
  session_start();
?>
  <script>
    let input = document.querySelector("#whatsapp");
    /* let iti = window.intlTelInput(input, {
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
    }); */
    /* iti.promise.then(function() {
      var fullNumber = iti.getSelectedCountryData().dialCode;
      document.getElementById("code").value = fullNumber;
    }); */

    input.addEventListener("input", function() {
      var fullNumber = iti.getSelectedCountryData().dialCode;
      document.getElementById("code").value = fullNumber;
    });
  </script>
  <script src="popup/js/addinvitado.js" type="text/javascript"></script>
  <script language="javascript">
    function checksubmit(){
      document.getElementById("guardar").value = "JD";
      document.getElementById("guardar").disabled = true;
      return true;
    }
    function cargar(){
      opener.location.reload();
      window.close();
    }
    function closewindow(){
      window.opener.location.reload();
      window.close();
    }
    function setuser(x){
      var d = $('#dato').val();
      var act = $('#idactividad1').val();
      $('#dato').val("0");
      if(d==0){
        $.ajax({
          url: "query/setuser.php",
          type: "GET",
          dataType: "html",
          data: {'act': act},
        })
        .done(function(datacol){
          $("#invi"+x).html(datacol);
        });
        $('#dato').val("1");
      }
    }
  </script>
  <style>
    [type=radio] {
      position: absolute;
      opacity: 0;
      width: 0;
      height: 0;
    }
  </style>
<?php
  include_once('../funciones.php');
?>
  <script>
    function setactivity(act){
      document.getElementById("actividad").value = act;
      var nmbac = document.getElementById("activ" + act).value;
      var destino = document.getElementById("destino").value;
      var nmbact = nmbac + " A " + destino;

      document.getElementById("nmbactividad").innerHTML = nmbac;
      document.getElementById("nmbac").value = nmbact;
      document.getElementById("nmbac").focus();
      document.getElementById("nmbac").select();

      if(act == 8) {
        document.getElementById("encact").style.display = "none";
        document.getElementById("btnadduser").style.display = "none";
      }
      else {
        document.getElementById("encact").style.display = "";
        document.getElementById("btnadduser").style.display = "";
      }
      
      if(act == 1){
        document.getElementById("idduracion").style.display = "";
        document.getElementById("idffin").style.display = "";
        document.getElementById("idlugar").style.display = "";
        document.getElementById("idubicacion").style.display = "";
        document.getElementById("idliga").style.display = "none";
      }
      else if(act == 4){
        document.getElementById("idduracion").style.display = "";
        document.getElementById("idffin").style.display = "";
        document.getElementById("idlugar").style.display = "none";
        document.getElementById("idubicacion").style.display = "none";
        document.getElementById("idliga").style.display = "";
      }

      else if(act == 6){
        document.getElementById("idduracion").style.display = "";
        document.getElementById("idffin").style.display = "";
        document.getElementById("idliga").style.display = "";
        document.getElementById("idlugar").style.display = "";
        document.getElementById("idubicacion").style.display = "";
      }
      else{
        document.getElementById("idduracion").style.display = "none";
        document.getElementById("duracion").value = 0;
        document.getElementById("idffin").style.display = "none";
        document.getElementById("idlugar").style.display = "none";
        document.getElementById("idubicacion").style.display = "none";
        document.getElementById("idliga").style.display = "none";
      }
    }
    function mostrarc(ocupaod){
      if(ocupaod == "1"){
        document.getElementById("showas").value = "O";
        document.getElementById("vercomoo").innerHTML = '<input type="radio" name="vercomo" ><i class="fas fa-user-minus"></i> Ocupado';
        document.getElementById("vercomod").innerHTML = '<input type="radio" name="vercomo" > <i class="fas fa-user-check"></i>';
      }
      else{
        document.getElementById("showas").value = "D";
        document.getElementById("vercomoo").innerHTML = '<input type="radio" name="vercomo" ><i class="fas fa-user-minus"></i>';
        document.getElementById("vercomod").innerHTML = '<input type="radio" name="vercomo" > <i class="fas fa-user-check"></i> Disponible';
      }
    }
    function confirma(idac,origen){
      var desc = document.getElementById("descripcionO").value;
      var conf = confirm("¿Deseas Marcar la actividad como realizada?");
      if(conf == true){
        //$("#popf"+idac).click();
        document.location.href="?modulo=actividades&accion=estatusactividad&estatus=F&id="+idac+"&origen="+origen+"&desc="+desc;
      }
    }
    function cancela(idac,origen){
      var desc = document.getElementById("descripcionO").value;
      var conf = confirm("¿Deseas Marcar la actividad como cancelada?");
      if(conf == true){
        //$("#popc"+idac).click();
        document.location.href="?modulo=actividades&accion=estatusactividad&estatus=C&id="+idac+"&origen="+origen+"&desc="+desc;
      }
    }
    function update(){
      $("#guardar").click();
    }
  </script>
<?php
  $estnot = busca($_GET['idact'],'crm_actividadesd','cad_user = "'.$_SESSION['uid'].'" AND cad_actividad','cad_notificacion');
  if($estnot == 1){
    $sqlnot = 'UPDATE crm_actividadesd SET cad_notificacion = "0" WHERE cad_user = "'.$_SESSION['uid'].'" AND cad_actividad = "'.$_GET['idact'].'"';
    setq($sqlnot);
  }
  if(isset($_GET['tablero'])) $tablero = $_GET['tablero'];
  else $tablero = busca($_GET['idact'],'crm_actividades','ca_id','ca_tablero');

  if(isset($_GET['orig'])) $or = $_GET['orig'];
  else $or = "2";

  if(isset($_GET['user'])){ $hiden = ''; $read = '';}
  else{ $hiden = 'hidden'; $read = 'readonly';}

  if ($tablero != 0) {
    $accion = '?modulo=actividades&accion=updateact&tablero='.$tablero.'&idact='.$_GET['idact'].'&origen='.$or.'';
    $st = 'style="display:none"';
  }else{
    $tablero = "0";
    if(isset($_GET['cal'])) $cal = '&cal=1';
    else $cal = "";
    $accion = '?modulo=actividades&accion=updateact&idact='.$_GET['idact'].'&origen='.$or.$cal.'';
    $style = 'style="display:none"';
  }

  if(isset($_GET['confirma'])){
    $title = 'Finalizar actividad';
    $accion = '?modulo=actividades&accion=estatusactividad&estatus=F&id='.$_GET['idact'].'&origen='.$or.'';
    $classbtn = 'success';
    $iconbtn = 'fas fa-check';
    $hidbtn = 'hidden';
    $txtbtn = ' Finalizar';
    $read = '';
  }elseif(isset($_GET['cancela'])){
    $title = 'Cancelar actividad';
    $accion = '?modulo=actividades&accion=estatusactividad&estatus=C&id='.$_GET['idact'].'&origen='.$or.'';
    $classbtn = 'danger';
    $iconbtn = 'fas fa-time';
    $txtbtn = ' Cancelar';
    $hidbtn = 'hidden';
    $read = '';
  }else{
    $title = 'Tipo de actividad a registrar';
    $classbtn = 'primary';
    $iconbtn = 'fas fa-save';
    $txtbtn = ' Actualizar';
    $hidbtn = '';
  }
  echo '<script>
    function showwhats(show){
      if(show == 1){
        document.getElementById("idwhats").style.display = "";
        //document.getElementById("btnConfWhats").style.display = "none";
        $("#btnConfWhats").remove();
      }
      else{
        document.getElementById("idwhats").style.display = "none";
        obtenerMsj();
      }
    }
    function obtenerMsj(){
      var tel = "+"+document.getElementById("code").value+document.getElementById("whatsapp").value;
      var msj = document.getElementById("wamessage").value;
      var msjSend = msj.replaceAll(" ","%20");
      var msjSend = msjSend.replaceAll("\n","%0A");
      sendWp(tel,msjSend);
    }
    function sendWp(tel,msjSend){
      var url = "https://api.whatsapp.com/send?phone="+tel+"&text="+msjSend;
      window.open(url,"_blank");
    }
  </script>';

  if(isset($_GET['ver'])) $ver = 'hidden';

  $idact = busca($_GET['idact'],'crm_actividades','ca_id','ca_accion');
  if(isset($_GET['readon'])) {
    $readon = ' disabled="true" '; 
    $disamos = ' readonly="readonly" disabled';
  }else {
    $readon = "";
    $disamos = "";
  }

  echo'<input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />
  <input type="hidden" id="idactividad1" value="'.$_GET['idact'].'"/>';
  echo'<div class="container mt-1 col-10">
    <form action="'.$accion.'" method="post" enctype ="multipart/form-data" onsubmit="checksubmit();">
      <div class="row page-title-actions">
        <div class="col-4 col-md-2"><label class="modaltext">'.$title.'</label></div>
        <div class="col-4 col-md-4" data-toggle="buttons">';
          $sqla = 'SELECT * FROM crm_acciones WHERE ca_estatus = "A" AND ca_id = "'.$idact.'" ORDER BY ca_id';
          $resulta = setq($sqla);
          $valac = "";
          $sql = 'SELECT crm_acciones.ca_nmb,crm_actividades.ca_nmb,crm_actividadesd.cad_miembro,crm_actividadesd.cad_telefono,
                  crm_actividadesd.cad_correo,crm_actividades.ca_duracion,crm_actividades.ca_vercomo,crm_actividades.ca_lugar,
                  crm_actividades.ca_liga,crm_actividades.ca_ubicacion,crm_actividades.ca_descripcion,crm_actividades.ca_fecha,
                  crm_actividades.ca_hora,crm_actividades.ca_adjunto,crm_actividades.ca_adjuntoext
                  FROM crm_actividades
                  INNER JOIN crm_acciones ON crm_actividades.ca_accion = crm_acciones.ca_id
                  INNER JOIN crm_actividadesd ON cad_actividad = crm_actividades.ca_id
                  WHERE crm_actividades.ca_tablero = "'.$tablero.'" AND crm_actividades.ca_id = "'.$_GET['idact'].'"';
          $extusu = busca($_GET['idact'],'crm_actividadesd','cad_tipomiembro IN ("E","C") AND cad_actividad','COUNT(*)');
          if($tablero == 0) {
            if($extusu > 0)
              $sql .= 'AND cad_tipomiembro = "E"';
          }
          else $sql .= 'AND cad_tipomiembro = "C"';
          $sql.=' ORDER BY crm_actividades.ca_fecha ASC,crm_actividades.ca_hora ASC';
          $resultac = setq($sql);
          list($nmbal,$nmbact,$nmbcliente,$telcliente,$mailcliente,$mins,$vercomo,$lugar,$liga,$ubicacion,$descripcion,$fini,$hora,$adjunto,$adjuntoext) = $resultac->fetch_array();

          while($rowa = $resulta->fetch_array()){
            if($rowa['ca_id'] == $_GET['idact']){
              $sele = 'active';
              $valac = $rowa['ca_id'];
              $nmbal = $rowa['ca_nmb'];
              $nmbact = $rowa['ca_nmb'].' A '.$nmbcliente;
            }else $sele = "";

            $showoc = "active";
            $showdi = "";
            echo '<div class="col-2 col-md-1">
              <label class="btn btn-secondary mr-1 mb-1 '.$sele.'">
                <input type="radio" name="options" id="option1" > <i class="'.$rowa['ca_icono'].'"></i>
              </label>
            </div>';
            echo '<input type="hidden" value="'.$rowa['ca_descripcion'].'" id="activ'.$rowa['ca_id'].'">';
          }
          echo '<input type="hidden" value="'.$valac.'" name="actividad" id="actividad">
          <input type="hidden" value="O" name="showas" id="showas">
        </div>
        <div class="col-2 col-md-1" '.$hiden.'>
          <button type="button" class="btn btn-success mr-1" onclick="confirma('.$_GET['idact'].','.$or.');"><i class="fas fa-check"></i></button>
          <a data-fancybox id="popf'.$_GET['idact'].'" data-type="ajax" data-src="popup/setactividad.php?idact='.$_GET['idact'].'&confirma=1&orig='.$or.'&readon=1" href="javascript:;"></a>
        </div>
        <div class="col-2 col-md-1" '.$hiden.'>
          <button type="button" class="btn btn-danger" onclick="cancela('.$_GET['idact'].','.$or.');"><i class="fa fa-times"></i></button>
          <a data-fancybox data-type="ajax" id="popc'.$_GET['idact'].'" data-src="popup/setactividad.php?idact='.$_GET['idact'].'&cancela=1&orig='.$or.'&readon=1" href="javascript:;"></a>
        </div>';
        if($telcliente || $nmbcliente){
          if($extusu > 0){
            $req = 'required';
            echo '<div class="col-2 col-md-2" '.$hiden.' id="btnConfWhats">
              <button type="button" class="btn bg-green bg-accent-4 text-white" style="background: #25D366" onclick="showwhats(1);"><i class="fab fa-whatsapp" style="color: #ffffff"></i> Confirmar cita</button>
            </div>';
          }
        }
        echo'<div class="col-2 col-md-1" '.$hiden.'>
          <button type="button" class="btn btn-sm btn-primary" onclick="update();"><i class="fa fa-save"></i> Actualizar</button>
        </div>';
      echo '</div>';
      echo '<div class="row" id="idwhats" '.$hidbtn.' style="display:none">
        <div class="col-3 col-md-3">
          <label for"nmbatc">Destino</label>
          <input id="code" name="code" hidden>
          <input type="tel" id="whatsapp" value="+52'.$telcliente.'" name="whatsapp" class="form-control" placeholder="Telefono" '.$req.' '.$hidbtn.'>
        </div>
        <div class="col-3 col-md-5">
          <label for"mesage">Mensaje</label>
          <textarea name="wamessage" id="wamessage" rows="3" class="form-control" '.$hidbtn.'>Hola '.$nmbcliente.', recuerda que tenemos una '.$nmbact.' agendada para el día '.date('d-m-Y',strtotime($fini)).' a las '.$hora."\n".'¿Confirmamos la cita?</textarea>
        </div>
        <div class="mb-5 col-md-4">
          <center>
            <br>
            <button type="button" class="btn btn-green" onclick="showwhats(0);"><i class="fab fa-whatsapp"></i>> Enviar</button>
          </center>
        </div>
      </div>';
      $fini = date('Y-m-d',strtotime($fini)).'T'.date('H:i:00',strtotime($hora));
      $caaccion = busca($_GET['idact'],'crm_actividades','ca_id','ca_accion');
      if($_GET['idact'] == 1 || $_GET['idact'] == 6 || $caaccion == 1 || $caaccion == 6){
        $caduracion = busca($_GET['idact'],'crm_actividades','ca_id','ca_duracion');
        if($caduracion != 0) $mins = $caduracion;
        else $mins = 60;
        $actlugar = "";
      }else{
        $actlugar = ' style="display:none" ';
        $mins = 0;
      }
      echo '<div class="row">
        <div class="col-12 col-md-12">
          <label for"nmbatc"></label>
        <div class="alert alert-primary fade in" role="alert" id="nmbactividad">'.$nmbal.'</div>
      </div>
      <div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"nmbac">Nombra tu actividad</label>
          <input type="text" name="nmbac" value="'.$nmbact.'" id="nmbac" class="form-control" placeholder="Nombre de tu actividad" required="required" '.$readon.' />
        </div>
      </div>
      <div class="col-12 col-md-3" >
        <div class="mb-5">
          <label for"fini">Fecha y hora de la actividad</label>
          <input type="datetime-local" name="fini" id="fini" value="'.$fini.'" class="form-control" placeholder="Cuando programas tu actividad" required="required" '.$readon.' />
        </div>
      </div>
      <div class="col-6 col-md-2" '.$actlugar.' id="idduracion">
        <div class="mb-5">
          <label>Duración</label>
          <div class="input-group">
            <input type="text" class="form-control square" id="duracion" placeholder="Duración" aria-label="Duración en minutos de la actividad" name="duracion" required value="'.$mins.'" onfocus="this.select();" '.$readon.' >
            <span class="input-group-text">Mins</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4" '.$actlugar.' id="idffin">
        <div class="mb-5">
          <center><label>Mostrar como</label>
          <div class="input-group">
            <div class="btn-group" data-toggle="buttons">
              <center>
                <label class="btn btn-danger mr-1 mb-1 '.$showoc.'" id="vercomoo" '.$disamos.' onclick="mostrarc(1)">
                  <input type="radio" name="vercomo" '.$disamos.'><i class="fas fa-user-minus" id="" '.$disamos.' ></i> Ocupado
                </label>
                <label class="btn btn-success mr-1 mb-1 '.$showdi.'" id="vercomod"  onclick="mostrarc(2)">
                  <input type="radio" name="vercomo" '.$disamos.'> <i class="fas fa-user-check" '.$disamos.' ></i>
                </label>
              </center>
            </div>
          </div>
          </center>
        </div>
      </div>
      <div class="col-6 col-md-4" '.$actlugar.' id="idlugar">
        <div class="mb-5">
          <label for"ffin">Lugar</label>
          <input type="text" name="lugar" id="lugar" value="'.$lugar.'" class="form-control" placeholder="Donde se realizará tu actividad" '.$readon.'  />
        </div>
      </div>
      <div class="col-6 col-md-4" '.$actlugar.' id="idubicacion">
        <div class="mb-5">
          <label for"ffin">Ubicación</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
            <input type="text" name="ubicacion" id="ubicacion" value="'.$ubicacion.'" class="form-control" placeholder="Donde se realizará tu actividad" '.$readon.'  />
          </div>
        </div>
      </div>
      <!--<div class="col-6 col-md-4" '.$actlugar.' id="idliga">
        <div class="mb-5">
          <label for"ffin">Liga de acceso</label>
          <input type="text" name="liga" id="liga" value="'.$liga.'" class="form-control" placeholder="Liga de acceso a la reunión" '.$readon.'  />
        </div>
      </div>-->
      <div class="col-6 col-md-4">
        <label for"ffin">Documento Adjunto</label>
        <div class="mb-5">
          <label id="projectinput7" class="file center-block form-control">';
            if($adjunto != "" && file_exists('../Adjuntos/'.$_SESSION['emp'].'/actividades/'.$adjunto)){
              echo '<a target="_BLANK" href="Adjuntos/'.$_SESSION['emp'].'/actividades/'.$adjunto.'">
                <button type="button" class="btn-sm btn-primary" ><i class="fas fa-paperclip"></i> '.$adjunto.'</button>
              </a>';
              if(!isset($_GET['ver']))
                echo'<a href="?modulo=actividades&accion=deladjact&idact='.$_GET['idact'].'" '.$readon.'>
                  <button type="button" class="btn-sm btn-danger"><i class="fas fa-eraser"></i> Borrar adjunto</button>
                </a>';
            }
            else
              echo '<input type="file" name="adjunto" id="adjunto" '.$readon.'><span class="file-custom" ></span>';
          echo '</label>
        </div>
      </div>';
      if(isset($_GET['confirma'])){
        $msjdesc = '(Retroalimentación de la actividad)';
      }else if(isset($_GET['cancela'])){
        $msjdesc = '(Motivo por el que se cancela la actividad)';
      }
      echo'<div class="col-12 col-md-8">
        <div class="mb-5">
          <label for"ffin">Descripción de la cita '.$msjdesc.'</label>
          <textarea class="form-control" id="descripcionO" name="descripcion" rows="2" required="required" onfocus="this.select();" '.$read.'>'.$descripcion.'</textarea>
        </div>
      </div>
      <div class="col-12 col-md-12 alert alert-primary">Participantes</div>';
      if($tablero != 0){
        $idcli = busca($_GET['idact'],'crm_actividadesd','cad_tipomiembro = "C" AND cad_actividad','cad_id');
        echo '<input type="hidden" name="idcliente" value="'.$idcli.'"/>';
      }
      echo'<div class="col-12 col-md-4" '.$style.'>
        <div class="mb-5">
          <label for"destino">Persona(s) a contactar</label>
          <input type="text" name="destino" id="destino" value="'.$nmbcliente.'" class="form-control" placeholder="Persona para contactar"  '.$readon.'  />
        </div>
      </div>
      <div class="col-6 col-md-4" '.$style.'>
        <div class="mb-5">
          <label for"telefono">Número telefónico</label>
          <input type="tel" name="telefono" id="telefono" value="'.$telcliente.'" class="form-control" placeholder="Telefóno del contacto"  '.$readon.' />
        </div>
      </div>
      <div class="col-6 col-md-4" '.$style.'>
        <div class="mb-5">
          <label for"correo">Correo de contacto</label>
          <input type="email" name="correo" id="correo" style="text-transform:lowercase;" value="'.$mailcliente.'" class="form-control" placeholder="correo de la contacto" '.$readon.'/>
        </div>
      </div>';
      if(busca($_GET['idact'],'crm_actividadesd','cad_tipomiembro="R" AND cad_actividad','COUNT(*)') > 0){
        $sqlr = 'SELECT cad_id,cad_user,cad_correo FROM crm_actividadesd WHERE cad_actividad = "'.$_GET['idact'].'" AND cad_tipomiembro = "R"';
        $resultr = setq($sqlr);
        list($idr,$miembro,$correo) = $resultr->fetch_array();
        echo '<input type="hidden" name="idr" value="'.$idr.'"/>';
        echo'<div class="col-12 col-md-4">
          <div class="mb-5">
            <label for"resp">Encargado de la actividad</label>
            <input type="text" name="resp" id="resp" value="'.$miembro.'" class="form-control" readonly/>
          </div>
        </div>
        <div class="col-6 col-md-4" hidden>
          <div class="mb-5">
            <label for"correoresp" hidden>Correo del encargado</label>
            <input type="email" name="correoresp" style="text-transform:lowercase;" id="correoresp" value="'.$correo.'" class="form-control" hidden readonly/>
          </div>
        </div>';
      }
      $f = 0;
      if(busca($_GET['idact'],'crm_actividadesd','cad_tipomiembro = "I" AND cad_actividad','COUNT(*)') > 0){
        $sqli = 'SELECT cad_id,cad_user FROM crm_actividadesd WHERE cad_actividad = "'.$_GET['idact'].'" AND cad_tipomiembro = "I"';
        $resulti = setq($sqli);
        echo '<div class="row col-lg-12" id="invitados">';
        while($rowi = $resulti->fetch_array()){
          $f++;
          echo '<input type="hidden" name="idi'.$rowi['cad_user'].'" value="'.$rowi['cad_id'].'"/>';
            echo '<div class="row col-lg-4">
              <div class="col-12 col-md-12">
                <div class="mb-5">
                  <label>Invitado a la actividad</label>
                  <input type="text" name="inv[]" id="inv'.$f.'" value="'.$rowi['cad_user'].'" class="form-control" readonly/>
                </div>
              </div>
            </div>';
        }
        echo'</div>';
      }else{
        echo '<div class="row col-lg-12" id="invitados">
        </div>';
      }
      $sqlcount = 'SELECT COUNT(*) c FROM usuarios WHERE u_empresa = "'.$_SESSION['emp'].'" AND u_id != "'.$_SESSION['uid'].'"';
      $resultcount = setq($sqlcount);
      list($con) = $resultcount->fetch_array();
      $fcon = $con - $f;
      if($idact == "8") $stjdceo = 'style="display:none"';
      if($fcon >= 1){
        echo '<div class="col-6 col-md-12 mb-2 mt-2" '.$hidbtn.' '.$hiden.' '.$stjdceo.'>
          <div class="mb-5">
            <center><button type="button" class="btn text-white" style="background: gray" id="invitado"><i class="fas fa-plus" style="color: #ffffff;"></i> Agregar usuario </button></center>
          </div>
        </div>';
        echo '<input type="hidden" id="countu" value="'.$fcon.'" />
        <input type="hidden" id="dato" value="0" />
        ';
      }
      $e = 0;
      if(busca($_GET['idact'],'crm_actividadesd','cad_tipomiembro="E" AND cad_actividad','COUNT(*)') > 0){
        $sqle = 'SELECT cad_id,cad_miembro,cad_correo,cad_telefono FROM crm_actividadesd 
                  WHERE cad_actividad = "'.$_GET['idact'].'" AND cad_tipomiembro = "E"';
        $resulte = setq($sqle);
        while($rowe = $resulte->fetch_array()){
          $e++;
          echo '<input type="hidden" name="ide'.$e.'" value="'.$rowe['cad_id'].'" />';
          echo'<div class="row col-lg-12">
          <div class="col-12 col-md-4">
            <div class="mb-5">
              <label>Invitado externo</label>
              <input type="text" name="ext[]" id="ext[]" value="'.$rowe['cad_miembro'].'" class="form-control" '.$readon.'/>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="mb-5">
              <label>Correo del invitado</label>
              <input type="email" style="text-transform:lowercase;" name="corr[]" id="corr[]" value="'.$rowe['cad_correo'].'" class="form-control" '.$readon.'/>
              </select>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="mb-5">
              <label>Teléfono del invitado</label>
              <input type="tel" maxlength="10" minlength="10" name="teli[]" id="teli[]" value="'.$rowe['cad_telefono'].'" class="form-control" '.$readon.'/>
              </select>
            </div>
          </div>
          </div>';
        }
      }
      if($e < 6){
        echo'<div class="col-12 col-md-12" mb-2 mt-2 '.$hidbtn.' '.$hiden.'>
          <div class="mb-5">
            <center><buttn type="button" class="btn text-white" style="background: teal;" id="externo"><i class="fas fa-plus" style="color: #ffffff"></i> Agregar invitado </button></center>
          </div>
        </div>
        <div class="row col-lg-12" id="externos">
        </div>';
      }
      echo '<input type="hidden" name="ex" value="'.$e.'"/>
      <input type="hidden" id="usu" name="usu" value="'.$f.'"/>';
      echo'<div class="col-12 col-md-12">
        <center>
          <button role="submit" class="btn btn-'.$classbtn.'" id="guardar" '.$ver.' ><i class="'.$iconbtn.'"></i>'.$txtbtn.'</button>
        </center>
      </div>
    </form>
  </div>';
  ?>