<?php
function startorg_child_theme_enqueue_styles() {
    $parent_style = 'startorg-style'; // Identificador do tema pai

    // Enfileira o CSS do tema pai
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );

    // Enfileira o CSS do tema filho
    wp_enqueue_style( 'startorg-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version') // Usa a versão do tema filho para controle de cache
    );

    // // Enfileira o CSS para dispositivos móveis
    // wp_enqueue_style( 'mobile-styles',
    //     get_stylesheet_directory_uri() . '/mobile-styles.css',
    //     array( 'startorg-child-style' ),
    //     wp_get_theme()->get('Version'),
    //     'all'
    // );
}
// add_action( 'wp_enqueue_scripts', 'startorg_child_theme_enqueue_styles' );

function enqueue_styles_with_priority() {
    // // CSS global
    // wp_enqueue_style(
    //     'css-global',
    //     get_stylesheet_directory_uri() . '/style.css',
    //     [],
    //     filemtime(get_stylesheet_directory() . '/style.css')
    // );
    // // CSS Desktop
    // wp_enqueue_style(
    //     'css-desktop',
    //     get_stylesheet_directory_uri() . '/style.css',
    //     [],
    //     filemtime(get_stylesheet_directory() . '/desktop-style.css'),
    //     'screen and (min-width: 768px)'
    // );

    // // CSS Mobile
    // wp_enqueue_style(
    //     'css-mobile',
    //     get_stylesheet_directory_uri() . '/mobile-styles.css',
    //     [],
    //     filemtime(get_stylesheet_directory() . '/mobile-styles.css'),
    //     'screen and (max-width: 767px)'
    // );

    // CSS Mobile
    wp_enqueue_style(
        'css-combined',
        get_stylesheet_directory_uri() . '/bng-style.css',
        [],
        filemtime(get_stylesheet_directory() . '/bng-style.css')
    );
}
add_action('wp_enqueue_scripts', 'enqueue_styles_with_priority');

add_theme_support('align-wide');

add_action('after_setup_theme', function() {
    // Limpa o Cache de Opções do Tema
    delete_option('theme_mods_' . get_stylesheet());
});

// Registrar e carregar o JavaScript personalizado
function startorg_enqueue_custom_scripts() {
  // Registrar o arquivo JS
  wp_enqueue_script(
      'pain-list-script', // Nome único para o script
      get_stylesheet_directory_uri() . '/javascript/pain-slider.js', // Caminho para o arquivo custom.js
      array('jquery'), // Dependência do jQuery (se necessário)
      null, // Versão do arquivo (deixe null para evitar cache durante o desenvolvimento)
      true // Carregar no footer
  );
  wp_enqueue_script(
      'mvv-list-script', // Nome único para o script
      get_stylesheet_directory_uri() . '/javascript/mvv-slider.js', // Caminho para o arquivo custom.js
      array('jquery'), // Dependência do jQuery (se necessário)
      null, // Versão do arquivo (deixe null para evitar cache durante o desenvolvimento)
      true // Carregar no footer
  );
  wp_enqueue_script(
      'custom-script', // Nome único para o script
      get_stylesheet_directory_uri() . '/javascript/doubts-open-answer.js', // Caminho para o arquivo custom.js
      array('jquery'), // Dependência do jQuery (se necessário)
      null, // Versão do arquivo (deixe null para evitar cache durante o desenvolvimento)
      true // Carregar no footer
  );
}
add_action('wp_enqueue_scripts', 'startorg_enqueue_custom_scripts');

// add_action('init', function () {
//   // Caminho para o arquivo do pattern no tema filho
//   $pattern_file = get_stylesheet_directory() . '/patterns/footer-global.php';

//   // Verifica se o arquivo existe antes de registrar o pattern
//   if (file_exists($pattern_file)) {
//       $pattern_content = file_get_contents($pattern_file);

//       // Registra o pattern
//       register_block_pattern(
//           'startorg-child/footer-global', // Identificador único
//           [
//               'title'   => __('Footer Global', 'startorg-child'), // Título amigável exibido no editor
//               'content' => $pattern_content, // Conteúdo do arquivo PHP
//           ]
//       );
//   }
// });


// Função para criar páginas iniciais automaticamente
function create_theme_pages() {
    $current_theme = wp_get_theme();
    if ($current_theme->get('Name') !== 'Boas Novas Gestão') {
        return;
    }

    // Verifica se a função é executada apenas uma vez
    if (get_option('theme_pages_created')) {
        return;
    }

    // Diretório da pasta de padrões
    $pattern_dir = '/var/www/html/wp-content/themes/startorg-child/patterns';

    // Verifica se a pasta existe
    if (!is_dir($pattern_dir)) {
        error_log('Pasta pattern não encontrada no tema.');
        return;
    }

    // Obtém todos os arquivos .php no diretório
    $pattern_files = glob($pattern_dir . '/*.php');

    // Relaciona apenas os patterns que devem ter uma página
    $pages = [
        'about-us' => 'Quem somos',
        'institutions' => 'Instituicoes',
        'doctors' => 'Medicos',
        'join-team' => 'Faca parte do time',
        'contacts' => 'contatos'
    ];

    // Cria páginas com base nos padrões encontrados
    foreach ($pattern_files as $file_path) {
        // Extrai o nome do arquivo sem extensão
        $file_name = basename($file_path, '.php');

        // Skipa se o pattern não for de uma página
        if (!array_key_exists($file_name,$pages)) {
            continue;
        }
        
        // Título da página (primeira letra maiúscula)
        $page_name = $pages[$file_name];

        // Verifica se a página já existe
        $page_exists = get_page_by_path($page_name);

        if (!$page_exists) {
            // Cria a página
            wp_insert_post([
                'post_title'   => $page_name,
                'post_name'   => $page_name,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'meta_input'   => [
                    '_wp_page_template' => "$file_name", // Define o template da página
                ],
            ]);
        }
    }

    // Marca como executado
    update_option('theme_pages_created', true);
}
add_action('after_switch_theme', 'create_theme_pages');

add_action('admin_notices', function() {
    $patterns = WP_Block_Patterns_Registry::get_instance()->get_all_registered();
    echo '<pre>' . print_r(array_keys($patterns), true) . '</pre>';
});


add_action('init', function() {
    register_block_pattern(
        'startorg-child/doctors',
        array(
            'title'       => __('Lista de Doutores', 'startorg-child'),
            'description' => __('Um padrão para exibir doutores', 'startorg-child'),
            'categories'  => array('layout'),
            'content'     => '<div class="doctors-list"><p>Aqui vai o conteúdo do padrão atualizado...</p></div>',
        )
    );
});
