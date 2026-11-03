public function plugin_popup( $result, $action, $args ) {
    if ( 'plugin_information' !== $action ) {
        return $result;
    }

    $this->init();

    if ( ! isset( $args->slug ) || $args->slug !== $this->repository ) {
        return $result;
    }

    $this->get_repository_info();

    if ( ! $this->github_response ) {
        return $result;
    }

    $plugin_data = get_plugin_data( $this->file );

    $info = new stdClass();
    $info->name = 'El Ambientalista Core';
    $info->slug = $this->repository;
    $info->version = ltrim( $this->github_response->tag_name, 'v' );
    $info->author = '<a href="https://elambientalistapost.com">Marcos Abraham Pérez Rosas</a>';
    $info->homepage = $plugin_data['PluginURI'];
    
    // Pestañas profesionales en la ventana modal
    $info->sections = [
        'description' => 'Núcleo de módulos periodísticos y de investigación para El Ambientalista Post. Incluye gestión avanzada de contenidos, sistema de comentarios y diseño editorial especializado.',
        'changelog'   => $this->github_response->body ? nl2br( esc_html( $this->github_response->body ) ) : 'Actualización general del sistema y mejoras de rendimiento.',
        'installation'=> '1. Sube el plugin a través de GitHub o descárgalo como release.<br>2. Actívalo desde el panel de administración de WordPress.<br>3. El sistema gestionará las actualizaciones automáticas desde GitHub.'
    ];

    // Añadir banner visual superior opcional para la ventana modal
    $info->banners = [
        'low' => 'https://elambientalistapost.com/wp-content/uploads/banner-plugin-low.jpg', // Opcional: URL de una imagen banner de 772x250
        'high' => 'https://elambientalistapost.com/wp-content/uploads/banner-plugin-high.jpg'
    ];
    
    return $info;
}