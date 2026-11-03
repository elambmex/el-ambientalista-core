<?php
if ( ! defined( 'ABSPATH' ) || post_password_required() ) return;

$post_id    = get_the_ID();
$post_title = get_the_title( $post_id );
$settings   = isset( $GLOBALS['eac_comments_settings'] ) ? $GLOBALS['eac_comments_settings'] : [];
$box_title  = ! empty( $settings['title'] ) ? $settings['title'] : 'De los comentarios';
$show_form  = ! isset( $settings['show_form'] ) || $settings['show_form'] === 'yes';
$logo_url   = ARP_CORE_URL . 'modulo-comentarios/assets/img/logo.svg';

// Obtener todos los comentarios aprobados de la entrada
$all_comments = get_comments( [
    'post_id' => $post_id,
    'status'  => 'approve',
    'order'   => 'ASC',
] );

$total_count  = count( $all_comments );
$comments_by_id = [];
$children_map   = [];
$root_comments  = [];

foreach ( $all_comments as $c ) {
    $comments_by_id[ $c->comment_ID ] = $c;
    $parent_id = (int) $c->comment_parent;
    if ( $parent_id === 0 ) {
        $root_comments[] = $c;
    } else {
        if ( ! isset( $children_map[ $parent_id ] ) ) {
            $children_map[ $parent_id ] = [];
        }
        $children_map[ $parent_id ][] = $c;
    }
}

// Seleccionar el comentario destacado principal para la tarjeta dentro del artículo (In-Article Preview Card)
$featured_root = null;
foreach ( $root_comments as $rc ) {
    // Prioridad 1: Comentario raíz que tenga respuesta del autor/redacción o sea del autor
    $has_staff_child = false;
    if ( ! empty( $children_map[ $rc->comment_ID ] ) ) {
        foreach ( $children_map[ $rc->comment_ID ] as $ch ) {
            if ( EAC_Comentarios_Helper::es_autor_o_redaccion( $ch ) ) {
                $has_staff_child = true;
                break;
            }
        }
    }
    if ( EAC_Comentarios_Helper::es_autor_o_redaccion( $rc ) || $has_staff_child ) {
        $featured_root = $rc;
        break;
    }
}

// Prioridad 2: Comentario marcado como NYT Pick
if ( ! $featured_root ) {
    foreach ( $root_comments as $rc ) {
        if ( get_comment_meta( $rc->comment_ID, '_eac_nyt_pick', true ) === '1' ) {
            $featured_root = $rc;
            break;
        }
    }
}

// Prioridad 3: Primer comentario raíz disponible
if ( ! $featured_root && ! empty( $root_comments ) ) {
    $featured_root = $root_comments[0];
}
?>

<!-- 1. TARJETA COMPACTA DENTRO DEL ARTÍCULO (IN-ARTICLE NYT PREVIEW CARD) -->
<section id="comments" class="eac-comments-card">
    <div class="eac-card-header">
        <h3 class="eac-header-title"><?php echo esc_html( $box_title ); ?></h3>
        <button type="button" class="eac-header-count eac-open-drawer-btn" data-open-mode="all" title="Ver todos los comentarios">
            <svg class="eac-bubble-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span><?php echo number_format_i18n( $total_count ); ?></span>
        </button>
    </div>

    <?php if ( $featured_root ) :
        $f_is_staff = EAC_Comentarios_Helper::es_autor_o_redaccion( $featured_root );
        $f_subtitle = EAC_Comentarios_Helper::obtener_subtitulo( $featured_root, $f_is_staff, false );
        $f_descendants = EAC_Comentarios_Helper::aplanar_descendientes( $featured_root->comment_ID, $children_map );
        $f_featured_reply = null;
        foreach ( $f_descendants as $desc ) {
            if ( EAC_Comentarios_Helper::es_autor_o_redaccion( $desc ) ) {
                $f_featured_reply = $desc;
                break;
            }
        }
        $remaining_replies = count( $f_descendants ) - ( $f_featured_reply ? 1 : 0 );
    ?>
        <div class="eac-featured-preview">
            <article class="eac-comment-body">
                <header class="eac-comment-meta">
                    <?php echo EAC_Comentarios_Helper::render_avatar( $featured_root, $f_is_staff, 42 ); ?>
                    <div class="eac-author-info">
                        <div class="eac-author-line">
                            <span class="eac-author-name"><?php echo esc_html( get_comment_author( $featured_root ) ); ?></span>
                            <?php if ( $f_is_staff ) : ?>
                                <span class="eac-staff-badge" title="Redacción · El Ambientalista Post">
                                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="eac-staff-logo" width="18" height="18" />
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="eac-author-subtitle"><?php echo esc_html( $f_subtitle ); ?></div>
                    </div>
                </header>
                <div class="eac-comment-content">
                    <?php echo wpautop( esc_html( $featured_root->comment_content ) ); ?>
                </div>
            </article>

            <?php if ( $f_featured_reply ) :
                $r_is_staff = EAC_Comentarios_Helper::es_autor_o_redaccion( $f_featured_reply );
                $r_subtitle = EAC_Comentarios_Helper::obtener_subtitulo( $f_featured_reply, $r_is_staff, false );
            ?>
                <div class="eac-preview-nested-reply">
                    <article class="eac-comment-body">
                        <header class="eac-comment-meta">
                            <?php echo EAC_Comentarios_Helper::render_avatar( $f_featured_reply, $r_is_staff, 38 ); ?>
                            <div class="eac-author-info">
                                <div class="eac-author-line">
                                    <span class="eac-author-name"><?php echo esc_html( get_comment_author( $f_featured_reply ) ); ?></span>
                                    <?php if ( $r_is_staff ) : ?>
                                        <span class="eac-staff-badge" title="Redacción · El Ambientalista Post">
                                            <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="eac-staff-logo" width="18" height="18" />
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="eac-author-subtitle"><?php echo esc_html( $r_subtitle ); ?></div>
                            </div>
                        </header>
                        <div class="eac-comment-content">
                            <?php echo wpautop( esc_html( $f_featured_reply->comment_content ) ); ?>
                        </div>
                    </article>
                </div>
            <?php endif; ?>

            <div class="eac-card-footer-actions">
                <?php if ( $remaining_replies > 0 ) : ?>
                    <button type="button"
                            class="eac-pill-btn eac-open-drawer-btn"
                            data-open-mode="thread"
                            data-thread-id="<?php echo esc_attr( $featured_root->comment_ID ); ?>">
                        <?php echo sprintf( _n( 'Leer %s respuesta', 'Leer %s respuestas', $remaining_replies, 'arp-elementor' ), number_format_i18n( $remaining_replies ) ); ?>
                    </button>
                <?php endif; ?>

                <button type="button"
                        class="eac-pill-btn eac-open-drawer-btn <?php echo ( $remaining_replies > 0 ) ? 'eac-pill-secondary' : ''; ?>"
                        data-open-mode="all">
                    <?php echo sprintf( 'Leer más comentarios (%s)', number_format_i18n( $total_count ) ); ?>
                </button>
            </div>
        </div>
    <?php else : ?>
        <div class="eac-empty-card-wrap">
            <p class="eac-empty-state">Aún no hay comentarios publicados en esta nota.</p>
            <?php if ( comments_open() ) : ?>
                <button type="button" class="eac-pill-btn eac-open-drawer-btn" data-open-mode="all" data-focus-form="1">
                    Escribir el primer comentario
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<!-- 2. PANEL LATERAL DESLIZANTE (NYT SLIDE-OVER DRAWER) -->
<div id="eac-drawer-backdrop" class="eac-drawer-backdrop" aria-hidden="true">
    <aside id="eac-comments-drawer" class="eac-comments-drawer" role="dialog" aria-modal="true" aria-label="Comentarios del artículo">
        
        <button type="button" id="eac-drawer-close" class="eac-drawer-close" aria-label="Cerrar panel de comentarios">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div class="eac-drawer-scroll-area">

            <!-- VISTA A: HILO DE RESPUESTA ESPECÍFICO ("Hilo de respuesta sobre...") -->
            <div id="eac-drawer-view-thread" class="eac-drawer-view" style="display:none;">
                <header class="eac-drawer-header">
                    <h2 class="eac-drawer-title">
                        <strong>Hilo de respuesta sobre</strong> <?php echo esc_html( $post_title ); ?>
                    </h2>
                </header>

                <!-- Contenedor donde se inyecta dinámicamente el hilo activo con fondo crema #fefae8 -->
                <div id="eac-active-thread-box" class="eac-highlighted-thread-box"></div>

                <!-- Carrusel horizontal: "Más comentarios sobre..." -->
                <?php if ( count( $root_comments ) > 1 ) : ?>
                    <div class="eac-more-carousel-section">
                        <h4 class="eac-more-carousel-heading">
                            Más comentarios sobre <strong><?php echo esc_html( $post_title ); ?> &rsaquo;</strong>
                        </h4>

                        <div class="eac-mini-cards-track" id="eac-mini-cards-track">
                            <?php foreach ( $root_comments as $rc ) :
                                $m_is_staff = EAC_Comentarios_Helper::es_autor_o_redaccion( $rc );
                                $m_likes    = (int) get_comment_meta( $rc->comment_ID, '_eac_recommends', true );
                            ?>
                                <div class="eac-mini-card" data-thread-target="<?php echo esc_attr( $rc->comment_ID ); ?>">
                                    <div class="eac-mini-card-meta">
                                        <?php echo EAC_Comentarios_Helper::render_avatar( $rc, $m_is_staff, 28 ); ?>
                                        <span class="eac-mini-author"><?php echo esc_html( get_comment_author( $rc ) ); ?></span>
                                    </div>
                                    <div class="eac-mini-card-excerpt">
                                        <?php echo esc_html( wp_trim_words( $rc->comment_content, 28, '...' ) ); ?>
                                    </div>
                                    <div class="eac-mini-card-footer">
                                        <strong><?php echo number_format_i18n( $m_likes ); ?></strong> Recomiendo
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="eac-carousel-arrows">
                            <button type="button" class="eac-carousel-nav-btn" id="eac-carousel-prev" aria-label="Anterior">&lsaquo;</button>
                            <button type="button" class="eac-carousel-nav-btn" id="eac-carousel-next" aria-label="Siguiente">&rsaquo;</button>
                        </div>

                        <div class="eac-all-comments-btn-wrap">
                            <button type="button" class="eac-pill-btn eac-switch-to-all-btn">
                                <?php echo sprintf( 'Los %s comentarios', number_format_i18n( $total_count ) ); ?>
                            </button>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="eac-all-comments-btn-wrap" style="margin-top:20px;">
                        <button type="button" class="eac-pill-btn eac-switch-to-all-btn">
                            <?php echo sprintf( 'Los %s comentarios', number_format_i18n( $total_count ) ); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- VISTA B: TODOS LOS COMENTARIOS CON BUSCADOR Y PESTAÑAS ("238 comentarios en...") -->
            <div id="eac-drawer-view-all" class="eac-drawer-view">
                <header class="eac-drawer-header">
                    <h2 class="eac-drawer-title">
                        <strong><?php echo sprintf( _n( '%s comentario en', '%s comentarios en', $total_count, 'arp-elementor' ), number_format_i18n( $total_count ) ); ?></strong>
                        <?php echo esc_html( $post_title ); ?>
                    </h2>

                    <!-- Buscador con ícono de lupa -->
                    <div class="eac-search-bar">
                        <svg class="eac-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="search" id="eac-comment-search" placeholder="Buscar comentarios" autocomplete="off" />
                    </div>

                    <!-- Pestañas en píldora estilo NYT -->
                    <div class="eac-filter-tabs" role="tablist">
                        <button type="button" class="eac-tab-pill is-active" data-filter="all">Todos</button>
                        <button type="button" class="eac-tab-pill" data-filter="picks">Selecciones de los lectores</button>
                        <button type="button" class="eac-tab-pill" data-filter="staff">De la redacción</button>
                    </div>
                </header>

                <div class="eac-drawer-Toolbar">
                    <?php if ( ! comments_open() ) : ?>
                        <p class="eac-closed-banner-text">
                            La sección de comentarios está cerrada. Para enviar una carta al editor para su publicación, escriba a <a href="mailto:redaccion@elambientalistapost.com">redaccion@elambientalistapost.com</a>.
                        </p>
                    <?php elseif ( $show_form ) : ?>
                        <button type="button" id="eac-toggle-form-btn" class="eac-pill-btn eac-write-comment-btn">
                            Escribir un comentario
                        </button>
                    <?php endif; ?>

                    <div class="eac-sort-dropdown-wrap">
                        <label for="eac-sort-select">Ordenar por:</label>
                        <select id="eac-sort-select" class="eac-sort-select">
                            <option value="newest">Más recientes</option>
                            <option value="oldest">Más antiguos</option>
                            <option value="recommended">Más recomendados</option>
                        </select>
                    </div>
                </div>

                <!-- Formulario Editorial Propio estilo NYT (Sin iframe de Jetpack ni estilos de The Fox) -->
                <?php if ( comments_open() && $show_form ) :
                    $current_user = wp_get_current_user();
                    $commenter    = wp_get_current_commenter();
                    $is_logged    = is_user_logged_in();
                ?>
                    <div id="eac-drawer-form-container" class="eac-drawer-form-container" style="display:none;">
                        <form id="eac-custom-comment-form" class="eac-nyt-form" novalidate>
                            <div class="eac-form-top-bar">
                                <h4 id="eac-reply-title" class="eac-reply-title">Participa en la conversación</h4>
                                <button type="button" id="eac-cancel-reply-btn" class="eac-cancel-reply-btn" style="display:none;">Cancelar respuesta</button>
                            </div>

                            <?php if ( $is_logged ) :
                                $u_is_staff      = EAC_Comentarios_Helper::es_autor_o_redaccion( (object)[ 'user_id' => $current_user->ID, 'comment_post_ID' => $post_id ] );
                                $can_impersonate = current_user_can( 'moderate_comments' ) || current_user_can( 'edit_others_posts' );
                                $post_obj        = get_post( $post_id );
                                $post_author_obj = ( $post_obj && $post_obj->post_author ) ? get_userdata( $post_obj->post_author ) : null;
                                $staff_list      = $can_impersonate ? get_users( [ 'role__in' => [ 'administrator', 'editor', 'author', 'contributor' ], 'orderby' => 'display_name' ] ) : [];
                                $form_collabs    = [];
                                if ( $can_impersonate ) {
                                    for ( $i = 0; $i < 5; $i++ ) {
                                        $cn = get_post_meta( $post_id, "_arp_collab_name_$i", true );
                                        $cr = get_post_meta( $post_id, "_arp_collab_role_$i", true );
                                        if ( ! empty( $cn ) ) {
                                            $form_collabs[ $i ] = [ 'name' => $cn, 'role' => $cr ?: 'Colaborador' ];
                                        }
                                    }
                                }
                            ?>
                                <div class="eac-logged-user-pill">
                                    <?php echo EAC_Comentarios_Helper::render_avatar( (object)[ 'user_id' => $current_user->ID, 'comment_author' => $current_user->display_name, 'comment_ID' => 0 ], $u_is_staff, 28 ); ?>
                                    <span>Sesión activa: <strong><?php echo esc_html( $current_user->display_name ); ?></strong></span>
                                </div>

                                <?php if ( $can_impersonate ) : ?>
                                    <div class="eac-impersonate-box">
                                        <div class="eac-field-group">
                                            <label for="eac-post-as-identity">Publicar comentario con la firma de:</label>
                                            <select name="eac_post_as_identity" id="eac-post-as-identity" class="eac-select-input">
                                                <option value="self">Mi cuenta actual (<?php echo esc_html( $current_user->display_name ); ?>)</option>
                                                <?php if ( $post_author_obj && (int) $post_author_obj->ID !== (int) $current_user->ID ) : ?>
                                                    <option value="user_<?php echo esc_attr( $post_author_obj->ID ); ?>">
                                                        ★ Autor de esta nota: <?php echo esc_html( $post_author_obj->display_name ); ?>
                                                    </option>
                                                <?php endif; ?>
                                                <?php if ( ! empty( $form_collabs ) ) : ?>
                                                    <optgroup label="Colaboradores de esta nota">
                                                        <?php foreach ( $form_collabs as $idx => $fc ) : ?>
                                                            <option value="collab_<?php echo esc_attr( $idx ); ?>">
                                                                <?php echo esc_html( $fc['name'] . ' (' . $fc['role'] . ')' ); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endif; ?>
                                                <?php if ( ! empty( $staff_list ) ) : ?>
                                                    <optgroup label="Otros miembros de la redacción">
                                                        <?php foreach ( $staff_list as $su ) :
                                                            if ( (int) $su->ID === (int) $current_user->ID ) continue;
                                                        ?>
                                                            <option value="user_<?php echo esc_attr( $su->ID ); ?>">
                                                                <?php echo esc_html( $su->display_name ); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endif; ?>
                                                <option value="custom_staff">Otra firma editorial personalizada...</option>
                                            </select>
                                        </div>

                                        <div id="eac-custom-staff-fields" class="eac-form-grid" style="display:none;">
                                            <div class="eac-field-group">
                                                <label for="eac-post-as-custom-name">Nombre del Autor/Redactor</label>
                                                <input type="text" name="eac_post_as_custom_name" id="eac-post-as-custom-name" placeholder="Ej. Pedro Ramírez" />
                                            </div>
                                            <div class="eac-field-group">
                                                <label for="eac-post-as-custom-role">Cargo o Rol en la nota</label>
                                                <input type="text" name="eac_post_as_custom_role" id="eac-post-as-custom-role" placeholder="Ej. Corresponsal ambiental" />
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php else : ?>
                                <div class="eac-form-grid">
                                    <div class="eac-field-group">
                                        <label for="eac-author">Nombre <span class="req">*</span></label>
                                        <input id="eac-author" name="author" type="text" value="<?php echo esc_attr( $commenter['comment_author'] ); ?>" placeholder="Tu nombre o firma" required />
                                    </div>
                                    <div class="eac-field-group">
                                        <label for="eac-location">Ubicación</label>
                                        <input id="eac-location" name="eac_user_location" type="text" value="" placeholder="Ej. Ciudad de México" />
                                    </div>
                                </div>
                                <div class="eac-field-group">
                                    <label for="eac-email">Correo electrónico <span class="req">*</span> <small>(No se publicará)</small></label>
                                    <input id="eac-email" name="email" type="email" value="<?php echo esc_attr( $commenter['comment_author_email'] ); ?>" placeholder="nombre@correo.com" required />
                                </div>
                            <?php endif; ?>

                            <div class="eac-field-group">
                                <label for="eac-comment-textarea">Tu comentario <span class="req">*</span></label>
                                <textarea id="eac-comment-textarea" name="comment" rows="4" placeholder="Comparte tu análisis, datos o perspectiva sobre esta nota..." required></textarea>
                            </div>

                            <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( $post_id ); ?>" />
                            <input type="hidden" name="comment_parent" id="eac-comment-parent" value="0" />

                            <div id="eac-form-feedback" class="eac-form-feedback" style="display:none;"></div>

                            <div class="eac-form-footer">
                                <p class="eac-editorial-policy">
                                    Los comentarios son moderados bajo los estándares editoriales de <em>El Ambientalista Post</em>.
                                </p>
                                <div class="eac-form-actions">
                                    <button type="button" id="eac-close-form-btn" class="eac-btn-ghost">Cancelar</button>
                                    <button type="submit" id="eac-submit-comment-btn" class="eac-btn-solid">Publicar comentario</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Lista completa de comentarios en el Drawer -->
                <div id="eac-drawer-comments-list" class="eac-drawer-comments-list">
                    <?php foreach ( $root_comments as $rc ) :
                        $c_id       = $rc->comment_ID;
                        $is_staff   = EAC_Comentarios_Helper::es_autor_o_redaccion( $rc );
                        $is_pick    = get_comment_meta( $c_id, '_eac_nyt_pick', true ) === '1';
                        $badge_text = get_comment_meta( $c_id, '_eac_badge_text', true ) ?: 'Selección de la redacción';
                        $likes      = (int) get_comment_meta( $c_id, '_eac_recommends', true );
                        $subtitle   = EAC_Comentarios_Helper::obtener_subtitulo( $rc, $is_staff, true );
                        $descendants = EAC_Comentarios_Helper::aplanar_descendientes( $c_id, $children_map );
                        $reply_count = count( $descendants );

                        // Verificar si en el hilo participa el staff para incluirlo también en el filtro "De la redacción"
                        $thread_has_staff = $is_staff;
                        foreach ( $descendants as $d ) {
                            if ( EAC_Comentarios_Helper::es_autor_o_redaccion( $d ) ) {
                                $thread_has_staff = true;
                                break;
                            }
                        }
                    ?>
                        <div class="eac-drawer-thread-item"
                             id="eac-thread-<?php echo esc_attr( $c_id ); ?>"
                             data-comment-id="<?php echo esc_attr( $c_id ); ?>"
                             data-timestamp="<?php echo esc_attr( strtotime( $rc->comment_date_gmt ) ); ?>"
                             data-likes="<?php echo esc_attr( $likes ); ?>"
                             data-is-pick="<?php echo ( $is_pick || $likes >= 5 ) ? '1' : '0'; ?>"
                             data-is-staff="<?php echo $thread_has_staff ? '1' : '0'; ?>">

                            <?php if ( $is_pick ) : ?>
                                <div class="eac-nyt-pick-banner">
                                    <span class="eac-pick-tag"><?php echo esc_html( $badge_text ); ?></span>
                                </div>
                            <?php endif; ?>

                            <article class="eac-comment-body">
                                <header class="eac-comment-meta">
                                    <?php echo EAC_Comentarios_Helper::render_avatar( $rc, $is_staff, 38 ); ?>
                                    <div class="eac-author-info">
                                        <div class="eac-author-line">
                                            <span class="eac-author-name"><?php echo esc_html( get_comment_author( $rc ) ); ?></span>
                                            <?php if ( $is_staff ) : ?>
                                                <span class="eac-staff-badge" title="Redacción · El Ambientalista Post">
                                                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="eac-staff-logo" width="18" height="18" />
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="eac-author-subtitle"><?php echo esc_html( $subtitle ); ?></div>
                                    </div>
                                </header>

                                <div class="eac-comment-content">
                                    <?php echo wpautop( esc_html( $rc->comment_content ) ); ?>
                                </div>

                                <footer class="eac-comment-footer-bar">
                                    <button type="button" class="eac-recommend-btn" data-comment-id="<?php echo esc_attr( $c_id ); ?>">
                                        <strong class="eac-rec-count"><?php echo number_format_i18n( $likes ); ?></strong> Recomiendo
                                    </button>

                                    <div class="eac-footer-right-actions">
                                        <?php if ( comments_open() ) : ?>
                                            <button type="button" class="eac-inline-reply-btn" data-reply-to="<?php echo esc_attr( $c_id ); ?>" data-author="<?php echo esc_attr( get_comment_author( $rc ) ); ?>">
                                                Responder
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="eac-share-btn" data-share-url="<?php echo esc_url( get_permalink( $post_id ) . '#comment-' . $c_id ); ?>" title="Copiar enlace al comentario">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M15 14l5-5-5-5"></path>
                                                <path d="M4 20v-7a4 4 0 0 1 4-4h12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </footer>
                            </article>

                            <?php if ( $reply_count > 0 ) : ?>
                                <div class="eac-drawer-replies-toggle-bar">
                                    <button type="button" class="eac-drawer-expand-replies-btn" data-expanded="0" data-count="<?php echo esc_attr( $reply_count ); ?>">
                                        <span><?php echo sprintf( _n( '%s Respuesta', '%s Respuestas', $reply_count, 'arp-elementor' ), number_format_i18n( $reply_count ) ); ?></span>
                                        <svg class="eac-chevron-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </button>
                                </div>

                                <div class="eac-drawer-children-container" style="display:none;">
                                    <?php foreach ( $descendants as $child ) :
                                        $ch_id       = $child->comment_ID;
                                        $ch_is_staff = EAC_Comentarios_Helper::es_autor_o_redaccion( $child );
                                        $ch_likes    = (int) get_comment_meta( $ch_id, '_eac_recommends', true );
                                        $ch_subtitle = EAC_Comentarios_Helper::obtener_subtitulo( $child, $ch_is_staff, true );
                                        $parent_obj  = isset( $comments_by_id[ $child->comment_parent ] ) ? $comments_by_id[ $child->comment_parent ] : null;
                                        $mention_str = $parent_obj ? '@' . get_comment_author( $parent_obj ) : '';
                                    ?>
                                        <div class="eac-drawer-child-item <?php echo $ch_is_staff ? 'is-staff-child' : ''; ?>" id="eac-comment-<?php echo esc_attr( $ch_id ); ?>">
                                            <article class="eac-comment-body">
                                                <header class="eac-comment-meta">
                                                    <?php echo EAC_Comentarios_Helper::render_avatar( $child, $ch_is_staff, 34 ); ?>
                                                    <div class="eac-author-info">
                                                        <div class="eac-author-line">
                                                            <span class="eac-author-name"><?php echo esc_html( get_comment_author( $child ) ); ?></span>
                                                            <?php if ( $ch_is_staff ) : ?>
                                                                <span class="eac-staff-badge" title="Redacción · El Ambientalista Post">
                                                                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="eac-staff-logo" width="18" height="18" />
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="eac-author-subtitle"><?php echo esc_html( $ch_subtitle ); ?></div>
                                                    </div>
                                                </header>

                                                <div class="eac-comment-content">
                                                    <?php if ( $mention_str && (int) $child->comment_parent !== (int) $rc->comment_ID ) : ?>
                                                        <p class="eac-reply-mention"><?php echo esc_html( $mention_str ); ?></p>
                                                    <?php endif; ?>
                                                    <?php echo wpautop( esc_html( $child->comment_content ) ); ?>
                                                </div>

                                                <footer class="eac-comment-footer-bar">
                                                    <button type="button" class="eac-recommend-btn" data-comment-id="<?php echo esc_attr( $ch_id ); ?>">
                                                        <strong class="eac-rec-count"><?php echo number_format_i18n( $ch_likes ); ?></strong> Recomiendo
                                                    </button>
                                                    <div class="eac-footer-right-actions">
                                                        <?php if ( comments_open() ) : ?>
                                                            <button type="button" class="eac-inline-reply-btn" data-reply-to="<?php echo esc_attr( $ch_id ); ?>" data-author="<?php echo esc_attr( get_comment_author( $child ) ); ?>">
                                                                Responder
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="eac-share-btn" data-share-url="<?php echo esc_url( get_permalink( $post_id ) . '#comment-' . $ch_id ); ?>" title="Copiar enlace">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M15 14l5-5-5-5"></path>
                                                                <path d="M4 20v-7a4 4 0 0 1 4-4h12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </footer>
                                            </article>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </aside>
</div>
