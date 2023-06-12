<?php

function PrimaweraSaveFullFile(){
    $remote_file_url = 'https://products.pvex.pl/services/products_info/feed/primavera/feed_primavera';
    if ('import_full_file' == $_POST['primawera_action']) {
        $upload_dir = wp_upload_dir();
        $primawera_filename = $upload_dir['basedir'].'/primawera/primawera_full.xml';

        $content = file_get_contents($remote_file_url);

        file_put_contents($primawera_filename, $content);

        ?>
        <div style="" class="rbit-settings-block">
            <br>
            <br>
            <h2>Zaimportowano plik</h2>
        </div>
        <?php
        //echo 'Zaimportowano plik'.PHP_EOL;
    }
    ?>
    <div style="" class="rbit-settings-block">
            <br>
            <br>
            <h2><?php echo __('Import full Primawera file', 'rbit-orders-primawera');?></h2>
            <form method="post" action="">
                <span>XML file - </span><?php echo $remote_file_url; ?>
                <input type="hidden" name="primawera_action" value="import_full_file">
                <input name="submit" class="button button-primary" type="submit" value="<?php echo __('Save', 'rbit-orders-primawera'); ?>" />
            </form>
    </div>
    <?php

}

function CliPrimaweraSaveFullFile(){
    WP_CLI::log('Import start');
    $upload_dir = wp_upload_dir();
    $content = file_get_contents('https://products.pvex.pl/services/products_info/feed/primavera/feed_primavera');
    WP_CLI::log('file downloaded');
    $primawera_filename = $upload_dir['basedir'].'/primawera/primawera_full.xml';
    if(file_put_contents($primawera_filename, $content)) {
        WP_CLI::log('Zaimportowano plik');
        WP_CLI::log('file size - '.strlen($content));
    }
    else {
        WP_CLI::log('file save bug');
        WP_CLI::log('file size - '.strlen($content));
    }


}

function rbit_cli_register_commands_primawera_full_file_download() {
    WP_CLI::add_command( 'primawera_full_file_download', 'CliPrimaweraSaveFullFile' );
}

add_action( 'cli_init', 'rbit_cli_register_commands_primawera_full_file_download' );


function CliPrimaweraSavePriceFile(){
    $upload_dir = wp_upload_dir();
    $content = file_get_contents('https://orders.pvex.pl/pricelist.php?wh=all&token=404D9EC0AA35F0255B392D2A8F20091B8518&format=xml');
    $primawera_filename = $upload_dir['basedir'].'/primawera/primawera_prices.xml';
    if(file_put_contents($primawera_filename, $content)) {
        WP_CLI::log('Zaimportowano plik');
        WP_CLI::log('file size - '.strlen($content));
    }
    else {
        WP_CLI::log('file save bug');
        WP_CLI::log('file size - '.strlen($content));
    }


}

function rbit_cli_register_commands_primawera_price_file_download() {
    WP_CLI::add_command( 'primawera_price_file_download', 'CliPrimaweraSavePriceFile' );
}

add_action( 'cli_init', 'rbit_cli_register_commands_primawera_price_file_download' );
