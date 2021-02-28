<?php 

if ( ! function_exists( 'fe_fs' ) ) {
    // Create a helper function for easy SDK access.
    function fe_fs() {
        global $fe_fs;

        if ( ! isset( $fe_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $fe_fs = fs_dynamic_init( array(
                'id'                  => '7886',
                'slug'                => 'front-editor',
                'type'                => 'plugin',
                'public_key'          => 'pk_721b5ebdb9cda3d26691a9fb5c35c',
                'is_premium'          => false,
                // If your plugin is a serviceware, set this option to false.
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'front_editor_settings',
                ),
                // Set the SDK to work in a sandbox mode (for development & testing).
                // IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
                'secret_key'          => 'sk_0c1aMUbAp.N+26}=q4q?K2-GL!3RT',
            ) );
        }

        return $fe_fs;
    }

    // Init Freemius.
    fe_fs();
    // Signal that SDK was initiated.
    do_action( 'fe_fs_loaded' );
}

/**
 * WP admin bar settings disable or not
 */
add_filter('show_admin_bar','disable_wp_admin_bar');

function disable_wp_admin_bar(){
    $options = get_option('bfe_front_editor_wp_admin_menu');

    if(empty($options)){
        return true;
    }

    if($options === 'display'){
        return true;
    }
    
    if($options === 'disable'){
        return false;
    }

    if($options === 'disable_but_admin'){
        $user = wp_get_current_user();
        if( current_user_can('administrator') ){
            return true;
        } 

        return false;
    }

    return true;
}

/**
 * Users can see only oun attachments
 */
add_action('pre_get_posts','users_own_attachments');

function users_own_attachments( $wp_query_obj ) {

    global $current_user, $pagenow;

    $is_attachment_request = ($wp_query_obj->get('post_type')=='attachment');

    if( !$is_attachment_request )
        return;

    if( !is_a( $current_user, 'WP_User') )
        return;

    if( !in_array( $pagenow, array( 'upload.php', 'admin-ajax.php' ) ) )
        return;

    if( !current_user_can('delete_pages') )
        $wp_query_obj->set('author', $current_user->ID );

    return;
}