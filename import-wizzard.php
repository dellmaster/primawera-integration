<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


function rbit_import()
{

    wp_enqueue_script('rbit_import-js', plugins_url("/js/index.js", __FILE__), array('jquery'));
//    wp_enqueue_style('rbit_import-css', plugins_url("runbyitShortcode.css", __FILE__));


}
add_action('admin_enqueue_scripts', 'rbit_import');


function adminHead(){
    echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css">';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.css">';
}

add_action('admin_head', 'adminHead');


function adminFooter(){
    echo '<script src="https://code.jquery.com/jquery-3.5.1.js"></script>';
    echo '<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>';
    echo '<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>';
}

add_action('admin_head', 'adminFooter');


function rbit_primawera_import_wizzard() {
    $upload_dir = wp_upload_dir();
    $primawera_filename = $upload_dir['basedir'].'/primawera/primawera_full.xml';

    $primawera_cat_selected = ($_POST['primawera_cat_selected']) ? $_POST['primawera_cat_selected'] : false;
    $woo_cat_selected = ($_POST['woo_cat_selected']) ? $_POST['woo_cat_selected'] : false;
    $primawera_min_price = ($_POST['primawera_min_price']) ? $_POST['primawera_min_price'] : 0;

    $primawera_cats_option = get_option('rbit_primawera_categories');
    //$primawera_cats_to_skip_option = get_option('rbit_primawera_categories_to_skip');
    $primawera_cats_to_import_option = get_option('rbit_primawera_categories_to_import');
    $primawera_cats_min_price_option = get_option('rbit_primawera_categories_min_price');

    // get woo categories
    $orderby = 'name';
    $order = 'asc';
    $hide_empty = false ;
    $cat_args = array(
        'orderby'    => $orderby,
        'order'      => $order,
        'hide_empty' => $hide_empty,
    );

    $product_categories = get_terms( 'product_cat', $cat_args );

    $woo_cats_options = '';

    foreach ($product_categories as $product_category) {
        $woo_cat_selected_flag = ($product_category->term_id == $woo_cat_selected) ? 'selected' : '';
        $woo_cats_options .= '<option value="'.$product_category->term_id.'" '.$woo_cat_selected_flag.'>'.$product_category->name.'</option>';

    }
    $woo_categories_select_tag = '<select name="woo_cat_selected" id="woo_cat_selected" class="cats-select" >';
    $woo_categories_select_tag .= $woo_cats_options;
    $woo_categories_select_tag .= '</select>';

    print_r($_POST['rbit_primawera_products_to_import']);

    $primawera_cats_options = '';
    $primawera_cats_array = PrimaweraGetCatsFromFullFile($primawera_filename);
    foreach($primawera_cats_array as $primawera_cat) {
        if (!empty($primawera_cat)) {
            $primawera_cat_selected_flag = ($primawera_cat_selected == $primawera_cat) ? 'selected' :'';
            $primawera_cats_options .= '<option value="'.$primawera_cat.'" '.$primawera_cat_selected_flag.'>'.$primawera_cat.'</option>';
        }
    }

    $categories_select_tag = '<select name="primawera_cat_selected" id="primawera_cat_selected" class="cats-select" >';
    $categories_select_tag .= $primawera_cats_options;
    $categories_select_tag .= '</select>';


    ?>
    <form action="" method="post">
    <div style="" class="rbit-import-block">

        <h2>Products import wizzard</h2>
        From:
        <?php echo $categories_select_tag; ?>
         To:
        <?php echo $woo_categories_select_tag; ?>
         Min price:
        <input type="number" name="primawera_min_price" min="0" value="<?php echo $primawera_min_price; ?>">
        <button type="submit" name="primawera_action" value="show_products">Show products</button>


    </div>
    <?php if('show_products' == $_POST['primawera_action'])  {
        $products_for_category = PrimaweraGetProductsFromFullFileForCategory($primawera_filename, $primawera_cat_selected);

        ?>
    <div style="margin-top: 50px;" class="rbit-import-block">
        <br>
        <br>
        <button class="import-button button button-primary" type="submit" name="primawera_action" value="import_products">Import products</button>
        <br>
        <span class="mt-2 import-button button button-primary" id="select-checkboxes" name="primawera_action" value="">Zaznacz wszystkie widoczne opcje</span>
        <br>
        <span class="mt-2 import-button button button-primary" id="unselect-checkboxes" name="primawera_action" value="">Odznacz wszystkie widoczne opcje</span>
        <br>
        Products: <?php echo count($products_for_category); ?>
        <table class="table" id="primavera">
            <thead>

            <tr>
                <th><input class="form-control input-xs table-filter" type="text" placeholder="szukaj" id="import"></th>
                <th><input class="form-control input-xs table-filter" type="text" placeholder="szukaj EAN" id="ean"></th>
                <th><input class="form-control input-xs table-filter" type="text" placeholder="szukaj Nazwa produktu" id="name"></th>
                <th><input class="form-control input-xs table-filter" type="text" placeholder="szukaj Marka" id="import"></th>
                <th><input class="form-control input-xs table-filter" type="text" placeholder="szukaj Dostępność" id="anavibility"></th>
                <th><input class="form-control input-xs table-filter" type="text" placeholder="szukaj Cena"id="price"></th>

            </tr>
            <tr>
                <th>Import</th>
                <th>EAN</th>
                <th>Nazwa produktu</th>
                <th>Marka</th>
                <th>Dostępność</th>
                <th>Cena</th>

            </tr>


            </thead>

            <?php

            foreach ($products_for_category as $product) {
                ?>
                <tr>
                    <td>
                        <input type="checkbox" id="rbit_primawera_categories_to_import" name="rbit_primawera_products_to_import[<?php echo $product['ean']; ?>]"  >
                    </td>
                    <td>
                        <?php echo $product['ean']; ?>
                    </td>
                    <td>
                        <?php echo $product['name']; ?>
                    </td>
                    <td>
                        <?php echo $product['brand']; ?>
                    </td>
                    <td>
                        <?php echo $product['available']; ?>
                    </td>
                    <td>
                        <?php echo $product['price']; ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tfoot>
            <tr>
                <th>Import</th>
                <th>EAN</th>
                <th>Nazwa</th>
                <th>Marka</th>
                <th>Dostępność</th>
                <th>Cena</th>
            </tr>
            </tfoot>
        </table>

        <button class="import-button button button-primary" type="submit" name="primawera_action" value="import_products">Import products</button>
    </div>
        <?php } ?>
    </form>

<?php
}


