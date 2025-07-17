<?php
  session_start();
  include_once('../funciones.php');
?>
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
</script>
<?php
  echo'
  <div class="modal-dialog" style="max-width: 60%;">
    <form action="?modulo=grupo&accion=update&id='.$_GET['id'].'" method="post" onsubmit="checksubmit();">';
      echo'<center>
        <div class="col-10 col-md-12">
          <label class="modal-title text-text-bold-600" id="myModalLabel33">Cambiar información de '.$nmb.'</label>
        </div>
        <div class="col-12 col-md-12" >
          <div class="mb-5">
            <label>Nombre del grupo: </label>
            <input type="text" placeholder="Grupo" value="'.$row['g_nmb'].'" name="nmb" class="form-control" onfocus="this.select();" required="required" />
          </div>
        </div>
        <div class="col-12 col-md-12" >
          <div class="mb-5">
            <input type="checkbox" name="estatus" checked="checked" class="flipswitch" />
          </div>
        </div>
        <div class="col-12 col-md-12">
          <div class="mb-5">
            <label>Empresas </label>
          </div>
        </div>
        <div class="col-12 col-md-12">
          <div class="table-responsive">
            <table width="100%" class="mb-0 table table-hover">';
              $sqlemp = 'SELECT e_id,e_nmb FROM empresas WHERE e_estatus = "A"';
              $resultemp = setq($sqlemp);
              while($rowemp = $resultemp->fetch_array()){
                if(busca($rowemp['e_id'],'grupos_empresas','ge_estatus = "A" AND ge_empresa','COUNT(*)') > 0) $ch = 'checked="checked"';
                else $ch = '';
                echo'<tr>
                    <td width="5%"><input type="checkbox" name="emp'.$rowemp['e_id'].'" '.$ch.' /></td>
                    <td><label>'.$rowemp['e_nmb'].'</label></td>
                    <td width="5%"><input type="checkbox" name="est'.$rowemp['e_id'].'" '.$ch.' class="flipswitch" /></td>
                    </tr>';
              }
              echo '
            </table>
          </div>
        </div>
        <div class="col-12 col-md-12">
          <center><button type="button" class="btn btn-primary" id="guardar" '.$disable.' onclick="cobros();"><i class="icon-bank"></i> GUARDAR </button></center>
        </div>
      </center>
    </form>
  </div>';
  /*<div class="col-12 col-md-4">
<div class="mb-5">
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-5">
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-5">
</div>
</div> */
?>