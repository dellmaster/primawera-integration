<?php
/*

Plugin Name: RunByIT Primawera integration
Plugin URI: https://runbyit.com/
Description: Plugin for import products and export orders to Primawera
Version: 1.0.0
Author: Oleksii Yurchenko
Author URI: https://runbyit.com/
Text Domain: rbit-orders-primawera

*/

require_once 'categories-mapping.php';
require_once 'primawera_file_import.php';
require_once 'import-wizzard.php';
require_once 'import-functions.php';
require_once 'import-test.php';
require_once 'order-export.php';

function rbit_is_shipping_admin_styles() {

    wp_enqueue_style( 'rbit-primawera-admin-styles',  plugin_dir_url(__FILE__) . 'css/rbit-primawera-admin.css?vr=' .time() );

    //wp_enqueue_script( 'rbit-primawera-admin-scripts', plugin_dir_url(__FILE__).'js/rbit-is-shipping-admin.js?v='.time(), array('jquery'), null, true );
}

add_action( 'admin_enqueue_scripts', 'rbit_is_shipping_admin_styles' );

add_action('admin_menu', 'rbit_primavera_export_menu');

function rbit_primavera_export_menu()
{
    add_menu_page( 'Primawera settings', 'Primawera', 'manage_options', 'rbit_primawera_slug', 'rbit_primawera_settings', 'dashicons-screenoptions' ); //'data:image/svg+xml;base64,' . base64_encode( $icon ),

    add_submenu_page( 'rbit_primawera_slug', 'Export Settings', 'Primawera export Settings', 'manage_options', 'rbit_primawera_settings_slug', 'rbit_primawera_settings');

    //add_submenu_page( 'rbit_primawera_slug', 'File download', 'Full file', 'manage_options', 'rbit_primawera_full_file_slug', 'PrimaweraSaveFullFile');

    add_submenu_page( 'rbit_primawera_slug', 'Categories', 'Categories mapping', 'manage_options', 'rbit_primawera_categories_slug', 'rbit_primawera_category_import');
    add_submenu_page( 'rbit_primawera_slug', 'Products', 'Import wizzard', 'manage_options', 'rbit_primawera_products_import_slug', 'rbit_primawera_import_wizzard');


}

// START Settings Page
function rbit_primawera_settings(){
    //delete_option( 'rbit_primawera_full_file' );
    //delete_option( 'rbit_primawera_date_file' );
    //delete_option( 'rbit_primawera_quantity_file' );
    ?>
    <div class="wrap">
        <h1><?php echo get_admin_page_title() ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'rbit_primawera_settings' ); // settings group name
            do_settings_sections( 'rbit_primawera_settings_slug' ); // just a page slug
            submit_button(); // "Save Changes" button
            ?>
        </form>
    </div>
    <?php
}


add_action( 'admin_init',  'rbit_primawera_settings_fields' );
function rbit_primawera_settings_fields(){

    // I created variables to make the things clearer
    $page_slug = 'rbit_primawera_settings_slug';
    $option_group = 'rbit_primawera_settings';
    //$export_option_group = 'rbit_primawera_export_settings';

    // 1. create section
    add_settings_section(
        'rbit_primawera_section_id', // section ID
        'Import settings', // title (optional)
        '', // callback function to display the section (optional)
        $page_slug
    );

    add_settings_section(
        'rbit_primawera_export_section_id', // section ID
        'Export settings', // title (optional)
        '', // callback function to display the section (optional)
        $page_slug
    );

    // 2. register fields
    //

    register_setting(
        $option_group,
        'rbit_primawera_full_file'
    );

    register_setting(
        $option_group,
        'rbit_primawera_date_file'
    );

    register_setting(
        $option_group,
        'rbit_primawera_quantity_file'
    );

    register_setting(
        $option_group,
        'rbit_primawera_api_url'
    );

    register_setting(
        $option_group,
        'rbit_primawera_api_login'
    );

    register_setting(
        $option_group,
        'rbit_primawera_api_pass'

    );

    // 3. add fields
    add_settings_field(
        'rbit_primawera_full_file',
        __('Primawera full products file url', 'rbit-orders-primawera'),
        'rbit_primawera_string_field_display', // function to print the field
        $page_slug,
        'rbit_primawera_section_id', // section ID
        array(
            'name'    => 'rbit_primawera_full_file',
        )
    );

    add_settings_field(
        'rbit_primawera_date_file',
        __('Primawera products for date file url', 'rbit-orders-primawera'),
        'rbit_primawera_string_field_display', // function to print the field
        $page_slug,
        'rbit_primawera_section_id', // section ID
        array(
            'name'    => 'rbit_primawera_date_file',
        )
    );

    add_settings_field(
        'rbit_primawera_quantity_file',
        __('Primawera products quantity url', 'rbit-orders-primawera'),
        'rbit_primawera_string_field_display', // function to print the field
        $page_slug,
        'rbit_primawera_section_id', // section ID
        array(
            'name'    => 'rbit_primawera_quantity_file',
        )
    );

    add_settings_field(
        'rbit_primawera_api_url',
        'Primawera API Url',
        'rbit_primawera_string_field_display', // function to print the field
        $page_slug,
        'rbit_primawera_export_section_id', // section ID
        array(
            'name'    => 'rbit_primawera_api_url',
        )
    );

    add_settings_field(
        'rbit_primawera_api_login',
        'Primawera API login',
        'rbit_primawera_string_field_display', // function to print the field
        $page_slug,
        'rbit_primawera_export_section_id', // section ID
        array(
            'name'    => 'rbit_primawera_api_login',
        )
    );

    add_settings_field(
        'rbit_primawera_api_password',
        'Primawera API password',
        'rbit_primawera_string_field_display', // function to print the field
        $page_slug,
        'rbit_primawera_export_section_id', // section ID
        array(
            'name'    => 'rbit_primawera_api_password',
        )
    );



}

// custom callback function to print field HTML
function rbit_primawera_string_field_display( $args ){
    printf(
        '<input type="text" id="%s" name="%s" value="%d" />',
        $args[ 'name' ],
        $args[ 'name' ],
        get_option( $args[ 'name' ], 'a' ) //
    );

}


// show admin notice
add_action( 'admin_notices', 'rbit_primawera_admin_notice' );

function rbit_primawera_admin_notice() {

    if(
        isset( $_GET[ 'page' ] )
        && 'rbit_primawera_settings_slug' == $_GET[ 'page' ]
        && isset( $_GET[ 'settings-updated' ] )
        && true == $_GET[ 'settings-updated' ]
    ) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php echo __('Primawera settings saved.', 'rbit-orders-primawera');?></strong>
            </p>
        </div>
        <?php
    }

}
// END Settings Page
