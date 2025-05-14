<?php

#[\AllowDynamicProperties]
class TemplateLoader implements TemplateLoaderInterface {

    public function init( PageInterface $page ) {
        $this->templates = wp_parse_args(
            array( 'page.php', 'index.php' ), (array) $page->getTemplate()
        );
    }

    public function wpscap_locate_template( $template_names, $load = false, $require_once = true ) {
        $located = '';

        foreach ( (array) $template_names as $template_name ) {            
            if ( ! $template_name ) {                
                continue;
            }

            
            if ( file_exists( plugin_dir_path( __DIR__ )  . 'templates/' . $template_name ) ) {                
                $located = plugin_dir_path( __DIR__ ) . 'templates/' . $template_name;                
                break;
            }
        }

        if ( $load && '' != $located ) {
            load_template( $located, $require_once );
        }

      

        return $located;
    }

    public function load() {
        do_action( 'template_redirect' );

       // $template = locate_template( array_filter( $this->templates ) );
        
        $template = $this->wpscap_locate_template(array_filter($this->templates), TRUE);

        $filtered = apply_filters( 'template_include',
            apply_filters( 'virtual_page_template', $template )
        );
        if ( empty( $filtered ) || file_exists( $filtered ) ) {
            $template = $filtered;
        }
        if ( ! empty( $template ) && file_exists( $template ) ) {
            require_once $template;
        }
    }
}