<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
$estatus = $_POST['estatus']; //Estatus = A y Estatus = P
$ordenp = $_POST['ordenp'];
$proceso = $_POST['proceso'];
if(isset($_SESSION['uid'])){
    $tuser = 'U';
    $operador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else{
    $tuser = 'O';
    $operador = $_SESSION['eid'];
}
$respuesta = 0;


$estatusart = busca($ordenp, 'pr_articulosop', 'pra_pa = "'.$proceso.'" AND pra_seleccionado = "1" AND pra_op', 'COUNT(*)');
if($estatusart > 0){
    //Verificamos si los que cambiamos los artículos somos nosotros u otro operador
    $response = busca($ordenp, 'pr_articulosop', 'pra_pa = "'.$proceso.'" AND pra_operador = "'.$operador.'" AND pra_toperador = "'.$tuser.'" AND pra_seleccionado = "1" AND pra_op', 'COUNT(*)');
    if($response > 0){
        //Es el mismo usuario y podemos continuar seleccionando
        //Dependiendo el estatus que recibimos por post vamos a actualizar el estatus de los articulos
        if($estatus == 0){
            $sqlf = 'pra_operador = NULL, pra_toperador = NULL';
        } else{
            $sqlf = 'pra_operador = "'.$operador.'", pra_toperador = "'.$tuser.'"';
        }

        //Inico de la verificación si se presionó el boton de seleccionar todos
        if($_POST['all'] == 0){
            $sqlg = 'pra_op = "'.$ordenp.'" AND pra_pa = "'.$proceso.'"';
        } else{
            $sqlg = 'pra_id = "'.$_POST['all'].'"';
        }
        //Fin de la verificación si se presionó el boton de seleccionar todos


        $sql = 'UPDATE pr_articulosop SET '.$sqlf.', pra_seleccionado = "'.$estatus.'" WHERE '.$sqlg;
        setq($sql);        
        $respuesta = 2;
     } else{
        //Es diferente el usuario
        $respuesta = 0;
    }

} else{
    //Dependiendo el estatus que recibimos por post vamos a actualizar el estatus de los articulos
    if($estatus == 0){
        $sqlf = 'pra_operador = NULL, pra_toperador = NULL';
    } else{
        $sqlf = 'pra_operador = "'.$operador.'", pra_toperador = "'.$tuser.'"';
    }

    //Inico de la verificación si se presionó el boton de seleccionar todos
    if($_POST['all'] == 0){
        $sqlg = 'pra_op = "'.$ordenp.'" AND pra_pa = "'.$proceso.'"';
    } else{
        $sqlg = 'pra_id = "'.$_POST['all'].'"';
    }
    //Fin de la verificación si se presionó el boton de seleccionar todos


    $sql = 'UPDATE pr_articulosop SET '.$sqlf.', pra_seleccionado = "'.$estatus.'" WHERE '.$sqlg;
    setq($sql);
    $respuesta = 1;
}

echo $respuesta;
?>