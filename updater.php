class ElAmbientalista_Updater {
    private $file;
    private $plugin;
    private $basename;
    private $active;
    private $username;
    private $repository;
    private $authorize_token;
    private $github_response;

    public function __construct( $file ) {
        $this->file = $file;
       $this->username = 'elambmex'; // Tu usuario real de GitHub
$this->repository = 'el-ambientalista-core'; // Nombre exacto del repositorio
        $this->authorize_token = ''; // Déjalo vacío si el repositorio es público

        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'modify_plugin_transient' ] );
        add_filter( 'plugins_api', [ $this, 'plugin_popup' ], 10, 3 );
        add_filter( 'upgrader_post_install', [ $this, 'after_install' ], 10, 3 );
    }

    private function init() {
        $this->basename = plugin_basename( $this->file );
        $this->active = is_plugin_active( $this->basename );
    }

    private function get_repository_info() {
        if ( is_array( $this->github_response ) ) {
            return;
        }

        $request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository );
        $args = [];

        if ( ! empty( $this->authorize_token ) ) {
            $args['headers'] = [ 'Authorization' => "token {$this->authorize_token}" ];
        }

        $response = wp_remote_get( $request_uri, $args );

        if ( is_wp_error( $response ) ) {
            return;
        }

        $body = wp_remote_retrieve_body( $response );
        $this->github_response = json_decode( $body );
    }

    public function modify_plugin_transient( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $this->init();
        $this->get_repository_info();

        if ( $this->github_response && property_exists( $this->github_response, 'tag_name' ) ) {
            $version = ltrim( $this->github_response->tag_name, 'v' );
            $plugin_data = get_plugin_data( $this->file );

            if ( version_compare( $plugin_data['Version'], $version, '<' ) ) {
                $obj = new stdClass();
                $obj->slug = $this->repository;
                $obj->plugin = $this->basename;
                $obj->new_version = $version;
                $obj->url = $this->github_response->html_url;
                $obj->package = $this->github_response->zipball_url;
                $transient->response[$this->basename] = $obj;
            }
        }

        return $transient;
    }

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
        $info->name = $plugin_data['Name'];
        $info->slug = $this->repository;
        $info->version = ltrim( $this->github_response->tag_name, 'v' );
        $info->author = $plugin_data['AuthorName'];
        $info->homepage = $plugin_data['PluginURI'];
        $info->sections = [
            'description' => $plugin_data['Description'],
            'changelog' => $this->github_response->body
        ];
        
        return $info;
    }

    public function after_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;

        $this->init();
        $install_directory = plugin_dir_path( $this->file );
        
        if ( $hook_extra['plugin'] === $this->basename ) {
            // Limpieza y renombrado de la carpeta descargada de GitHub al nombre original del plugin
            $slug = 'tu-usuario-' . $this->repository; // GitHub suele añadir el sufijo del usuario/rama al zip
            // Nota: Se recomienda asegurar que la carpeta descomprimida coincida con 'el-ambientalista-core'
        }

        return $result;
    }
}

// Inicializar el sistema de actualizaciones en el archivo principal
new ElAmbientalista_Updater( __FILE__ );