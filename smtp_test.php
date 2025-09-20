<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <meta content="width=device-width,initial-scale=1" name="viewport">
        <style>
          .parrafo-estandar {
            color: #7C8898;;
            font-size: 15px;
            text-align: justify;
            margin-bottom:20px;
          }
          .parrafo-estandar-left {
            color: #7C8898;;
            font-size: 15px;
            text-align: left;
            margin-bottom:20px;
          }
          .container-oferta{
            width:250px;
            margin: auto;
          }
          .center-content{
            text-align: center;
          }
          .text-gray{
            color:#7C8898;
          }
        </style>
    </head>
    <body>
    <div class="container" style="width: 28rem; margin: auto; border: 1px gray solid; border-radius: 5px;">
        <div style="width: 28rem; margin: auto; text-align: left; background-color:#7C8898;">
          ${img}
        </div>
        <div style="width: 28rem; margin: auto; text-align: center;">
            <div style="padding: 25px;">
                <h2 style="text-align: center; font-size: 28px; color: #42505C;">
                  ACEPTA TU SOLICITUD.
                </h2>
                <p class="parrafo-estandar-left" style="margin-top:30px;">
                 Estimado(a) estudiante:
                </p>
                <p class="parrafo-estandar-left">
                  Tu postulación para la convocatoria: ${programa} fue aceptada por el Comité de la Universidad para continuar con tus estudios.
                </p>
                <p class="parrafo-estandar-left">
                  Debes ingresar a la plataforma para revisar la información acerca de la resolución y aceptar el apoyo para finalizar exitosamente tu proceso de solicitud.
                </p>
                <p class="parrafo-estandar-left">
                  <strong>IMPORTANTE:</strong> ${textoImportante}
                </p>
                <hr>
                <p class="parrafo-estandar">
                  Detalles:
                </p>
                <div class="container-oferta text-gray">
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr class="text-gray">
                      <td align="left" width="50%">
                        <b>Nombre del apoyo</b>
                      </td>
                      <td align="right" width="50%">
                        <b>%</b>
                      </td>
                    </tr>
                  </table><hr>
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr class="text-gray">
                      <td align="left" width="50%">
                        <div>${programa}</div>
                      </td>
                      <td align="right" width="50%">
                        <div>${porcentaje}</div>
                      </td>
                    </tr>
                  </table>
                </div>
                <p class="parrafo-estandar btn-center" style="text-align:center;">
                  <a href="${linkBoton}"
                    target="_blank"
                    style="background:#5A72EA; color:#ffffff; text-decoration:none; 
                            display:inline-block; padding:12px 20px; border-radius:20px; 
                            font:bold 16px Arial, sans-serif;">
                    Aceptar mi apoyo
                  </a>
                </p>
                <p class="parrafo-estandar">
                  <b>Mejores deseos, <br>
                  ${nombreuniversidad}</b>
                </p>
                <p class="parrafo-estandar center-content">
                  Este es un mensaje generado automáticamente, no responder
                </p>
            </div>
            <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:20px;">
              <tr>
                <td align="center">
                  <table width="60%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td width="50%">
                        <div style="margin-bottom:8px;">
                          <img src="https://storage.googleapis.com/assets-wispo/Logo_Gray.png" style="width:3rem; margin-right: 2px;">
                        </div>
                        <span style="font-size:10px;" class="text-gray">
                          Para saber más sobre cómo podemos optimizar el respaldo financiero de tu universidad, contáctanos en:
                        </span>
                      </td>
                      <td width="50%" style="padding-left:20px;" class="text-gray">
                        <span>
                          Contacto
                        </span>
                        <p style="font-size:10px; margin:5px 0px;">
                          ${correoContacto}
                        </p>
                        <p style="font-size:10px; margin:5px 0px;">
                          ${direccionContacto}
                        </p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <div style="background-color:rgba(124, 136, 152, 0.5); color:#FFF; padding: 5px 20px 10px 20px;">
              <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="center">
                    <table width="50%" cellpadding="0" cellspacing="0">
                      <tr>
                        <td width="10%" style="vertical-align: middle;">
                          <img src="https://storage.googleapis.com/assets-wispo/House_Lock_Gray.png" style="width: 2.5rem; margin-right: 2px;">
                        </td>
                        <td width="90%" style="vertical-align: middle;">
                          <h4 style="text-align: left; padding: 0px; margin: 0px; color:#FFF;">¡Antes de dar clic!</h4>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
              <p style="font-size: 12px; text-align: center; margin-top:5px;">Laudex nunca te preguntará por información personal por correo.
                Al dar clic en alguna liga de este correo, la dirección debe contener "laudex.mx".</p>
              <p style="font-size: 12px; text-align: center; margin-bottom:0px;">No responda este correo. Fue generado automáticamente.</p>
            </div>
            <div style="padding: 10px 30px 10px 30px;">
              <p style=" font-size: 10px; text-align: center; margin-top:0px; margin-bottom:0px; color:#828282;">
                © 2024 Wispo, Todos los derechos reservados.<br>
                Wispo es una marca registrada.<br>
                Los términos y condiciones, características, soporte, precios y servicios están sujetos a cambio sin previo aviso.
              </p>
            </div>
        </div>
    </div>
    </body>
  </html>
