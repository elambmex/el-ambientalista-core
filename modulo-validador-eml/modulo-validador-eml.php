<?php
/**
 * Plugin Name: El Ambientalista - Módulo de Verificación
 * Description: Añade un shortcode [verificador_elambmex] para verificar correos contra un CSV local y recibir reportes .eml.
 * Version: 1.2
 * Author: El Ambientalista Post
 */

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente.
}

// === PARTE 1: REGISTRAR EL SHORTCODE ===
// Imprime el HTML, CSS y JS en la página pública.

add_shortcode('verificador_elambmex', 'elamb_verificador_shortcode_html');

function elamb_verificador_shortcode_html() {
    ob_start();
    ?>
    
    <style>
        .elamb-verifier-container {
            font-family: 'Georgia', serif;
            line-height: 1.6;
            background-color: #ffffff;
            color: #111111;
            max-width: 680px;
            margin: 40px auto;
            padding: 40px;
            border-top: 5px solid #3c8d0d;
            background-color: #fdfdfd;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .elamb-verifier-container h1 {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 28px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .elamb-verifier-container h3 {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 22px;
            font-weight: 700;
            margin-top: 30px;
        }
        .elamb-verifier-container p, 
        .elamb-verifier-container label {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .elamb-verifier-container a {
            color: #3c8d0d;
            text-decoration: none;
            border-bottom: 1px dotted #3c8d0d;
        }
        .elamb-verifier-container a:hover {
            color: #111;
            border-bottom-style: solid;
        }
        .elamb-verifier-form-group {
            display: flex;
            margin-bottom: 20px;
        }
        .elamb-verifier-container input[type="text"] {
            flex-grow: 1;
            padding: 12px;
            font-size: 16px;
            font-family: Arial, sans-serif;
            border: 1px solid #ccc;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }
        .elamb-verifier-container input[type="file"] {
            font-family: Arial, sans-serif;
            font-size: 14px;
            width: 100%;
            margin-bottom: 15px;
        }
        .elamb-verifier-container button {
            padding: 12px 20px;
            font-size: 16px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            background-color: #3c8d0d;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 0 4px 4px 0;
            transition: background-color 0.2s;
        }
        .elamb-verifier-container button:hover {
            background-color: #2f6a0a;
        }
        .elamb-verifier-container form button {
            border-radius: 4px;
        }
        .elamb-verifier-message {
            margin-top: 20px;
            font-size: 18px;
            padding: 15px;
            border-radius: 4px;
            display: none;
        }
        .elamb-verifier-message.success {
            background-color: #eaf5e6;
            border: 1px solid #3c8d0d;
            color: #2f6a0a;
            display: block;
        }
        .elamb-verifier-message.error {
            background-color: #fbeae5;
            border: 1px solid #c9302c;
            color: #c9302c;
            display: block;
        }
        .elamb-verifier-loader {
            display: none;
            font-style: italic;
            color: #555;
            font-family: Arial, sans-serif;
        }
    </style>

    <div class="elamb-verifier-container">
        <h1>Verificador de Correos</h1>
        <p>Si recibes un correo que parece ser de <strong>El Ambientalista Post</strong> y te resulta sospechoso, puedes verificar su autenticidad aquí. Sabrás al instante si es un correo legítimo o un intento de <em>phishing</em>.</p>

        <p>Ingresa la dirección de correo que deseas verificar:</p>

        <div class="elamb-verifier-form-group">
            <input type="text" id="elambEmailInput" placeholder="ejemplo@elambmex.com">
            <button id="elambVerifyButton">Verificar</button>
        </div>
        
        <div id="elambLoader" class="elamb-verifier-loader">Verificando...</div>
        <div id="elambMessage" class="elamb-verifier-message" aria-live="polite"></div>
        
        <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

        <h3>Reportar un Correo Sospechoso</h3>
        <p>Si prefieres que nuestro equipo lo analice, puedes adjuntar el archivo <code>.eml</code> del correo aquí y lo analizaremos por ti.</p>
        
        <?php
        if (isset($_GET['upload_success'])) {
            echo '<div class="elamb-verifier-message success">¡Archivo enviado! Gracias, nuestro equipo lo revisará.</div>';
        }
        if (isset($_GET['upload_error'])) {
            echo '<div class="elamb-verifier-message error">Error al enviar el archivo. Inténtalo de nuevo o envíalo manualmente.</div>';
        }
        ?>

        <form id="elambUploadForm" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('elamb_file_upload_action', 'elamb_file_upload_nonce'); ?>
            
            <label for="emlFile" style="font-weight:bold; font-size:16px; font-family:Arial, sans-serif;">Selecciona el archivo .eml o .msg:</label>
            <input type="file" id="emlFile" name="emlFile" accept=".eml,.msg" required>
            <button type="submit" name="submit_eml_file">Enviar Archivo</button>
        </form>
    </div>

    <script>
        document.getElementById('elambVerifyButton').addEventListener('click', async function() {
            const emailInput = document.getElementById("elambEmailInput").value.trim().toLowerCase();
            const messageDiv = document.getElementById("elambMessage");
            const loader = document.getElementById("elambLoader");

            messageDiv.textContent = "";
            messageDiv.className = "elamb-verifier-message";
            
            if (!emailInput) {
                messageDiv.textContent = "Por favor, ingresa un correo electrónico.";
                messageDiv.className = "elamb-verifier-message error";
                return;
            }

            loader.style.display = "block";

            try {
                const response = await fetch('<?php echo esc_url_raw(rest_url('elamb/v1/verify-email')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email: emailInput })
                });

                if (!response.ok) {
                    throw new Error('Error de red o del servidor.');
                }

                const result = await response.json();
                
                if (result.isValid) {
                    messageDiv.textContent = "El correo ingresado es auténtico y pertenece a nuestro equipo.";
                    messageDiv.className = "elamb-verifier-message success";
                } else {
                    messageDiv.textContent = "El correo ingresado NO está en nuestra lista de correos autorizados.";
                    messageDiv.className = "elamb-verifier-message error";
                }

            } catch (error) {
                console.error('Error:', error);
                messageDiv.textContent = "Ocurrió un error al verificar. Intenta de nuevo más tarde.";
                messageDiv.className = "elamb-verifier-message error";
            } finally {
                loader.style.display = "none";
            }
        });
    </script>
    
    <?php
    return ob_get_clean();
}


// === PARTE 2: EL ENDPOINT DE API REST (Corregido: Errores de sintaxis) ===

add_action('rest_api_init', function () {
    register_rest_route('elamb/v1', '/verify-email', array(
        'methods' => 'POST',
        'callback' => 'elamb_handle_email_verification_csv',
        'permission_callback' => '__return_true'
    ));
});

function elamb_handle_email_verification_csv($request) {
    $params = $request->get_json_params();
    $email_to_check = strtolower(sanitize_email($params['email']));

    if (empty($email_to_check)) {
        // AQUÍ ESTABA EL ERROR (FALTABA =>)
        return new WP_Error('no_email', 'No se proporcionó correo', array('status' => 400));
    }
    
    $csv_file_path = plugin_dir_path(__FILE__) . 'lista_correos_autorizados.csv';
    $isValid = false;

    if (!file_exists($csv_file_path)) {
        error_log('Error Verificador EML: No se encontró el archivo lista_correos_autorizados.csv');
        // AQUÍ ESTABA EL OTRO ERROR (FALTABA =>)
        return new WP_Error('csv_not_found', 'Error interno del servidor.', array('status' => 500));
    }

    if (($handle = fopen($csv_file_path, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (isset($row[0]) && strtolower(trim($row[0])) === $email_to_check) {
                $isValid = true;
                break;
            }
        }
        fclose($handle);
    }

    return new WP_REST_Response(array('isValid' => $isValid), 200);
}


// === PARTE 3: EL RECEPTOR DE ARCHIVOS .EML ===
// (Sin cambios)

add_action('template_redirect', 'elamb_handle_eml_file_upload');

function elamb_handle_eml_file_upload() {
    
    if (isset($_POST['submit_eml_file']) && isset($_POST['elamb_file_upload_nonce'])) {
        
        if (!wp_verify_nonce($_POST['elamb_file_upload_nonce'], 'elamb_file_upload_action')) {
            wp_die('Error de seguridad.');
        }

        if (isset($_FILES['emlFile']) && $_FILES['emlFile']['error'] == 0) {
            $file = $_FILES['emlFile'];
            $file_name = $file['name'];
            $file_tmp_name = $file['tmp_name'];
            $file_size = $file['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['eml', 'msg'];
            
            if (in_array($file_ext, $allowed_exts) && $file_size < 5242880) {
                $to = 'soporte@elambmex.com';
                $subject = 'Reporte de Phishing desde Verificador Web';
                $body = 'Un usuario ha enviado un archivo para su análisis.<br><br>Nombre del archivo: ' . esc_html($file_name);
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $attachments = array($file_tmp_name);

                if (wp_mail($to, $subject, $body, $headers, $attachments)) {
                    wp_redirect(add_query_arg('upload_success', 'true', wp_get_referer()));
                    exit;
                } else {
                    wp_redirect(add_query_arg('upload_error', 'true', wp_get_referer()));
                    exit;
                }
            } else {
                wp_redirect(add_query_arg('upload_error', 'true', wp_get_referer()));
                exit;
            }
        }
    }
}


// === PARTE 4: LA PÁGINA DE ADMINISTRACIÓN (Versión compatible) ===
// (Sin cambios)

add_action('admin_menu', 'elamb_verificador_admin_menu');
function elamb_verificador_admin_menu() {
    add_options_page(
        'Ajustes del Verificador de Correos',
        'Verificador de Correos',
        'manage_options',
        'elamb-verificador-settings',
        'elamb_render_verifier_options_page'
    );
}

function elamb_render_verifier_options_page() {
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permiso para acceder a esta página.');
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <?php settings_errors(); ?>
        <p>
            Desde aquí puedes actualizar la lista de correos autorizados que usa el verificador.
            El archivo debe ser un <strong>.csv</strong> y contener un solo correo por línea.
        </p>
        <form method="post" action="options.php" enctype="multipart/form-data">
            <?php settings_fields('elamb_verifier_options_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="elamb_csv_file">Subir nuevo archivo .csv</label>
                    </th>
                    <td>
                        <input type="file" id="elamb_csv_file" name="elamb_csv_file" accept=".csv" />
                    </td>
                </tr>
            </table>
            <?php submit_button('Guardar Cambios', 'primary', 'elamb_submit_csv_upload'); ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'elamb_verifier_register_settings');
function elamb_verifier_register_settings() {
    register_setting(
        'elamb_verifier_options_group',
        'elamb_csv_file',
        'elamb_handle_csv_upload'
    );
}

function elamb_handle_csv_upload($input_file) {
    
    if (isset($_FILES['elamb_csv_file']) && !empty($_FILES['elamb_csv_file']['tmp_name']) && isset($_POST['elamb_submit_csv_upload'])) {
        
        $file = $_FILES['elamb_csv_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file_ext !== 'csv') {
            add_settings_error('elamb_verifier_messages', 'invalid_ext', 'Error: El archivo debe ser de tipo .csv', 'error');
            return;
        }
        
        $target_path = plugin_dir_path(__FILE__) . 'lista_correos_autorizados.csv';
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            add_settings_error('elamb_verifier_messages', 'upload_success', '¡Éxito! La lista de correos autorizados ha sido actualizada.', 'updated');
        } else {
            add_settings_error('elamb_verifier_messages', 'upload_failed', 'Error: No se pudo guardar el archivo. Verifica los permisos de la carpeta del plugin.', 'error');
        }
    }
    return ''; 
}
?>