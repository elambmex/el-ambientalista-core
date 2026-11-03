<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class EAC_Widget_Autor_Comentarios extends \\Elementor\\Widget_Base {

    public function get_name() {
        return 'eac_autor_comentarios_widget';
    }

    public function get_title() {
        return 'Comentarios del Autor (Historial)';
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    public function get_categories() {
        return [ 'elambientalista', 'general' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => 'Configuración',
                'tab'   => \\Elementor\\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label'   => 'Título de la sección',
                'type'    => \\Elementor\\Controls_Manager::TEXT,
                'default' => 'Comentarios y respuestas del autor',
            ]
        );

        $this->add_control(
            'number',
            [
                'label'   => 'Número de comentarios a mostrar',
                'type'    => \\Elementor\\Controls_Manager::NUMBER,
                'default' => 10,
                'min'     => 1,
                'max'     => 50,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings  = $this->get_settings_for_display();
        $author_id = get_queried_object_id();

        if ( ! $author_id || ! is_author() ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p style="padding:15px; background:#f9f9f7; border:1px solid #ddd;">[Widget de Comentarios del Autor: Se mostrará en las páginas de archivo de autor /author/usuario/]</p>';
            }
            return;
        }

        $user_obj = get_userdata( $author_id );
        if ( ! $user_obj ) return;

        $number_comments = ! empty( $settings['number'] ) ? intval( $settings['number'] ) : 10;

        // Consultar comentarios creados por este usuario específico
        $comments = get_comments( [
            'user_id' => $author_id,
            'status'  => 'approve',
            'number'  => $number_comments,
            'orderby' => 'comment_date',
            'order'   => 'DESC',
        ] );
        ?>

        <div class="eac-author-comments-wrapper">
            <div class="eac-author-comments-header">
                <h3 class="eac-author-comments-title"><?php echo esc_html( $settings['title'] ); ?></h3>
                <span class="eac-author-count"><?php echo count( $comments ); ?> intervenciones</span>
            </div>

            <?php if ( ! empty( $comments ) ) : ?>
                <div class="eac-author-comments-list">
                    <?php foreach ( $comments as $comment ) :
                        $post_id    = $comment->comment_post_ID;
                        $post_title = get_the_title( $post_id );
                        $post_url   = get_permalink( $post_id );
                        $fecha      = get_comment_date( 'j \d\e F \d\e Y', $comment );
                        $is_reply   = (int) $comment->comment_parent > 0;
                    ?>
                        <article class="eac-author-comment-item">
                            <div class="eac-item-context">
                                <span class="eac-context-tag"><?php echo $is_reply ? 'Respuesta en el artículo' : 'Comentario en el artículo'; ?></span>
                                <a href="<?php echo esc_url( $post_url ); ?>" class="eac-context-post-title"><?php echo esc_html( $post_title ); ?></a>
                            </div>

                            <div class="eac-item-content">
                                <?php echo wpautop( esc_html( $comment->comment_content ) ); ?>
                            </div>

                            <footer class="eac-item-footer">
                                <span class="eac-item-date"><?php echo esc_html( $fecha ); ?></span>
                                <a href="<?php echo esc_url( $post_url . '#comment-' . $comment->comment_ID ); ?>" class="eac-item-link">Ver en contexto &rsaquo;</a>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="eac-author-no-comments">Este autor aún no ha registrado comentarios o respuestas en las publicaciones.</p>
            <?php endif; ?>
        </div>

        <?php
    }
}