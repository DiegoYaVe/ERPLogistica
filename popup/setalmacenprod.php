<?php 
session_start();
include_once('../funciones.php');

$idalmacen = $_GET['id'];

if($idalmacen){
  $sql = 'SELECT * FROM pr_almacenes WHERE pa_id = "'.$idalmacen.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
}

if(!$idalmacen) $titulo = 'Agregar'; else $titulo = 'Actualizar';

if($row['pa_id']) $accion = 'update'; else $accion = 'insert';


echo '
  <div class="container">
    <div class="alert alert-primary">'.$titulo.' almacen de producción</div>
    <form method="post" action="?modulo=almacenesprod&accion='.$accion.'" enctype="multipart/form-data">
    <div class="row">
      <input type="hidden" value="'.$row['pa_id'].'" name="id" />
      <div class="col-md-8 mb-5">
        <label>Nombre del almacen: </label>
        <input type="text" id="nmb" class="form-control focus mayus" placeholder="Dale un nombre a tu almacen" name="nmb" value="'.$row['pa_nmb'].'" required="required" >
      </div>
      <div class="col-md-2 mb-5">
        <label>Tipo de almacen:</label>';
        if($row['pa_tipoa'] == "I") $talmacen = "checked"; else $talmacen = "";
        echo '<input type="checkbox" name="talmacen" '.$talmacen.' class="flipswitchalmdos" />
      </div>
      <div class="col-md-2 mb-5">
        <label>Vendible:</label>';
        if($row['pa_vendible']) $vendible = "checked"; else $vendible = "";
        echo '<input type="checkbox" name="vendible" '.$vendible.' class="flipswitchalm" />
      </div>
      </div>

      <div class="row">
      <div class="col-md-6 mb-5">
        <label>Calle:</label> 
        <input type="text" name="calle" value="'.$row['pa_calle'].'" class="form-control" />
      </div>
      <div class="col-md-3 mb-5">
        <label>Num Ex.:</label> 
        <input type="text" name="nume" value="'.$row['pa_nume'].'" class="form-control" />
      </div>
      <div class="col-md-3 mb-5">
        <label>Num Int.:</label> 
        <input type="text" name="numi" value="'.$row['pa_numi'].'" class="form-control" />
      </div>
      </div>

      <div class="row">
      <div class="col-md-4 mb-5">
        <label>Colonia:</label> 
        <input type="text" name="colonia" value="'.$row['pa_colonia'].'" class="form-control" />
      </div>
      <div class="col-md-4 mb-5">
        <label>Ciudad:</label> 
        <input type="text" name="ciudad" value="'.$row['pa_ciudad'].'" class="form-control" />
      </div>
      <div class="col-md-4 mb-5">
        <label>Estado:</label> 
        <input type="text" name="estado" value="'.$row['pa_estado'].'" class="form-control" />
      </div>
      </div>

      <div class="row">
      <div class="col-md-2 mb-5">
        <label>Pais:</label> 
        <input type="text" name="pais" value="'.$row['pa_pais'].'" class="form-control" />
      </div>
      <div class="col-md-2 mb-5">
        <label>CP:</label> 
        <input type="text" name="cp" value="'.$row['pa_cp'].'" class="form-control" />
      </div>
      <div class="col-md-2 mb-5">
        <label>Telefono:</label> 
        <input type="number" name="telefono" value="'.$row['pa_telefono'].'" class="form-control"  />
      </div>
      <div class="col-md-3 mb-5">
        <label>Correo:</label> 
        <input type="email" name="correo" value="'.$row['pa_correo'].'" class="form-control" />
      </div>';
      if (!$row['pa_img'])
        echo '
        <div class="col-md-3 mb-5">
          <label>Imagen:</label> 
          <input type="file" name="img" class="form-control" />
        </div>';  
      else
        echo '
        <div class="col-md-3 mb-5">
          <label>Imagen</label><br>
          <img src="'.$row['pa_img'].'" alt="" class="width-100 img-thumbnail">
          <button type="button" onclick="borrarimg('.$idalmacen.');" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
        </div>
        <script class="">
          function borrarimg(id){
            var c = confirm("¿Estás seguro de eliminar la imagen del almacen?");
            if(c){
              window.location.href="?modulo=almacenes&accion=borrarimg&id="+id;
            }
          }
        </script>
        '; 

      echo '
      </div>
      <div class="col-md-12 text-xs-center mb-5" style="display: flex; justify-content: center;">
        <!-- <label>Guardar: </label> <br> -->
        <button type="submit" class="btn btn-primary"><i class="fa fa-save">&nbsp;Guardar</i>
      </div>
    </form>
  </div>';

?>