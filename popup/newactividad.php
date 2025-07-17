<?php
  include_once('../funciones.php');
  session_start();
?>
  <script src="popup/js/addinvitado.js" type="text/javascript"></script>
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
        //document.getElementById("idliga").style.display = "none";
      }
      else if(act == 4){
        document.getElementById("idduracion").style.display = "";
        document.getElementById("idffin").style.display = "";
        document.getElementById("idlugar").style.display = "none";
        document.getElementById("idubicacion").style.display = "none";
        //document.getElementById("idliga").style.display = "";
      }

      else if(act == 6){
        document.getElementById("idduracion").style.display = "";
        document.getElementById("idffin").style.display = "";
        //document.getElementById("idliga").style.display = "";
        document.getElementById("idlugar").style.display = "";
        document.getElementById("idubicacion").style.display = "";
      }
      else{
        document.getElementById("idduracion").style.display = "none";
        document.getElementById("duracion").value = 0;
        document.getElementById("idffin").style.display = "none";
        document.getElementById("idlugar").style.display = "none";
        document.getElementById("idubicacion").style.display = "none";
        //document.getElementById("idliga").style.display = "none";
      }
    }
    function mostrarc(ocupaod){
      if(ocupaod == "1"){
        document.getElementById("showas").value = "O";
        document.getElementById("vercomoo").innerHTML = '<input type="radio" name="vercomo" ><i class="fas fa-user-minus" style="color: #ffffff;"></i> Ocupado';
        document.getElementById("vercomod").innerHTML = '<input type="radio" name="vercomo" > <i class="fas fa-user-check" style="color: #ffffff;"></i>';
      }
      else{
        document.getElementById("showas").value = "D";
        document.getElementById("vercomoo").innerHTML = '<input type="radio" name="vercomo" ><i class="fas fa-user-minus" style="color: #ffffff;"></i>';
        document.getElementById("vercomod").innerHTML = '<input type="radio" name="vercomo" > <i class="fas fa-user-check" style="color: #ffffff;"></i> Disponible';
      }
    }
  </script>
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
    function setgmail(){
      var cgm = $("#resp").val();

      $.ajax({
        url: "query/respgmail.php",
        type: "POST",
        dataType: "text",
        data: {'cgm': cgm},
        success:function(data){
          $("#correoresp").val(data);
        }
      });
    }
    function setuser(x){
      var d = $('#dato').val();
      $('#dato').val("0");
      if(d==0){
        $.ajax({
          url: "query/setuser.php",
          type: "GET",
          dataType: "html"
        })
        .done(function(datacol){
          $("#invi"+x).html(datacol);
        });
        $('#dato').val("1");
      }
    }
    function removeinvext(inv){
      $("#invext"+inv).remove();
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
  if (isset($_GET['tablero']) && $_GET['tablero'] != 0) {
    $tablero = $_GET['tablero'];
    $accion = '?modulo=actividades&accion=insertact&tablero='.$tablero.'';
    $st = 'style="display:none"';
  }else if(isset($_GET['id']) || isset($_GET['clone'])){
    if($_GET['id'] == "3") $cal = '&cal=1';
    else $cal = '';
    $accion = '?modulo=actividades&accion=insertact'.$cal.'';
    $style = 'style="display:none"';
  }

  echo'
  <div class="container mt-1 col-9">
    <form action="'.$accion.'" method="post" enctype =  "multipart/form-data" onsubmit="checksubmit();">
      <div class="row">
        <div class="col-4 col-md-3"><label class="modaltext">Tipo de actividad a registrar</label></div>
        <div class="btn-group col-md-9" data-toggle="buttons">';
          $sqla = 'SELECT * FROM crm_acciones WHERE ca_estatus = "A"';
          $sqla .= ' ORDER BY ca_id';
          $resulta = setq($sqla);
          $valac = "";
          $tablero = busca($tablero,'crm_tableros','ct_id','ct_cliente');
          $nmbcliente = busca($tablero,'crm_clientes','c_id','c_nmb');
          $telcliente = busca($tablero,'crm_clientes','c_id','c_telefono1');
          $mailcliente = busca($tablero,'crm_clientes','c_id','c_correo1');
          while($rowa = $resulta->fetch_array()){
            if($rowa['ca_id'] == $_GET['act']){
              $sele = 'active';
              $valac = $rowa['ca_id'];
              $nmbal = $rowa['ca_nmb'];
              $nmbact = $rowa['ca_nmb'].' A '.$nmbcliente;
            }else $sele = "";

            $showoc = "active";
            $showdi = "";
            $lugar = "";
            $liga = "";
            $ubicacion = "";
            $descripcion = "";
            if(isset($_GET['clone'])){
              $sql = 'SELECT crm_acciones.ca_nmb,crm_actividades.ca_nmb,crm_actividadesd.cad_miembro,crm_actividadesd.cad_telefono,
                      crm_actividadesd.cad_correo,crm_actividades.ca_duracion,crm_actividades.ca_vercomo,crm_actividades.ca_lugar,
                      crm_actividades.ca_liga,crm_actividades.ca_ubicacion,crm_actividades.ca_descripcion
                      FROM crm_actividades
                      INNER JOIN crm_acciones ON crm_actividades.ca_accion = crm_acciones.ca_id
                      INNER JOIN crm_actividadesd ON cad_actividad = crm_actividades.ca_id
                      WHERE crm_actividades.ca_tablero = "'.$_GET['tablero'].'" AND crm_actividades.ca_id = "'.$_GET['clone'].'"';
              if($tablero != 0){
                $sql .= ' AND cad_tipomiembro = "C" '; 
              }
              $sql.=' ORDER BY crm_actividades.ca_fecha ASC,crm_actividades.ca_hora ASC';
              $resultac = setq($sql);
              list($nmbal,$nmbact,$nmbcliente,$telcliente,$mailcliente,$mins,$vercomo,$lugar,$liga,$ubicacion,$descripcion) = $resultac->fetch_array();
            }
            echo '<div class="col-2 col-md-1">
              <label class="btn btn-secondary mr-1 mb-1 '.$sele.'"  onclick="setactivity('.$rowa['ca_id'].');">
                <input type="radio" name="options" id="option1" > <i class="'.$rowa['ca_icono'].'"></i>
              </label>
            </div>';
            echo '<input type="hidden" value="'.$rowa['ca_descripcion'].'" id="activ'.$rowa['ca_id'].'">';
          }
          echo '<input type="hidden" value="'.$valac.'" name="actividad" id="actividad">
          <input type="hidden" value="O" name="showas" id="showas">
        </div>
      </div>';

      $fini = date('Y-m-d',strtotime('+1 day')).'T'.date('H:').'00:00';
      if($_GET['act'] == 1 || $_GET['act'] == 6){
        $mins = 60;
        $actlugar = "";
      }else{
        $actlugar = ' style="display:none" ';
        $mins = 0;
      }
      echo '<div class="row">
        <div class="col-12 col-md-12">
            <label for"nmbatc"></label>
          <div class="alert alert-primary" role="alert" id="nmbactividad">'.$nmbal.'</div>
        </div>
        <div class="col-12 col-md-3">
          <div class="mb-5">
            <label for"nmbac">Nombra tu actividad</label>
            <input type="text" name="nmbac" value="'.$nmbact.'" id="nmbac" class="form-control" placeholder="Nombre de tu actividad" required="required" />
          </div>
        </div>
        <div class="col-12 col-md-3" >
          <div class="mb-5">
            <label for"fini">Fecha y hora de la actividad</label>
            <input type="datetime-local" name="fini" id="fini" value="'.$fini.'" class="form-control" placeholder="Cuando programas tu actividad" required="required" />
          </div>
        </div>
        <div class="col-6 col-md-2" '.$actlugar.' id="idduracion">
          <div class="mb-5">
            <label>Duración</label>
            <div class="input-group">
              <input type="text" class="form-control square" id="duracion" placeholder="Duración" aria-label="Duración en minutos de la actividad" name="duracion" required value="'.$mins.'" onfocus="this.select();" >
              <span class="input-group-text" id="basic-addon2">Min</span>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-4" '.$actlugar.' id="idffin">
          <div class="mb-5">
            <center><label>Mostrar como</label>
            <div class="input-group" style="display: block;">
              <div class="btn-group" data-toggle="buttons">
                <center>
                  <label class="btn-sm btn-danger text-white mr-1 mb-1 '.$showoc.'" id="vercomoo"  onclick="mostrarc(1)">
                    <input type="radio" name="vercomo" ><i class="fas fa-user-minus" style="color: #ffffff;"></i>Ocupado
                  </label>
                  <label class="btn-sm btn-success text-white mr-1 mb-1 '.$showdi.'" id="vercomod"  onclick="mostrarc(2)">
                    <input type="radio" name="vercomo" > <i class="fas fa-user-check" style="color: #ffffff;"></i>
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
            <input type="text" name="lugar" id="lugar" value="'.$lugar.'" class="form-control" placeholder="Donde se realizará tu actividad" />
          </div>
        </div>
        <div class="col-6 col-md-4" '.$actlugar.' id="idubicacion">
          <div class="mb-5">
          <label for"ffin">Ubicación</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-map-marked" style="color: #000000;"></i></span>
              <input type="text" name="ubicacion" id="ubicacion" value="'.$ubicacion.'" class="form-control" placeholder="Donde se realizará tu actividad" />
            </div>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <label for"ffin">Documento Adjunto</label>
          <div class="mb-5">
            <label id="projectinput7" class="file center-block form-control">
              <input type="file" name="adjunto" id="adjunto">
              <span class="file-custom"></span>
            </label>
          </div>
        </div>
        <!--<div class="col-6 col-md-4" '.$actlugar.' id="idliga">
          <div class="mb-5">
            <label for"ffin">Liga de acceso</label>
            <input type="text" name="liga" id="liga" value="'.$liga.'" class="form-control" placeholder="Liga de acceso a la reunión" />
          </div>
        </div>-->
        <div class="col-12 col-md-8">
          <div class="mb-5">
            <label for"ffin">Descripción de la cita</label>
            <textarea class="form-control" name="descripcion" rows="2" required="required" >'.$descripcion.'</textarea>
          </div>
        </div>
        <div class="col-12 col-md-12 alert alert-primary">Participantes</div>';
        if(!isset($_GET['id']) && $tablero != 0){
          echo'<div class="col-12 col-md-4" '.$style.'>
            <div class="mb-5">
              <label for"destino">Persona(s) a contactar</label>
              <input type="text" name="destino" id="destino" value="'.$nmbcliente.'" class="form-control" placeholder="Persona para contactar"  />
            </div>
          </div>
          <div class="col-6 col-md-4" '.$style.'>
            <div class="mb-5">
              <label for"telefono">Número telefónico</label>
              <input type="tel" name="telefono" id="telefono" value="'.$telcliente.'" class="form-control" placeholder="Telefóno del contacto" />
            </div>
          </div>
          <div class="col-6 col-md-4" '.$style.'>
            <div class="mb-5">
              <label for"correo">Correo de contacto</label>
              <input type="email" name="correo" id="correo" style="text-transform:lowercase;" value="'.$mailcliente.'" class="form-control" placeholder="correo de la contacto" />
            </div>
          </div>';
        }else{
          echo'<div class="col-12 col-md-4" hidden>
            <div class="mb-5">
              <label for"destino">Persona(s) a contactar</label>
              <input type="text" name="destino" id="destino" value="" class="form-control" placeholder="Persona para contactar"  />
            </div>
          </div>';
        }
        $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
        if($grupo != "ADMIN") {
          $sel = 'style="display: none;"';
          $inp = 'style="display: block;"';
        }else {
          $sel = 'style="display: block;"';
          $inp = 'style="display: none;"';
        }
        echo'<div class="col-12 col-md-4" id="encact">
          <div class="mb-5">
            <label for"resp">Encargado de la actividad</label>
            <select id="resp" onchange="setgmail();" name="responsable" class="form-control" '.$sel.'>
              <option value="0" disabled="">Selecciona un usuario </option>';
              $sql = 'SELECT u_id,u_gmail FROM usuarios WHERE u_grupo = "VENTAS"';
              $resultu = setq($sql);
              while($rowu = mysqli_fetch_array($resultu)){
                if($_SESSION['uid'] == $rowu['u_id']){ $seld = 'selected'; $gmail = $rowu['u_gmail'];}else $seld = "";
                echo  '<option value="'.$rowu['u_id'].'" '.$seld.'>'.$rowu['u_id'].'</option>';
              }
            echo'</select>
            <input name="responsable" class="form-control" type="text" value="'.$_SESSION['uid'].'" '.$inp.' disabled> 
          </div>
        </div>
        <div class="col-6 col-md-4" style="display:none">
          <div class="mb-5">
            <label for"correoresp" hidden>Correo del encargado</label>
            <input type="email" name="correoresp" style="text-transform:lowercase;" id="correoresp" value="'.$gmail.'" class="form-control" hidden readonly/>
          </div>
        </div>';
        echo'<div class="col-6 col-md-4 mt-2" id="btnadduser">
          <div class="mb-5">
            <button type="button" class="btn bg-grey bg-darken-2 text-white" style="float:right; background:gray" id="invitado"><i class="fas fa-plus" style="color: #ffffff;"></i> Agregar usuario </button>
          </div>
        </div>';
        if(isset($_GET['clone']) && busca($_GET['clone'],'crm_actividadesd','cad_tipomiembro = "I" AND cad_actividad','COUNT(*)') > 0){
          $sqli = 'SELECT cad_id,cad_user FROM crm_actividadesd WHERE cad_actividad = "'.$_GET['clone'].'" AND cad_tipomiembro = "I"';
          $resulti = setq($sqli);
          echo '<div class="row col-lg-12" id="invitados">';
          while($rowi = $resulti->fetch_array()){
            $f++;
            //echo '<input type="hidden" name="idi'.$rowi['cad_user'].'" value="'.$rowi['cad_id'].'"/>';
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
        $sqlcount = 'SELECT COUNT(*) c FROM usuarios WHERE u_empresa = "'.$_SESSION['emp'].'"';
        $resultcount = setq($sqlcount);
        list($con) = $resultcount->fetch_array();
        $con--;
        echo'<input type="hidden" id="countu" value="'.$con.'" />
        <input type="hidden" name="usu" value="0"/>
        <input type="hidden" id="dato" value="0" />
        
        <div class="col-12 col-md-12">
          <div class="mb-5">
            <center><buttn type="button" class="btn text-white" id="externo" style="background: teal;"><i class="fas fa-plus" style="color: #ffffff;"></i> Agregar invitado </button></center>
          </div>
        </div>';
        if(isset($_GET['clone']) && busca($_GET['clone'],'crm_actividadesd','cad_tipomiembro="E" AND cad_actividad','COUNT(*)') > 0){
          $sqle = 'SELECT cad_id,cad_miembro,cad_correo,cad_telefono FROM crm_actividadesd 
                    WHERE cad_actividad = "'.$_GET['clone'].'" AND cad_tipomiembro = "E"';
          $resulte = setq($sqle);
          while($rowe = $resulte->fetch_array()){
            $e++;
            //echo '<input type="hidden" name="ide'.$e.'" value="'.$rowe['cad_id'].'" />';
            echo'<div class="row col-lg-12" id="invext'.$e.'">
              <div class="col-12 col-md-4">
                <div class="mb-5">
                  <label>Invitado externo</label>
                  <input type="text" name="ext[]" id="ext[]" value="'.$rowe['cad_miembro'].'" class="form-control" '.$readon.'/>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="mb-5">
                  <label>Correo del invitado</label>
                  <input type="email" name="corr[]" style="text-transform:lowercase;" id="corr[]" value="'.$rowe['cad_correo'].'" class="form-control" '.$readon.'/>
                  </select>
                </div>
              </div>
              <div class="col-12 col-md-3">
                <div class="mb-5">
                  <label>Teléfono del invitado</label>
                  <input type="tel" maxlength="10" minlength="10" name="teli[]" id="teli[]" value="'.$rowe['cad_telefono'].'" class="form-control" '.$readon.'/>
                  </select>
                </div>
              </div>
              <div class="col-12 col-md-1">
                <a class="btn btn-sm btn-danger text-white" title="Quitar" onclick="removeinvext('.$e.')"><i class="fa fa-trash"></i> Eliminar</a>
              </div>
            </div>';
          }
        }
        echo'<div class="row col-lg-12" id="externos">
        </div>

        <div class="col-12 col-md-12">
          <center><button role="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button></center>
        </div>
      </div>
    </form>
  </div>';
?>