<?php
/*
|--------------------------------------------------------------------------
| MÓDULO 10: Comentarios Editoriales (Estilo NYT con Panel Lateral / Drawer)
| Integrado con modulo-autor-ubicacion, NYT Picks, Suplantación Editorial (Publicar como)
|--------------------------------------------------------------------------
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class EAC_Modulo_Comentarios {

    public function __construct() {
        // 1. Reemplazar plantilla nativa de The Fox con prioridad máxima
        add_filter( 'comments_template', [ $this, 'reemplazar_plantilla_the_fox' ], 9999 );

        // 2. Cargar CSS y JS en entradas y páginas individuales
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 99 );

        // 3. Registrar widget en Elementor
        add_action( 'elementor/widgets/register', [ $this, 'registrar_widget_elementor' ] );

        // 4. Shortcode: [eac_comentarios]
        add_shortcode( 'eac_comentarios', [ $this, 'render_shortcode' ] );

        // 5. Campo en perfil de usuario para rol editorial global y ubicación
        add_action( 'show_user_profile', [ $this, 'campo_cargo_editorial' ] );
        add_action( 'edit_user_profile', [ $this, 'campo_cargo_editorial' ] );
        add_action( 'personal_options_update', [ $this, 'guardar_cargo_editorial' ] );
        add_action( 'edit_user_profile_update', [ $this, 'guardar_cargo_editorial' ] );

        // 6. Metabox en edición de comentarios (Admin) para NYT Pick / Ubicación / Firma Editorial (Cambiar Autor)
        add_action( 'add_meta_boxes_comment', [ $this, 'agregar_metabox_comentario' ] );
        add_action( 'edit_comment', [ $this, 'guardar_metabox_comentario' ] );

        // 7. Guardar campo de ubicación pública del comentarista en el formulario
        add_action( 'comment_post', [ $this, 'guardar_ubicacion_publica_comentario' ] );

        // 8. Endpoint AJAX para el botón "Recomiendo" (Likes de comentarios)
        add_action( 'wp_ajax_eac_recommend_comment', [ $this, 'ajax_recommend_comment' ] );
        add_action( 'wp_ajax_nopriv_eac_recommend_comment', [ $this, 'ajax_recommend_comment' ] );

        // 9. Endpoint AJAX propio para publicar comentarios (con soporte para "Publicar como..." si es Editor/Admin)
        add_action( 'wp_ajax_eac_submit_comment', [ $this, 'ajax_submit_comment' ] );
        add_action( 'wp_ajax_nopriv_eac_submit_comment', [ $this, 'ajax_submit_comment' ] );
    }

    public function enqueue_assets() {
        if ( is_singular() ) {
            wp_enqueue_style(
                'eac-comentarios-style',
                ARP_CORE_URL . 'modulo-comentarios/assets/css/style.css',
                [],
                '6.0.0'
            );
            wp_enqueue_script(
                'eac-comentarios-script',
                ARP_CORE_URL . 'modulo-comentarios/assets/js/script.js',
                [],
                '6.0.0',
                true
            );
            wp_localize_script( 'eac-comentarios-script', 'eacCommentsData', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'eac_comments_nonce' ),
            ] );
        }
    }

    public function reemplazar_plantilla_the_fox( $template ) {
        $custom = ARP_CORE_PATH . 'modulo-comentarios/templates/comments-template.php';
        return file_exists( $custom ) ? $custom : $template;
    }

    public function registrar_widget_elementor( $widgets_manager ) {
        require_once ARP_CORE_PATH . 'modulo-comentarios/widgets/widget-comentarios.php';
        $widgets_manager->register( new \EAC_Widget_Comentarios() );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( [
            'title'     => 'De los comentarios',
            'show_form' => 'yes',
        ], $atts );

        ob_start();
        $GLOBALS['eac_comments_settings'] = $atts;
        comments_template();
        unset( $GLOBALS['eac_comments_settings'] );
        return ob_get_clean();
    }

    public function campo_cargo_editorial( $user ) {
        $cargo = get_the_author_meta( 'eac_cargo_editorial', $user->ID );
        ?>
        <h3>Información Editorial (Comentarios NYT)</h3>
        <table class="form-table">
            <tr>
                <th><label for="eac_cargo_editorial">Cargo Editorial Predeterminado</label></th>
                <td>
                    <input type="text" name="eac_cargo_editorial" id="eac_cargo_editorial" value="<?php echo esc_attr( $cargo ); ?>" class="regular-text" placeholder="Ej. Director Editorial / Columnista de tecnología" />
                    <p class="description">Se mostrará junto a la fecha en los comentarios cuando la entrada no tenga definido el campo "Rol del autor".</p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function guardar_cargo_editorial( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) return false;
        if ( isset( $_POST['eac_cargo_editorial'] ) ) {
            update_user_meta( $user_id, 'eac_cargo_editorial', sanitize_text_field( wp_unslash( $_POST['eac_cargo_editorial'] ) ) );
        }
    }

    public function agregar_metabox_comentario( $comment ) {
        add_meta_box(
            'eac_nyt_pick_metabox',
            'Control Editorial NYT: Firma, Destacado y Ubicación',
            [ $this, 'render_metabox_comentario' ],
            'comment',
            'normal',
            'high'
        );
    }

    public function render_metabox_comentario( $comment ) {
        wp_nonce_field( 'eac_guardar_nyt_pick_nonce', 'eac_nyt_pick_nonce' );

        $post_id           = (int) $comment->comment_post_ID;
        $post              = get_post( $post_id );
        $is_nyt_pick       = get_comment_meta( $comment->comment_ID, '_eac_nyt_pick', true );
        $custom_badge_text = get_comment_meta( $comment->comment_ID, '_eac_badge_text', true );
        $ubicacion_lector  = get_comment_meta( $comment->comment_ID, '_eac_user_location', true );
        $recommends        = (int) get_comment_meta( $comment->comment_ID, '_eac_recommends', true );

        // Datos de Suplantación / Firma Editorial
        $override_mode     = get_comment_meta( $comment->comment_ID, '_eac_author_override_mode', true ); // '', 'wp_user', 'post_author', 'collab_0..4', 'custom_staff'
        $override_role     = get_comment_meta( $comment->comment_ID, '_eac_custom_staff_role', true );
        $override_avatar   = get_comment_meta( $comment->comment_ID, '_eac_custom_staff_avatar', true );

        if ( empty( $custom_badge_text ) ) {
            $custom_badge_text = 'Selección de la redacción';
        }

        // Obtener lista de usuarios del equipo editorial (Administradores, Editores, Autores, Colaboradores)
        $staff_users = get_users( [
            'role__in' => [ 'administrator', 'editor', 'author', 'contributor' ],
            'orderby'  => 'display_name',
            'order'    => 'ASC',
        ] );

        // Obtener colaboradores registrados en el metabox de Autor y Ubicación de esa entrada
        $post_collabs = [];
        if ( $post_id > 0 ) {
            for ( $i = 0; $i < 5; $i++ ) {
                $c_name = get_post_meta( $post_id, "_arp_collab_name_$i", true );
                $c_role = get_post_meta( $post_id, "_arp_collab_role_$i", true );
                if ( ! empty( $c_name ) ) {
                    $post_collabs[ $i ] = [
                        'name' => $c_name,
                        'role' => $c_role ?: 'Colaborador · El Ambientalista Post',
                    ];
                }
            }
        }

        $post_author_user = ( $post && $post->post_author ) ? get_userdata( $post->post_author ) : null;
        ?>
        <style>
            .eac-admin-section-title {
                margin: 16px 0 8px 0;
                padding-bottom: 6px;
                border-bottom: 1px solid #dcdcde;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #1d2327;
            }
        </style>

        <h4 class="eac-admin-section-title">1. Identidad y Firma Editorial (Publicar o mostrar como otro autor)</h4>
        <table class="form-table">
            <tr>
                <th><label for="eac_reassign_identity">Cambiar firma del comentario a:</label></th>
                <td>
                    <select name="eac_reassign_identity" id="eac_reassign_identity" class="regular-text">
                        <option value="" <?php selected( $override_mode, '' ); ?>>— Mantener autor actual (<?php echo esc_html( $comment->comment_author ); ?>) —</option>

                        <?php if ( $post_author_user ) :
                            $pa_role = get_post_meta( $post_id, '_arp_author_role', true ) ?: 'Autor de la nota';
                        ?>
                            <optgroup label="Autor Principal de esta Nota">
                                <option value="user_<?php echo esc_attr( $post_author_user->ID ); ?>" <?php selected( (int) $comment->user_id === (int) $post_author_user->ID && $override_mode === 'wp_user', true ); ?>>
                                    ★ <?php echo esc_html( $post_author_user->display_name . ' (' . $pa_role . ')' ); ?>
                                </option>
                            </optgroup>
                        <?php endif; ?>

                        <?php if ( ! empty( $post_collabs ) ) : ?>
                            <optgroup label="Colaboradores asignados en esta Nota">
                                <?php foreach ( $post_collabs as $idx => $col ) : ?>
                                    <option value="collab_<?php echo esc_attr( $idx ); ?>" <?php selected( $override_mode, 'collab_' . $idx ); ?>>
                                        <?php echo esc_html( $col['name'] . ' — ' . $col['role'] ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>

                        <?php if ( ! empty( $staff_users ) ) : ?>
                            <optgroup label="Miembros de la Redacción (Usuarios de WordPress)">
                                <?php foreach ( $staff_users as $su ) : ?>
                                    <option value="user_<?php echo esc_attr( $su->ID ); ?>">
                                        <?php echo esc_html( $su->display_name . ' (' . $su->user_email . ')' ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>

                        <optgroup label="Firma Editorial Personalizada (Sin cuenta de usuario)">
                            <option value="custom_staff" <?php selected( $override_mode, 'custom_staff' ); ?>>
                                Forzar Insignia de Redacción con el Nombre/Rol escritos abajo
                            </option>
                        </optgroup>
                    </select>
                    <p class="description">
                        Si seleccionas a un miembro de la redacción o colaborador (por ejemplo, <strong>Pedro</strong> aunque tú estés en la sesión de <strong>Marcos</strong>), WordPress actualizará automáticamente el nombre, correo, ID de usuario, avatar e insignia verificada de este comentario.
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="eac_custom_staff_role">Rol / Cargo personalizado (Opcional)</label></th>
                <td>
                    <input type="text" name="eac_custom_staff_role" id="eac_custom_staff_role" value="<?php echo esc_attr( $override_role ); ?>" class="regular-text" placeholder="Ej. Corresponsal en Perú / Editor invitado" />
                    <p class="description">Si lo llenas, este cargo reemplazará el rol por defecto debajo del nombre del autor en este comentario.</p>
                </td>
            </tr>
            <tr>
                <th><label for="eac_custom_staff_avatar">URL de Foto / Avatar personalizado (Opcional)</label></th>
                <td>
                    <input type="url" name="eac_custom_staff_avatar" id="eac_custom_staff_avatar" value="<?php echo esc_attr( $override_avatar ); ?>" class="regular-text" placeholder="https://..." />
                    <p class="description">Útil si el autor o colaborador (ej. Pedro) no tiene cuenta de WordPress pero quieres mostrar su fotografía junto a la insignia del periódico.</p>
                </td>
            </tr>
        </table>

        <h4 class="eac-admin-section-title">2. Curaduría y Destacado (NYT Pick)</h4>
        <table class="form-table">
            <tr>
                <th><label for="eac_nyt_pick">Selección Editorial (NYT Pick)</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="eac_nyt_pick" id="eac_nyt_pick" value="1" <?php checked( $is_nyt_pick, '1' ); ?> />
                        Destacar este comentario en la pestaña "Selecciones de los lectores" y en la vista previa
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="eac_badge_text">Texto del distintivo</label></th>
                <td>
                    <input type="text" name="eac_badge_text" id="eac_badge_text" value="<?php echo esc_attr( $custom_badge_text ); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="eac_user_location">Ubicación del lector</label></th>
                <td>
                    <input type="text" name="eac_user_location" id="eac_user_location" value="<?php echo esc_attr( $ubicacion_lector ); ?>" class="regular-text" placeholder="Ej. Lima, Perú" />
                </td>
            </tr>
            <tr>
                <th><label for="eac_recommends">Contador de "Recomiendo"</label></th>
                <td>
                    <input type="number" name="eac_recommends" id="eac_recommends" value="<?php echo esc_attr( $recommends ); ?>" class="small-text" min="0" />
                </td>
            </tr>
        </table>
        <?php
    }

    public function guardar_metabox_comentario( $comment_id ) {
        if ( ! isset( $_POST['eac_nyt_pick_nonce'] ) || ! wp_verify_nonce( $_POST['eac_nyt_pick_nonce'], 'eac_guardar_nyt_pick_nonce' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
            return;
        }

        $comment = get_comment( $comment_id );
        $post_id = $comment ? (int) $comment->comment_post_ID : 0;

        // 1. Procesar cambio de identidad / firma editorial
        if ( isset( $_POST['eac_reassign_identity'] ) ) {
            $selection = sanitize_text_field( wp_unslash( $_POST['eac_reassign_identity'] ) );

            if ( strpos( $selection, 'user_' ) === 0 ) {
                $target_user_id = intval( substr( $selection, 5 ) );
                $target_user    = get_userdata( $target_user_id );
                if ( $target_user ) {
                    // Evitar recursión infinita removiendo temporalmente el hook
                    remove_action( 'edit_comment', [ $this, 'guardar_metabox_comentario' ] );
                    wp_update_comment( [
                        'comment_ID'           => $comment_id,
                        'user_id'              => $target_user->ID,
                        'comment_author'       => $target_user->display_name,
                        'comment_author_email' => $target_user->user_email,
                        'comment_author_url'   => $target_user->user_url,
                    ] );
                    add_action( 'edit_comment', [ $this, 'guardar_metabox_comentario' ] );
                    update_comment_meta( $comment_id, '_eac_author_override_mode', 'wp_user' );
                }
            } elseif ( strpos( $selection, 'collab_' ) === 0 && $post_id > 0 ) {
                $idx    = intval( substr( $selection, 7 ) );
                $c_name = get_post_meta( $post_id, "_arp_collab_name_$idx", true );
                $c_role = get_post_meta( $post_id, "_arp_collab_role_$idx", true );
                $c_url  = get_post_meta( $post_id, "_arp_collab_url_$idx", true );

                if ( ! empty( $c_name ) ) {
                    remove_action( 'edit_comment', [ $this, 'guardar_metabox_comentario' ] );
                    wp_update_comment( [
                        'comment_ID'         => $comment_id,
                        'user_id'            => 0,
                        'comment_author'     => $c_name,
                        'comment_author_url' => $c_url ?: '',
                    ] );
                    add_action( 'edit_comment', [ $this, 'guardar_metabox_comentario' ] );
                    update_comment_meta( $comment_id, '_eac_author_override_mode', 'collab_' . $idx );
                    if ( empty( $_POST['eac_custom_staff_role'] ) && ! empty( $c_role ) ) {
                        update_comment_meta( $comment_id, '_eac_custom_staff_role', sanitize_text_field( $c_role ) );
                    }
                }
            } elseif ( $selection === 'custom_staff' ) {
                update_comment_meta( $comment_id, '_eac_author_override_mode', 'custom_staff' );
            } elseif ( $selection === '' ) {
                // Si se deja vacío no sobreescribimos los datos actuales
            }
        }

        if ( isset( $_POST['eac_custom_staff_role'] ) ) {
            update_comment_meta( $comment_id, '_eac_custom_staff_role', sanitize_text_field( wp_unslash( $_POST['eac_custom_staff_role'] ) ) );
        }
        if ( isset( $_POST['eac_custom_staff_avatar'] ) ) {
            update_comment_meta( $comment_id, '_eac_custom_staff_avatar', esc_url_raw( wp_unslash( $_POST['eac_custom_staff_avatar'] ) ) );
        }

        // 2. Guardar datos de NYT Pick y Ubicación
        $is_pick = isset( $_POST['eac_nyt_pick'] ) ? '1' : '0';
        update_comment_meta( $comment_id, '_eac_nyt_pick', $is_pick );

        if ( isset( $_POST['eac_badge_text'] ) ) {
            update_comment_meta( $comment_id, '_eac_badge_text', sanitize_text_field( wp_unslash( $_POST['eac_badge_text'] ) ) );
        }
        if ( isset( $_POST['eac_user_location'] ) ) {
            update_comment_meta( $comment_id, '_eac_user_location', sanitize_text_field( wp_unslash( $_POST['eac_user_location'] ) ) );
        }
        if ( isset( $_POST['eac_recommends'] ) ) {
            update_comment_meta( $comment_id, '_eac_recommends', max( 0, intval( $_POST['eac_recommends'] ) ) );
        }
    }

    public function guardar_ubicacion_publica_comentario( $comment_id ) {
        if ( isset( $_POST['eac_user_location'] ) && ! empty( $_POST['eac_user_location'] ) ) {
            update_comment_meta( $comment_id, '_eac_user_location', sanitize_text_field( wp_unslash( $_POST['eac_user_location'] ) ) );
        }
    }

    public function ajax_recommend_comment() {
        check_ajax_referer( 'eac_comments_nonce', 'nonce' );
        $comment_id = isset( $_POST['comment_id'] ) ? intval( $_POST['comment_id'] ) : 0;
        if ( $comment_id <= 0 ) {
            wp_send_json_error();
        }
        $current = (int) get_comment_meta( $comment_id, '_eac_recommends', true );
        $action_type = isset( $_POST['recommend_action'] ) ? sanitize_text_field( $_POST['recommend_action'] ) : 'add';
        if ( $action_type === 'remove' && $current > 0 ) {
            $current--;
        } else {
            $current++;
        }
        update_comment_meta( $comment_id, '_eac_recommends', $current );
        wp_send_json_success( [ 'count' => $current ] );
    }

    public function ajax_submit_comment() {
        check_ajax_referer( 'eac_comments_nonce', 'nonce' );

        $post_id        = isset( $_POST['comment_post_ID'] ) ? intval( $_POST['comment_post_ID'] ) : 0;
        $comment_parent = isset( $_POST['comment_parent'] ) ? intval( $_POST['comment_parent'] ) : 0;
        $comment_text   = isset( $_POST['comment'] ) ? wp_kses_post( wp_unslash( $_POST['comment'] ) ) : '';

        // Verificar si quien publica tiene permisos de moderación y eligió "Publicar como..." desde el Drawer
        $can_impersonate = current_user_can( 'moderate_comments' ) || current_user_can( 'edit_others_posts' );
        $post_as         = ( $can_impersonate && isset( $_POST['eac_post_as_identity'] ) ) ? sanitize_text_field( wp_unslash( $_POST['eac_post_as_identity'] ) ) : 'self';

        if ( $can_impersonate && $post_as !== 'self' ) {
            if ( empty( trim( $comment_text ) ) ) {
                wp_send_json_error( [ 'message' => 'Por favor escribe el contenido del comentario.' ] );
            }

            $insert_args = [
                'comment_post_ID'  => $post_id,
                'comment_parent'   => $comment_parent,
                'comment_content'  => $comment_text,
                'comment_approved' => 1,
                'comment_type'     => 'comment',
            ];

            $override_mode = '';
            $custom_role   = isset( $_POST['eac_post_as_custom_role'] ) ? sanitize_text_field( wp_unslash( $_POST['eac_post_as_custom_role'] ) ) : '';

            if ( strpos( $post_as, 'user_' ) === 0 ) {
                $target_uid  = intval( substr( $post_as, 5 ) );
                $target_user = get_userdata( $target_uid );
                if ( $target_user ) {
                    $insert_args['user_id']              = $target_user->ID;
                    $insert_args['comment_author']       = $target_user->display_name;
                    $insert_args['comment_author_email'] = $target_user->user_email;
                    $insert_args['comment_author_url']   = $target_user->user_url;
                    $override_mode                       = 'wp_user';
                }
            } elseif ( strpos( $post_as, 'collab_' ) === 0 ) {
                $idx    = intval( substr( $post_as, 7 ) );
                $c_name = get_post_meta( $post_id, "_arp_collab_name_$idx", true );
                $c_role = get_post_meta( $post_id, "_arp_collab_role_$idx", true );
                $c_url  = get_post_meta( $post_id, "_arp_collab_url_$idx", true );
                if ( ! empty( $c_name ) ) {
                    $insert_args['user_id']              = 0;
                    $insert_args['comment_author']       = $c_name;
                    $insert_args['comment_author_email'] = get_option( 'admin_email' );
                    $insert_args['comment_author_url']   = $c_url ?: '';
                    $override_mode                       = 'collab_' . $idx;
                    if ( empty( $custom_role ) && ! empty( $c_role ) ) {
                        $custom_role = $c_role;
                    }
                }
            } elseif ( $post_as === 'custom_staff' ) {
                $c_name = isset( $_POST['eac_post_as_custom_name'] ) ? sanitize_text_field( wp_unslash( $_POST['eac_post_as_custom_name'] ) ) : 'Redacción';
                $insert_args['user_id']              = 0;
                $insert_args['comment_author']       = $c_name;
                $insert_args['comment_author_email'] = get_option( 'admin_email' );
                $override_mode                       = 'custom_staff';
            }

            $new_comment_id = wp_insert_comment( $insert_args );
            if ( ! $new_comment_id ) {
                wp_send_json_error( [ 'message' => 'No se pudo guardar el comentario.' ] );
            }

            if ( $override_mode ) {
                update_comment_meta( $new_comment_id, '_eac_author_override_mode', $override_mode );
            }
            if ( ! empty( $custom_role ) ) {
                update_comment_meta( $new_comment_id, '_eac_custom_staff_role', $custom_role );
            }

            wp_send_json_success( [
                'message'  => 'Comentario publicado con la firma editorial seleccionada.',
                'approved' => true,
            ] );
        }

        // Flujo estándar de envío
        $comment_data = [
            'comment_post_ID' => $post_id,
            'author'          => isset( $_POST['author'] ) ? sanitize_text_field( wp_unslash( $_POST['author'] ) ) : '',
            'email'           => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
            'url'             => '',
            'comment'         => $comment_text,
            'comment_parent'  => $comment_parent,
        ];

        $comment = wp_handle_comment_submission( $comment_data );

        if ( is_wp_error( $comment ) ) {
            wp_send_json_error( [ 'message' => $comment->get_error_message() ] );
        }

        if ( ! empty( $_POST['eac_user_location'] ) ) {
            update_comment_meta(
                $comment->comment_ID,
                '_eac_user_location',
                sanitize_text_field( wp_unslash( $_POST['eac_user_location'] ) )
            );
        }

        $user = wp_get_current_user();
        do_action( 'set_comment_cookies', $comment, $user );

        $approved = ( (string) $comment->comment_approved === '1' );
        $msg = $approved
            ? 'Tu comentario ha sido publicado.'
            : 'Gracias. Tu comentario ha sido enviado y está pendiente de moderación editorial.';

        wp_send_json_success( [
            'message'  => $msg,
            'approved' => $approved,
        ] );
    }
}

new EAC_Modulo_Comentarios();

/**
 * Helper class para renderizar tanto la tarjeta en el artículo como el panel lateral (Drawer) estilo NYT
 */
class EAC_Comentarios_Helper {

    public static $avatar_palette = [
        '#f4a298', '#e5c158', '#8ab8a8', '#9ab3d5', '#c8a2c8', '#d9ad7c', '#a3c1ad'
    ];

    public static function es_autor_o_redaccion( $comment ) {
        if ( isset( $comment->comment_ID ) && $comment->comment_ID > 0 ) {
            $override_mode = get_comment_meta( $comment->comment_ID, '_eac_author_override_mode', true );
            if ( ! empty( $override_mode ) ) {
                return true;
            }
        }

        $post = isset( $comment->comment_post_ID ) ? get_post( $comment->comment_post_ID ) : null;
        if ( $post && isset( $comment->user_id ) && (int) $comment->user_id > 0 && (int) $comment->user_id === (int) $post->post_author ) {
            return true;
        }
        if ( ! empty( $comment->user_id ) ) {
            $user = get_userdata( $comment->user_id );
            if ( $user && array_intersect( [ 'administrator', 'editor', 'author' ], (array) $user->roles ) ) {
                return true;
            }
        }
        return false;
    }

    public static function obtener_rol_autor( $comment, $is_staff ) {
        if ( ! $is_staff ) {
            return '';
        }

        // 0. Prioridad máxima: Rol personalizado asignado al comentario desde el metabox o desde "Publicar como"
        if ( isset( $comment->comment_ID ) && $comment->comment_ID > 0 ) {
            $custom_role = get_comment_meta( $comment->comment_ID, '_eac_custom_staff_role', true );
            if ( ! empty( $custom_role ) ) {
                return $custom_role;
            }
        }

        $user_id = isset( $comment->user_id ) ? (int) $comment->user_id : 0;
        $post_id = isset( $comment->comment_post_ID ) ? (int) $comment->comment_post_ID : 0;
        $post    = $post_id ? get_post( $post_id ) : null;

        // 1. Autor principal (_arp_author_role)
        if ( $post && $user_id > 0 && (int) $post->post_author === $user_id ) {
            $role = get_post_meta( $post_id, '_arp_author_role', true );
            if ( ! empty( $role ) ) return $role;
        }

        // 2. Colaborador (_arp_collab_name_$i)
        if ( $post_id > 0 ) {
            $comment_author_name = trim( mb_strtolower( get_comment_author( $comment ) ) );
            for ( $i = 0; $i < 5; $i++ ) {
                $c_name = trim( mb_strtolower( (string) get_post_meta( $post_id, "_arp_collab_name_$i", true ) ) );
                $c_role = trim( (string) get_post_meta( $post_id, "_arp_collab_role_$i", true ) );
                if ( ! empty( $c_name ) && $c_name === $comment_author_name && ! empty( $c_role ) ) {
                    return $c_role;
                }
            }
        }

        // 3. Respaldo en perfil de usuario
        if ( $user_id > 0 ) {
            $user_role = get_user_meta( $user_id, 'eac_cargo_editorial', true );
            if ( ! empty( $user_role ) ) return $user_role;
        }

        return 'Redacción · El Ambientalista Post';
    }

    public static function obtener_subtitulo( $comment, $is_staff, $include_date = true ) {
        $fecha_corta = get_comment_date( 'j \d\e F', $comment );
        if ( $is_staff ) {
            $rol = self::obtener_rol_autor( $comment, true );
            return $include_date ? $rol . ' · ' . $fecha_corta : $rol;
        }
        $loc = get_comment_meta( $comment->comment_ID, '_eac_user_location', true );
        if ( ! empty( $loc ) ) {
            return $include_date ? $loc . ' · ' . $fecha_corta : $loc;
        }
        return $fecha_corta;
    }

    public static function render_avatar( $comment, $is_staff, $size = 38 ) {
        if ( isset( $comment->comment_ID ) && $comment->comment_ID > 0 ) {
            $custom_avatar = get_comment_meta( $comment->comment_ID, '_eac_custom_staff_avatar', true );
            if ( ! empty( $custom_avatar ) ) {
                return '<div class="eac-avatar"><img src="' . esc_url( $custom_avatar ) . '" alt="' . esc_attr( get_comment_author( $comment ) ) . '" width="' . intval( $size ) . '" height="' . intval( $size ) . '" loading="lazy" /></div>';
            }
        }

        if ( $is_staff && ! empty( $comment->user_id ) ) {
            $avatar_url = get_avatar_url( $comment->user_id, [ 'size' => 120 ] );
            return '<div class="eac-avatar"><img src="' . esc_url( $avatar_url ) . '" alt="' . esc_attr( get_comment_author( $comment ) ) . '" width="' . intval( $size ) . '" height="' . intval( $size ) . '" loading="lazy" /></div>';
        }
        $author_name = isset( $comment->comment_author ) ? trim( $comment->comment_author ) : trim( get_comment_author( $comment ) );
        $initial     = ! empty( $author_name ) ? mb_strtoupper( mb_substr( $author_name, 0, 1 ) ) : 'L';
        $cid         = isset( $comment->comment_ID ) ? $comment->comment_ID : 0;
        $color_index = abs( crc32( $author_name . $cid ) ) % count( self::$avatar_palette );
        $bg_color    = self::$avatar_palette[ $color_index ];

        return '<div class="eac-avatar eac-initial-avatar" style="background-color:' . esc_attr( $bg_color ) . '; width:' . intval( $size ) . 'px; height:' . intval( $size ) . 'px;"><span>' . esc_html( $initial ) . '</span></div>';
    }

    public static function contar_descendientes( $comment_id, $children_map ) {
        if ( empty( $children_map[ $comment_id ] ) ) return 0;
        $total = count( $children_map[ $comment_id ] );
        foreach ( $children_map[ $comment_id ] as $child ) {
            $total += self::contar_descendientes( $child->comment_ID, $children_map );
        }
        return $total;
    }

    public static function aplanar_descendientes( $comment_id, $children_map, &$result = [] ) {
        if ( empty( $children_map[ $comment_id ] ) ) return $result;
        foreach ( $children_map[ $comment_id ] as $child ) {
            $result[] = $child;
            self::aplanar_descendientes( $child->comment_ID, $children_map, $result );
        }
        return $result;
    }
}
