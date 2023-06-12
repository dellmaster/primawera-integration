<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
// Categories mapping callback function
function rbit_primawera_category_import() {

    $default_category_id = 5861;
    //delete_option('rbit_dd_categories');
    // START save mapping options
    if ('save_mapping' == $_POST['primawera_action']) {
        update_option('rbit_primawera_categories', $_POST['rbit_primawera_categories']);
        //update_option('rbit_primawera_categories_to_skip', $_POST['rbit_primawera_categories_to_skip']);
        update_option('rbit_primawera_categories_to_import', $_POST['rbit_primawera_categories_to_import']);
        update_option('rbit_primawera_categories_min_price', $_POST['rbit_primawera_categories_min_price']);
        echo 'Data updated<br>';
        //echo '<pre>';
        //print_r($_POST['rbit_primawera_categories_to_import']);
        //echo '</pre>';
    }
    // END save mapping options

    $primawera_cats_option = get_option('rbit_primawera_categories');
    //$primawera_cats_to_skip_option = get_option('rbit_primawera_categories_to_skip');
    $primawera_cats_to_import_option = get_option('rbit_primawera_categories_to_import');
    $primawera_cats_min_price_option = get_option('rbit_primawera_categories_min_price');
    //$dd_cats_default_description_option = get_option('rbit_dd_categories_default_description');
    //$dd_cats_ceneo_import_option = get_option('rbit_dd_categories_ceneo_import');
    //if (!$dd_cats) add_option('rbit_dd_categories', '', $deprecated = '', $autoload = 'yes');
    $upload_dir = wp_upload_dir();
    $primawera_filename = $upload_dir['basedir'].'/primawera/primawera_full.xml';

    //$primawera_array = PrimaweraFullFiletoArray($primawera_filename);

    //echo '<pre>';
    //print_r($primawera_cats_option);
    //echo '</pre>';

    //echo '<pre>';
    //print_r($dd_array[7619]);
    //echo '</pre>';

    //$primawera_cats_array = PrimaweraGetCtegories($primawera_array);
    $primawera_cats_array = PrimaweraGetCatsFromFullFile($primawera_filename);

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

    ?>

    <div style="" class="rbit-settings-block">
        <h2>Primawera categories mapping</h2>
        <br>
        <br>
        <form action="" method="post">
            <span class="rbit-action-text">Save categories mapping: </span><input name="submit" class="button button-primary" type="submit" value="<?php echo "Save"; ?>" />
            <table class="rbit_table">
                <tr>
                    <th>
                        Import
                    </th>
                    <th>
                        Kategoria Primawera
                    </th>
                    <th>
                        Kategoria w Zapachnisci
                    </th>
                    <th>
                        min price
                    </th>
                </tr>

                <?php
                $cat_num = 0;
                foreach($primawera_cats_array as $primawera_cat)
                {
                    if (!empty($primawera_cat)) {
                        $cat_num++;
                        $categories_select_tag_not_first_options = '';
                        $categories_select_tag_first_option = '';
                        foreach ($product_categories as $product_category) {
                            $cat_selected = ($product_category->term_id == $primawera_cats_option[$primawera_cat]) ? 'selected' : '';
                            if ($default_category_id == $product_category->term_id) {
                                $categories_select_tag_first_option = '<option value="'.$product_category->term_id.'" '.$cat_selected.'>'.$product_category->name.'</option>';
                            }
                            else {
                                $categories_select_tag_not_first_options .= '<option value="'.$product_category->term_id.'" '.$cat_selected.' >'.$product_category->name.'</option>';
                            }

                        }

                        $categories_select_tag_options = $categories_select_tag_first_option.$categories_select_tag_not_first_options;

                        $categories_select_tag = '<select name="rbit_primawera_categories['.$primawera_cat.']" id="rbit_primawera_categories" class="is-cats-select" >';
                        $categories_select_tag .= $categories_select_tag_options;
                        $categories_select_tag .= '</select>';


                        $import_checked = (isset($primawera_cats_to_import_option[$primawera_cat]) && $primawera_cats_to_import_option[$primawera_cat]) ? 'checked' : '';
                        //$is_cat_selected = in_array($dd_cats_option[$dd_cat], )
                        $price_min = isset($primawera_cats_min_price_option[$primawera_cat]) ? $primawera_cats_min_price_option[$primawera_cat] : 10;
                        ?>
                        <tr class="">
                            <td>
                                <input type="checkbox" id="rbit_primawera_categories_to_import" name="rbit_primawera_categories_to_import[<?php echo $primawera_cat; ?>]" <?php echo $import_checked; ?> >
                            </td>
                            <td>
                                <?php echo $primawera_cat; ?>
                            </td>
                            <td>
                                <?php echo $categories_select_tag; ?>
                            </td>
                            <td>
                                <input type="number" id="rbit_primawera_categories_min_price" name="rbit_primawera_categories_min_price[<?php echo $primawera_cat; ?>]" min="0" max="100" value="<? echo $price_min; ?>">
                            </td>
                        </tr>
                        <?php
                    }

                }
                ?>

            </table>
            <span class="rbit-action-text">Save categories mapping: </span><input type="hidden" name="primawera_action" value="save_mapping">
            <input name="submit" class="button button-primary" type="submit" value="<?php echo "Save"; ?>" />
        </form>
    </div>

    <?php

}

function PrimaweraFullFiletoArray($primawera_filename){
    //return json_decode(json_encode(simplexml_load_string(file_get_contents($primawera_filename), 'SimpleXMLElement', LIBXML_NOCDATA),true), true)['products'];
    return simplexml_load_string(file_get_contents($primawera_filename), 'SimpleXMLElement', LIBXML_NOCDATA);
}

function PrimaweraGetCtegories($products) {
    $categories = [];
    foreach($products as $key => $product){
        if (is_array($product['categories'])) {
            foreach($product['categories'] as $cat){
                if(!in_array($cat, $categories))
                    $categories[] = $cat;
            }
        }
        else {
            //print_r($product['categories_names']);
            //echo "key - $key |";
        }

    }
    return $categories;
}
function PrimaweraGetCatsFromFullFile($xmlFile) {
    $cats_array = [];
    $primEL = 'product';

    $xml = new XMLReader();
    $xml->open($xmlFile);

    // finding first primary element to work with
    while($xml->read() && $xml->name != $primEL){;}

    // looping through elements
    while($xml->name == $primEL) {
        // loading element data into simpleXML object
        try {
            $element = new SimpleXMLElement($xml->readOuterXML());
            //$element = new SimpleXMLElement($xml->readOuterXML());
            $categories = $element->xpath('/product/categories/category');
            foreach ($categories as $category) {
                $cat_string = (string)$category;
                //print_r($cat_string);
                //echo $category;
                //echo '<br>';
                $cats_array[$cat_string] = $cat_string;

            }
        }
        catch (Exception $exception) {
            //echo $exception->getMessage();
        }


        // moving pointer
        $xml->next($primEL);
        // clearing current element
        unset($element);
    } // end while

    $xml->close();
    //echo 'Cats - '.count($cats_array);
    //echo '<pre>';
    //print_r($cats_array);
    //echo '</pre>';

    return $cats_array;
}
