<?php
ini_set('display_errors', 1);
class temporizadorpps
{
    var $model;
    var $view;
    function __construct()
    {
        $this->model = new modeltemporizadorpps(isset($obj));
    }

    function index()
    {
        $this->view = new viewtemporizadorpps($this->model);
        $this->view->browse();
    }
}

class modeltemporizadorpps
{

    function select($id)
    {
        $sql = "SELECT * FROM usuarios WHERe u_id = '" . $id . "' AND u_empresa = '" . $_SESSION['emp'] . "'";
        $result = setqemojis($sql);
        $row = $result->fetch_array();
        $this->nuser = $row['u_nuser'];
        $this->id = $row['u_id'];
        $this->nmb = $row['u_nmb'];
        $this->apellidos = $row['u_apellidos'];
        $this->grupo = $row['u_grupo'];
        $this->correo = $row['u_correo'];
        $this->telefono = $row['u_telefono'];
        $this->avatar = $row['u_avatar'];
        $this->estatus = $row['u_estatus'];
        $this->nacimiento = $row['u_nacimiento'];
        $this->puesto = $row['u_puesto'];
        $this->mailcorp = $row['u_mailcorp'];
        $this->passcorp = $row['u_passcorp'];
        $this->aporte = $row['u_aporte'];
        $this->host = $row['u_host'];
        $this->puerto = $row['u_puerto'];
        $this->seguridad = $row['u_seguridad'];
        $this->contraseñacorp = $row['u_contraseñacorp'];
        $this->remitente = $row['u_remitente'];
        $this->color = $row['u_color'];
        $this->saludo = $row['u_saludo'];
        $this->notificaciones = $row['u_notificaciones'];

    }
    function result($page, $nmb, $grupo, $estatus)
    {
        $pagenum = 50;
        $sql = 'SELECT * FROM usuarios WHERE ';
        if ($estatus != "T")
            $sql .= 'u_estatus = "' . $estatus . '" ';
        else
            $sql .= 'u_estatus != "" ';
        if ($nmb)
            $sql .= ' AND (u_nmb LIKE "%' . $nmb . '%" OR u_nuser LIKE "%' . $nmb . '%" OR u_apellidos  LIKE "%' . $nmb . '%" OR u_id LIKE "%' . $nmb . '%")';
        if ($grupo)
            $sql .= ' AND u_grupo = "' . $grupo . '" ';
        $sql .= ' AND u_empresa = "' . $_SESSION['emp'] . '" ORDER BY u_id ASC';
        $sql .= ' LIMIT ' . ($pagenum * $page) . ',' . $pagenum;

        $this->result = setq($sql);
        $this->resultt = setq($sql);
    }
    function setdata($nuser, $id, $nmb, $apellidos, $correo, $telefono, $puesto, $nacimiento, $grupo, $comment, $estatus, $mailcorp, $passcorp, $host, $puerto, $seguridad, $correopass, $remitente, $color, $notificaciones, $saludo)
    {
        $this->nuser = $nuser;
        $this->id = clearvmayus($id);
        $this->nmb = clearvmayus($nmb);
        $this->apellidos = clearvmayus($apellidos);
        $this->correo = clearvminus($correo);
        $this->telefono = clearvmayus($telefono);
        $this->puesto = clearvmayus($puesto);
        $this->nacimiento = clearvmayus($nacimiento);
        $this->grupo = clearvmayus($grupo);
        $this->aporte = clearvmayus($comment, false);
        $this->mailcorp = clearvminus($mailcorp);
        $this->passcorp = clearvmayus($passcorp, false);
        $this->host = clearvmayus($host, false);
        $this->puerto = clearvmayus($puerto, false);
        $this->seguridad = clearvmayus($seguridad, false);
        $this->correopass = clearvmayus($correopass, false);
        $this->remitente = clearvmayus($remitente, false);
        $this->color = clearvmayus($color);
        $this->saludo = $saludo;
        $this->notificaciones = $notificaciones;
        if ($estatus)
            $this->estatus = "A";
        else
            $this->estatus = "I";
    }
}

class viewtemporizadorpps
{
    var $model;
    function __construct($model)
    {
        //    include('header.php');
        ?>
        <script>
            function checkguardar() {
                document.getElementById("saveuser").innerHTML = "Guardando";
                document.getElementById("saveuser").disabled = true;
                return true;
            }
        </script>
        <?php
        $this->model = $model;
        $this->tipoc = array("C" => "Cliente", "P" => "Prospécto");
    }
    function browse()
    {
        ?>

        <style>

            .table-ext {
                border: 0px solid !important;
            }

            .full-height-container {
                /* border: 1px #gray; */
            }

            .timer-container {
                text-align: center;
                margin: 20px;
            }

            .timer {
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
            }

            .timer-segment {
                display: flex;
                align-items: center;
                flex-direction: column;
                padding: 5px;
            }

            .timer-number {
                font-size: 10rem;
            }

            .timer-label {
                font-size: 1.2rem;
            }

            .timer h2 {
                font-size: 1.5rem;
                font-weight: bold;
            }

            /* Estilos para botones (si los agregas) */
            .button {
                background-color: #007BFF;
                color: #fff;
                padding: 10px 20px;
                margin: 10px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }

            .button:hover {
                background-color: #0056b3;
            }

            /* Estilo base para los botones */
            .custom-button {
                /* background-color: #007BFF; */
                color: #fff;
                border: none;
                border-radius: 20px;
                cursor: pointer;
            }

            /* Estilo para el botón "Iniciar" */
            .custom-button.btn-success {
                font-size: 24px;
                padding: 10px 20px;
                margin-right: 10px;
            }

            /* Estilo para el botón "Pausa" */
            .custom-button.btn-secondary {
                font-size: 24px;
                padding: 10px 20px;
                margin-right: 10px;
            }

            /* Estilo para el botón "Detener" */
            .custom-button.btn-danger {
                font-size: 24px;
                padding: 10px 20px;
            }
            /* CSS para aumentar el tamaño de la fuente en SweetAlert */
            .my-title {
                font-size: 32px; /* Tamaño de fuente para el título */
            }
            .my-text {
                font-size: 27px; /* Tamaño de fuente para el texto */
            }

            /* Estilos table G*/
            #myTableG {
                width: 100%;
                border-collapse: collapse;
            }

            #myTableG th, #myTableG td {
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
                /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
            }

            #myTableG tbody tr:nth-child(odd) td {
                /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
            }

            #myTableG tbody tr:nth-child(even) td {
                /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
            }
            
            #myTableG {
                font-size: 12px; /* Cambia el tamaño de letra deseado */
            }
        </style>

        <!-- BUSCAMOS SI HAY UN REGISTRO DEL PROCESO Y ORDEN DE PRODUCCION PREVIO Y DIFERENTE A FINALIZADO -->
        <?php

        $grupou = busca($_SESSION['eid'], 'pr_empleados', 'pe_id', 'pe_rol');

        if($grupou == 1 || $grupou == 2){
            $btnreset = '
            <!-- <div class="col-12 d-flex justify-content-end"> -->
                <button id="resetButton" class="custom-button btn btn-secondary" onclick="resetContador();">
                    <i class="fas fa-history"></i>&nbsp;Reiniciar
                </button>
            <!-- </div> -->
            ';
        } else{
            $btnreset = '';
        }
        $btnreset = '
            <!-- <div class="col-12 d-flex justify-content-end"> -->
                <button id="resetButton" class="custom-button btn btn-secondary" onclick="resetContador();">
                    <i class="fas fa-history"></i>&nbsp;Reiniciar
                </button>
            <!-- </div> -->
            ';

        $sqle = 'SELECT ppx_id, ppx_accion, ppx_operador, ppx_horas, ppx_minutos, ppx_segundos FROM pr_procesoexe WHERE ppx_proceso = "' . $_GET['proceso'] . '" AND ppx_ordenp = "' . $_GET['op'] . '" ORDER BY ppx_id DESC LIMIT 1;';
        $resulte = setq($sqle);
        list($ppxid, $accion, $operador, $horas, $minutos, $segundos) = $resulte->fetch_array();
        if (empty($ppxid)) {
            $disI = "";
            $disP = "";
            $disF = "";

            $horas = "00";
            $minutos = "00";
            $segundos = "0";

            $clase = "success";
            $txtAlert = "Este proceso de esta orden de producción se encuentra listo para iniciarse.";
            /* $btnreset = ''; */
        } else {

            if (strlen($horas) < 2) {
                $horas = "0".$horas;
            }

            if (strlen($minutos) < 2) {
                $minutos = "0".$minutos;
            }

            if (strlen($segundos) < 2) {
                $segundos = "0".$segundos;
            }

            if($operador != $_SESSION['eid']){
                $disI = " disabled";
                $disP = " disabled";
                $disF = " disabled";
                $clase = "danger";
                $txtAlert = "Este proceso de esta orden de producción ya se encuentra iniciado por otro usuario";
            } else{
            if ($accion == "1") {
                $disI = " disabled";
                $disP = "";
                $disF = "";
                $clase = "primary";
                $txtAlert = "Este proceso de esta orden de producción se encuentra en ejecución.";  
            } else if ($accion == "3") {
                $disI = " disabled";
                $disP = "";
                $disF = "";
                $clase = "primary";
                $txtAlert = "Este proceso de esta orden de producción se encuentra en ejecución.";  
            } else if ($accion == "2") {
                $disI = "";
                $disP = " disabled";
                $disF = "";
                $clase = "dark";
                $txtAlert = "Este proceso de esta orden de producción se encuentra en pausa.";
            } else {
                $cantidadd = busca($_GET['op'], 'pr_articulosop', 'pra_estpa IN ("A", "N") AND pra_op', 'COUNT(*)');
                if($cantidadd > 0){
                    $disI = "";
                    $disP = "disabled";
                    $disF = "disabled";

                    $clase = "success";
                    $txtAlert = "Este proceso de esta orden de producción se encuentra listo para reaunudarse.";
                } else{
                    $disI = " disabled";
                    $disP = " disabled";
                    $disF = " disabled";
                    $clase = "danger";
                    $txtAlert = "Este proceso de esta orden de producción ya se encuentra finalizado.";
                }
            }
            }
        }


        //Buscamos los detalles del proceso y de la orden de producción
        $sqlprod = 'SELECT * FROM pr_procesosop WHERE pp_id = "'.$_GET['proceso'].'" AND pp_ordenp = "'.$_GET['op'].'"';
        $resultp = setq($sqlprod);
        $rowp = $resultp->fetch_array();

        $ordenp = busca($_GET['op'], 'pr_ordenprod', 'po_id', 'po_folio');
        $proceso = busca($rowp['pp_proceso'], 'pr_procesos', 'ppr_id', 'ppr_nmb');
        $operador = busca($rowp['pp_operador'], 'pr_empleados', 'pe_id', 'pe_nmb');

        $fini = fecha_formato($rowp['pp_fini'], true, false);

        if(empty($rowp['pp_ffin'])){
            $ffin = "No disponible";
        } else{
            $ffin = fecha_formato($rowp['pp_ffin'], true, false);
        }

        $obs = $rowp['pp_obs']
        ?>

        <div class="row" style="background: white;">
            <div class="col-4 mt-14 full-height-container">
                <table id="myTableG" class="table table-hover">
                    <thead class="thead-active text-black">
                        <tr>
                            <th colspan="2">
                                <center>Detalles del proceso</center>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="thead-active bg-primary text-white">
                            <td>Concepto</td>
                            <td>Descripción</td>
                        </tr>

                        <tr>
                            <td>Orden de producción:</td>
                            <td><?php echo $ordenp; ?></td>
                        </tr>
                        <tr>
                            <td>Proceso:</td>
                            <td><?php echo $proceso; ?></td>
                        </tr>
                        <tr>
                            <td>Operador:</td>
                            <td><?php echo $operador; ?></td>
                        </tr>
                        <tr>
                            <td>Fecha inicio:</td>
                            <td><?php echo $fini; ?></td>
                        </tr>
                        <tr>
                            <td>Fecha final:</td>
                            <td><?php echo $ffin; ?></td>
                        </tr>
                        <tr>
                            <td>Retroalimentación:</td>
                            <td><?php echo $obs; ?></td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <script>
                function btnTemporizador(op, proceso){
                    window.open("popup/temporizadorpps.php?op="+op+"&proceso="+proceso, "Temporizador", "width=1400, height=800, resizable=yes, scrollbars=yes");
                    /* window.open("popup/operador.php", "Temporizador", "width=1400, height=800, resizable=yes, scrollbars=yes"); */
                }
            </script>
            <?php
            $txtPar = "'17', '18'";
              echo '<button class="btn btn-sm btn-primary" onClick="btnTemporizador('.$txtPar.');">Prueba</button>';

        ?>
            <div class="col-8 full-height-container">
                <table class="table table-ext table-responsive">
                    <thead>
                        <tr>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="timer-container">
                                    <!-- <h2>Temporizador</h2> -->
                                    <div id="msj-alert" class="alert alert-<?php echo $clase; ?>" role="alert">
                                        <span id="mensaje"><?php echo $txtAlert; ?></span>
                                    </div>
                                    <!-- <?php /* echo $btnreset; */ ?> -->
                                    <div class="timer">
                                        <div class="timer-segment">
                                            <div class="timer-number" id="hours"><?php echo $horas; ?></div>
                                            <div class="timer-label">Horas</div>
                                        </div>
                                        <div class="timer-segment">
                                            <div class="timer-number">:</div>
                                            <div class="timer-label"></div>
                                        </div>
                                        <div class="timer-segment">
                                            <div class="timer-number" id="minutes"><?php echo $minutos; ?></div>
                                            <div class="timer-label">Minutos</div>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <input type="hidden" id="adj1" value="">
                                <input type="hidden" id="retro1" value="">
                                <input type="hidden" id="cantidad1" value="">
                                <input type="hidden" id="articuloSel" value="">
                                <input type="text" id="hidden-seconds" value="<?php echo $segundos; ?>">
                                <input type="text" id="tpid" value="<?php echo $ppxid;?>">
                                <input type="hidden" id="ordenprod" value="<?php echo $_GET['op']; ?>">
                                <input type="hidden" id="proceso" value="<?php echo $_GET['proceso']; ?>">
                                <button id="startButton" class="custom-button btn btn-success" <?php echo $disI; ?>><i
                                        class="fas fa-play-circle"></i>&nbsp;Iniciar</button>
                                <button id="pauseButton" class="custom-button bnt btn-secondary" <?php echo $disP; ?>><i
                                        class="fas fa-pause-circle"></i>&nbsp;Pausa</button>
                                <button id="stopButton" class="custom-button btn btn-danger" <?php echo $disF; ?>><i
                                        class="fas fa-stop-circle"></i>&nbsp;Detener</button>
                                <?php echo $btnreset; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <script>
            const hoursElement = document.getElementById("hours");
            const minutesElement = document.getElementById("minutes");
            const hiddenSecondsElement = document.getElementById("hidden-seconds");
            const startButton = document.getElementById("startButton");
            const pauseButton = document.getElementById("pauseButton");
            const stopButton = document.getElementById("stopButton");

            let timer;
            let running = false;
            let totalSeconds = 0;

            if(parseInt(hiddenSecondsElement.value) > 0){
                totalSeconds = hiddenSecondsElement.value;
            }

            startButton.addEventListener("click", function() {
                abrirPopup("I");
            });

            pauseButton.addEventListener("click", function() {
                abrirPopup("P");
            });

            stopButton.addEventListener("click", function() {
                abrirPopup("F");
            });
            /* pauseButton.addEventListener("click", pauseTimer);
            stopButton.addEventListener("click", stopTimer); */

            // Función para mostrar la alerta con el contador
            function showCountdownAlert() {
            updateCountdown(5);
            Swal.fire({
                title: 'Cuenta regresiva',
                text: 'Comenzando en ' + 5 + ' segundos',
                timer: 5 * 1000,
                timerProgressBar: true,
                showConfirmButton: false, // Oculta el botón "Ok"
                showCancelButton: true, // Muestra el botón de "Cancelar"
                cancelButtonColor: '#d33', // Color rojo
                cancelButtonText: 'Cancelar', // Texto del botón de "Cancelar"
                onOpen: () => {
                Swal.showLoading();
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.timer) {
                // La cuenta regresiva llegó a 0, ejecuta la función
                startTimer();
                }
            });
            }


        // Función para actualizar y mostrar la cuenta regresiva en SweetAlert
        function updateCountdown(seconds) {
            if (seconds >= 0) {
                Swal.update({
                    title: 'Cuenta regresiva',
                    text: 'Comenzando en ' + seconds + ' segundos',
                });

                setTimeout(() => {
                    updateCountdown(seconds - 1);
                }, 1000); // Actualiza cada segundo
            }
        }

        function abrirPopup(tipo){
            console.log("Tipo xd: "+tipo);
            var tpid = document.getElementById("tpid").value;
            if(!tpid){
                url = 'popup/settemporizador?tipo='+tipo+'&idp='+'<?php echo $_GET['op']; ?>'+'&proceso='+'<?php echo $_GET['proceso']; ?>';
            } else{
                url = 'popup/settemporizador?id='+tpid+'&tipo='+tipo+'&idp='+'<?php echo $_GET['op']; ?>'+'&proceso='+'<?php echo $_GET['proceso']; ?>';
            }
                // Abre Fancybox con los atributos especificados
                $.fancybox.open({
                    src: url,
                    type: 'ajax'
                });
        }

        window.addEventListener('datosEnviados', function (event) {
            var datosRecibidos = event.detail;
            var adj4 = datosRecibidos.adj3;
            var retro4 = datosRecibidos.retro3;
            var cantidad4 = datosRecibidos.cantidad3;
            var tipo = datosRecibidos.tipo;
            var articuloSel = datosRecibidos.articuloSel
            
            /* console.log("Tipo xd: "+tipo);] */
            // Ahora puedes utilizar estos valores en la pantalla principal
            document.getElementById("cantidad1").value = cantidad4;
            document.getElementById("adj1").value = adj4;
            document.getElementById("retro1").value = retro4;
            document.getElementById("articuloSel").value = articuloSel;
            if(tipo == "I"){
                showCountdownAlert();
            } else if(tipo == "P"){
                pauseTimer();
            } else{
                stopTimer();
            }
        });


            function startTimer() {
                var hours = Math.floor(totalSeconds / 3600);
                var minutes = Math.floor((totalSeconds % 3600) / 60);
                var seconds = document.getElementById("hidden-seconds").value;
                var ordenprod = document.getElementById("ordenprod").value;
                var proceso = document.getElementById("proceso").value;
                var tpid = document.getElementById("tpid");
                var msja = document.getElementById("msj-alert");
                var mensaje = document.getElementById("mensaje");

                var cantidad = document.getElementById("cantidad1").value;
                var retro = document.getElementById("retro1").value;
                var adj = document.getElementById("adj1").value;
                var articuloSel = document.getElementById("articuloSel").value;
                
                if (!running) {
                    timer = setInterval(updateTimer, 1000);
                    running = true;
                    startButton.disabled = true;
                    pauseButton.disabled = false;
                    stopButton.disabled = false;

                    $.ajax({
                        dataType: "json",
                        type: "POST",
                        url: "query/tiemposprod.php",
                        data: {
                            "iniciar": 1,
                            "proceso": proceso,
                            "ordenprod": ordenprod,
                            "horas": hours,
                            "minutos": minutes,
                            "segundos": seconds,
                            "tpid": tpid.value,
                            "retro": retro,
                            "adj": adj,
                            "cantidad": cantidad,
                            "articuloSel": articuloSel
                        },
                        success: function (data) {
                            tpid.value = data;
                            msja.classList.remove("alert-success");
                            msja.classList.remove("alert-dark");
                            msja.classList.add("alert-primary");
                            mensaje.innerHTML = "Este proceso de esta orden de producción se encuentra en ejecución.";
                        }
                    });


                }
            }

            function pauseTimer() {
                if (running) {
                    var hours = Math.floor(totalSeconds / 3600);
                    var minutes = Math.floor((totalSeconds % 3600) / 60);
                    var seconds = document.getElementById("hidden-seconds").value;
                    var ordenprod = document.getElementById("ordenprod").value;
                    var proceso = document.getElementById("proceso").value;
                    var tpid = document.getElementById("tpid");
                    var msja = document.getElementById("msj-alert");
                    var mensaje = document.getElementById("mensaje");

                    var cantidad = document.getElementById("cantidad1").value;
                    var retro = document.getElementById("retro1").value;
                    var adj = document.getElementById("adj1").value;

                    clearInterval(timer);
                    running = false;
                    startButton.disabled = false;
                    pauseButton.disabled = true;
                    stopButton.disabled = false;

                    $.ajax({
                        dataType: "json",
                        type: "POST",
                        url: "query/tiemposprod.php",
                        data: {
                            "pausar": 1,
                            "proceso": proceso,
                            "ordenprod": ordenprod,
                            "horas": hours,
                            "minutos": minutes,
                            "segundos": seconds,
                            "tpid": tpid.value,
                            "retro": retro,
                            "adj": adj,
                            "cantidad": cantidad
                        },
                        success: function (data) {
                            tpid.value = data;
                            msja.classList.remove("alert-primary");
                            msja.classList.add("alert-dark");
                            mensaje.innerHTML = "Este proceso de esta orden de producción se encuentra en pausa.";
                        }
                    });
                }
            }

            function stopTimer() {
                var hours = Math.floor(totalSeconds / 3600);
                var minutes = Math.floor((totalSeconds % 3600) / 60);
                var seconds = document.getElementById("hidden-seconds").value;
                var ordenprod = document.getElementById("ordenprod").value;
                var proceso = document.getElementById("proceso").value;
                var tpid = document.getElementById("tpid");
                var msja = document.getElementById("msj-alert");
                var mensaje = document.getElementById("mensaje");

                var cantidad = document.getElementById("cantidad1").value;
                var retro = document.getElementById("retro1").value;
                var adj = document.getElementById("adj1").value;
                var articuloSel = document.getElementById("articuloSel").value;

                clearInterval(timer);
                running = false;
                updateDisplay();
                startButton.disabled = false;
                pauseButton.disabled = true;
                stopButton.disabled = true;

                $.ajax({
                    dataType: "json",
                    type: "POST",
                    url: "query/tiemposprod.php",
                    data: {
                        "finalizar": 1,
                        "proceso": proceso,
                        "ordenprod": ordenprod,
                        "horas": hours,
                        "minutos": minutes,
                        "segundos": seconds,
                        "tpid": tpid.value,
                        "retro": retro,
                        "adj": adj,
                        "cantidad": cantidad,
                        "articuloSel": articuloSel
                    },
                    success: function (data) {
                        if(data == "e1"){
                            /* tpid.value = data; */
                            msja.classList.remove("alert-primary");
                            msja.classList.remove("alert-dark");
                            msja.classList.remove("alert-danger");
                            msja.classList.add("alert-success");
                            mensaje.innerHTML = "Este proceso de esta orden de producción se encuentra listo para reaunudarse.";
                        } else{
                            tpid.value = "";
                            msja.classList.remove("alert-dark");
                            msja.classList.remove("alert-primary");
                            msja.classList.add("alert-danger");
                            mensaje.innerHTML = "Este proceso de esta orden de producción ya se encuentra finalizado.";
                            startButton.setAttribute("disabled", true);
                            pauseButton.setAttribute("disabled", true);
                            stopButton.setAttribute("disabled", true);
                            totalSeconds = 0;
                            hiddenSecondsElement.value = 0;
                        }
                    }
                });
            }

            function updateTimer() {
                totalSeconds++;

                // Reiniciar segundos a 0 cuando alcanza 60
                if (totalSeconds % 60 === 0) {
                    hiddenSecondsElement.value = 0;
                } else {
                    hiddenSecondsElement.value = totalSeconds % 60;
                }

                updateDisplay();
            }


            function updateDisplay() {
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const displayHours = hours < 10 ? "0" + hours : hours;
                const displayMinutes = minutes < 10 ? "0" + minutes : minutes;

                hoursElement.textContent = displayHours;
                minutesElement.textContent = displayMinutes;
            }

            function resetContador(){
                var ordenprod = document.getElementById("ordenprod").value;
                var proceso = document.getElementById("proceso").value;

                $.ajax({
                        dataType: "json",
                        type: "POST",
                        url: "query/tiemposprod.php",
                        data: {
                            "reset": 1,
                            "proceso": proceso,
                            "ordenprod": ordenprod
                        },
                        success: function (data) {
                            window.location.href = "?modulo=temporizadorpps&accion=index&op="+"<?php echo $_GET['op']; ?>"+"&proceso="+"<?php echo $_GET['proceso']; ?>";
                        }
                    });

            }

        </script>


        <?php

    }

}
?>