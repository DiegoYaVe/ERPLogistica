<?php
//ini_set('display_errors', 1);
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolut_ceo');
include('../funciones.php');
$empresa = $_POST['empresa'];

$salida = "";
//$query = 'SELECT * FROM cfdi_unidades ORDER BY cu_nmb ASC';
    $salida = '<div class="table table-responsive table-hover">
                <table class="table table-striped table-bordered table-hover" width="100%">
                <thead class="thead bg-primary text-white h5">
                <tr>
                  <th width="10%" class="pb-1">Seleccionar</th>
                  <th width="30%" class="pb-1">Nombre</th>
                  <th width="30%" class="pb-1">Alias</th>
                  <th width="20%" class="pb-1">Almacen</th>
                </tr>
               </thead>
               <tbody>';

  if(isset($_POST['consulta']) && !empty($_POST['consulta'])){

    $q = mb_strtoupper(trim($_POST['consulta']));
    if(strlen($q) > 2)
      $query = 'SELECT * FROM crm_clientes WHERE c_nmb LIKE "%'.$_POST['consulta'].'%"
                OR c_alias LIKE "%'.$_POST['consulta'].'%" OR c_apellidos LIKE "%'.$_POST['consulta'].'%" ORDER BY c_nmb ASC';
  }
  else $salida.="<tr><td colspan='3'><center>Introduzca un valor para busqueda</center></td></tr>";
  if(isset($query)){
    $resultado = setq($query);
    $i = 0;
    if($resultado->num_rows > 0){
      while($row = $resultado->fetch_assoc()){
        $i++;

        echo '<input type="hidden" id="name'.$row['c_id'].'" value="'.$row['c_alias'].'">';
        $almacen = busca($row['c_origen'],'almacenes','a_id','a_nmb');
        if(!$almacen) $almacen = "NO DEFINIDO";

        $salida.='<tr>
        <td class="align-middle">
          <button onfocus="setactive('.$row['c_id'].');"  id="butt'.$row['c_id'].'" class="btn btn-primary" onclick="setprod('.$row['c_id'].');"><i class="fas fa-crosshairs" style="color: #ffffff;"></i></button>
        </td>
        <td class="align-middle">'.$row['c_nmb'].' '.$row['c_apellidos'].'</td>
        <td class="align-middle">'.$row['c_alias'].'</td>
        <td class="align-middle">'.$almacen.'</td>
        <td class="number-align">';

      $salida.='</td>
        </tr>';
      }
      $salida.='</tbody></table>';
    ?>
      <script>
      document.onkeydown = checkKey;
      function checkKey(e) {
          e = e || window.event;
          if (e.keyCode == '38') { //arrow up
            var event = document.createEvent("HTMLEvents");
            var evtName = (typeof(type) === "string") ? "key" + type : "keydown";
            event.initEvent(evtName, true, false);
            event.keyCode = "9";

            document.dispatchEvent(event);
//            simulateKey("9","press");
          }
          else if (e.keyCode == '40') {  //arrow down
            window.dispatchEvent(new KeyboardEvent('keydown', {
              key: "Tab",
              keyCode: 9,
              code: "Tab",
              which: 9,
              shiftKey: false,
              ctrlKey: false,
              metaKey: false
            }));
                          alert("sioino");

          }
      }
      </script>
    <?php
    }
    else{
      $salida = '<tr><td colspan="3"><center>NO hay coincidencias con la busqueda</center></td></tr>';
    }
  }

  echo $salida;
  //Finaliza tabla din�mica de registros
?>
<script>

function simulateKeyPress(character) {
  jQuery.event.trigger({
    type: 'keypress',
    which: character.charCodeAt(9)
  });
}
  function setprod(cliente){
    var namecliente = document.getElementById("name" + cliente).value;

    opener.document.getElementById("cliente").value = namecliente;
    opener.document.getElementById("cliente").focus();
    opener.document.getElementById("cliente").select();
    opener.document.actualizaclie.submit();

    window.close();
  }

  function setactive(){

  }
</script>