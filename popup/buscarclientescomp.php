<?php
//ini_set('display_errors', 1);
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolut_ceo');
include('../funciones.php');

$salida = "";
//$query = 'SELECT * FROM cfdi_unidades ORDER BY cu_nmb ASC';
    $salida = '<div class="table table-responsive table-hover">
                <table class="table table-striped table-bordered table-hover" width="100%">
                <thead class="thead bg-primary text-white h6">
                <tr>
                  <th width="5%" class="pb-1">Seleccionar</th>
                  <th width="15%" class="pb-1">Alias</th>
                  <th width="10%" class="pb-1">RFC</th>
                  <th width="40%" class="pb-1">Razón Social</th>
                  <th width="10%" class="pb-1">CP</th>
                  <th width="20%" class="pb-1">Regimen</th>
                </tr>
               </thead>
               <tbody>';

  if(isset($_POST['consulta']) && !empty($_POST['consulta'])){

    $q = mb_strtoupper(trim($_POST['consulta']));
    if(strlen($q) > 2)
      $query = 'SELECT * FROM crm_clientes
                INNER JOIN crm_direcciones ON c_id = cd_cliente
                INNER JOIN crm_fiscales ON c_id = cf_cliente
                WHERE c_estatus = "A" AND
                (c_nmb LIKE "%'.$_POST['consulta'].'%"
                OR c_alias LIKE "%'.$_POST['consulta'].'%"
                OR c_apellidos LIKE "%'.$_POST['consulta'].'%"
                OR cf_nmfiscales LIKE "%'.$_POST['consulta'].'%"
                OR cf_rfc LIKE "%'.$_POST['consulta'].'%" ) AND cf_direccion = cd_id';
  }
  else $salida.="<tr><td colspan='3'><center>Introduzca un valor para busqueda</center></td></tr>";
  if(isset($query)){
    $resultado = setq($query);
    $i = 0;
    if($resultado->num_rows > 0){
      while($row = $resultado->fetch_assoc()){
        $i++;

        echo '<input type="hidden" id="idcl'.$row['cf_id'].'" value="'.$row['c_id'].'">';
        echo '<input type="hidden" id="idfis'.$row['cf_id'].'" value="'.$row['cf_id'].'">';
        $almacen = busca($row['c_origen'],'almacenes',' a_id','a_nmb');
        if(!$almacen) $almacen = "NO DEFINIDO";

        $salida.='<tr>
        <td class="align-middle">
          <button id="butt'.$row['cf_id'].'" class="btn btn-primary" onclick="setprod('.$row['cf_id'].');"><i class="icon-crosshairs"></i></button>
        </td>
        <td class="align-middle">'.$row['c_alias'].'</td>
        <td class="align-middle">'.$row['cf_rfc'].'</td>
        <td class="align-middle">'.$row['cf_razonsocial'].'</td>
        <td class="align-middle">'.$row['cd_cp'].'</td>
        <td class="align-middle">'.$row['cf_regimen'].'</td>
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
  //Finaliza tabla dinámica de registros
?>
<script>

function simulateKeyPress(character) {
  jQuery.event.trigger({
    type: 'keypress',
    which: character.charCodeAt(9)
  });
}
  function setprod(cliente){
    var namecliente = document.getElementById("idcl" + cliente).value;
    var namefiscal = document.getElementById("idfis" + cliente).value;

    opener.document.getElementById("cliente").value = namecliente;
    opener.document.getElementById("fiscales").value = namefiscal;
    opener.document.setfacliente.submit();

    window.close();
  }

</script>