<?php

/**
 * @param string $xmlFile all products xml file
 * @param string $category_show primawera category
 * @return array
 */
function PrimaweraGetProductsFromFullFileForCategory($xmlFile, $category_show) {
    //$prices_xml_file = 'https://orders.pvex.pl/pricelist.php?wh=all&token=404D9EC0AA35F0255B392D2A8F20091B8518&format=xml';
    $upload_dir = wp_upload_dir();
    $prices_xml_file =  $upload_dir['basedir'].'/primawera/primawera_prices.xml';

    $products_array = [];

    $prices_array = PrimaweraAllProductGetPrices($prices_xml_file);
    //print_r($prices_array);
    $primEL = 'product';

    $xml = new XMLReader();
    $xml->open($xmlFile);

    // finding first primary element to work with
    while($xml->read() && $xml->name != $primEL){;}

    // looping through elements
    while($xml->name == $primEL) {
        try {
            // loading element data into simpleXML object
            $element = new SimpleXMLElement($xml->readOuterXML());
        }
        catch (Exception $exception) {
            //echo $exception->getMessage();
            // moving pointer
            $xml->next($primEL);
            // clearing current element
            unset($element);
            continue;
        }
            $current_product_show = false;
            $categories = $element->xpath('/product/categories/category');
            //$prod_categories = [];
            foreach ($categories as $category) {
                $cat_string = (string)$category;

                //$prod_categories[] = $cat_string;
                $fixed_cat_string = str_replace('&gt;', '>', $cat_string);
                if ($category_show == $fixed_cat_string) $current_product_show = true;
            }

            if ($current_product_show) {
                $product = [];


                $brand =  $element->xpath('/product/brand');
                $name = $element->xpath('/product/name');
                //$price = 100;


                //$available = 1;
                $eans = $element->xpath('/product/eans');
                $ean = (string)$eans[0]->ean;
                // TODO duplicate product if we have few EANs
                //'price' => $price,
                // 'available' => $available,
                $price = $prices_array[$ean]['price'];
                //$price = 10;
                $available = $prices_array[$ean]['available'];
                //$available = '';

                //$product['brand'] = (string)$brand;
                $product['brand'] = (string)$brand[0];
                $product['name'] = (string)$name[0];
                $product['price'] = $price;
                $product['available'] = $available;
                //$product['ean'] = print_r($ean, true);
                $product['ean'] = $ean;
                //$product['ean'] = print_r($prod_categories, true);

                $products_array[] = $product;
                unset($product);
                unset($price);
                unset($available);
                unset($ean);
                unset($eans);
                unset($name);
                unset($brand);
                //$products_array[$ean] = $product;
            }

            // DO STUFF

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

    return $products_array;
}

/**
 * @param $xmlFile string path to xml file
 * @param $woo_category int category id for import
 * @param $product_eans array primawera products eans array
 * @return array
 */
function PrimaweraProductsImportByEANsToCategory($xmlFile, $woo_categor_id, $product_eans) {
    $products_array = [];

    $primEL = 'product';

    $xml = new XMLReader();
    $xml->open($xmlFile);

    // finding first primary element to work with
    while($xml->read() && $xml->name != $primEL){;}

    // looping through elements
    while($xml->name == $primEL) {
        // loading element data into simpleXML object
        $element = new SimpleXMLElement($xml->readOuterXML());
        $current_product_import = false;

        $eans = $element->xpath('/product/eans');
        $ean = (string)$eans[0]->ean;


        if (in_array($ean, $product_eans)) {
            $product = [];


            $brand =  $element->xpath('/product/brand');
            $name = $element->xpath('/product/name');
            // TODO get prices from other file
            $price = 100;
            // TODO get $available from other file
            $available = 1;
            $eans = $element->xpath('/product/eans');
            $ean = (string)$eans[0]->ean;


            //$product['brand'] = (string)$brand;
            $product['brand'] = (string)$brand[0];
            $product['name'] = (string)$name[0];
            $product['price'] = $price;
            $product['available'] = $available;
            //$product['ean'] = print_r($ean, true);
            $product['ean'] = $ean;
            //$product['ean'] = print_r($prod_categories, true);

            $products_array[] = $product;
            //$products_array[$ean] = $product;
        }

        // DO STUFF

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

    return $products_array;
}

/**
 * @param string $element_ean - product EAN
 * @param SimpleXMLElement $XMLElementFull - xml element from big file
 * @param array|false $ElementPrice - xml element from quantity file or false if we don`t have it in Price file
 * @param int $category_id WooCommerce category ID
 * @return array|bool return created products ids array or false on error
 */
function PrimaweraProductImportToCategory(string $element_ean, SimpleXMLElement $XMLElementFull, $ElementPrice, $category_id)
{
    $upload_dir = wp_upload_dir();

    $brand_el = $XMLElementFull->xpath('/product/brand');
    $brand =  (string)$brand_el[0];
    $name_el = $XMLElementFull->xpath('/product/name');
    $name = (string)$name_el[0];
    //$eans = $XMLElementFull->xpath('/product/eans');
    $description_el = $XMLElementFull->xpath('/product/dscr');
    $description = (string)$description_el[0];
    $image_url_el = $XMLElementFull->xpath('/product/image');
    $image_url = $image_url_el[0];

    if ($ElementPrice) {
        $price = $ElementPrice['price'];
        $qty = $ElementPrice['qty'];
    }
    else
    {
        $price = 0;
        $qty = 0;
    }


    //$status = ($qty > 0) ? 'publish' : 'draft';
    $status = 'publish';
    $stock_status = ($qty > 0) ? 'instock' : 'outofstock';

    $product_ids = false;

    $attributes_array = [];

    // get products attributes
    foreach ($XMLElementFull->xpath('/product/features/feature') as $feature) {
        $feature_name = (string)$feature->attributes()->name;
        $feature_value_el = $feature->value[0];
        $feature_value = (string)$feature_value_el;
        $attributes_array[$feature_name] = $feature_value;
    }

    //WP_CLI::log('attrs array - '.print_r($attributes_array, true));


    foreach ($XMLElementFull->xpath('/product/eans') as $ean) {
        if($ean->ean == $element_ean) {

        $product = new WC_Product_Simple();
        $product->set_name($name);

        $product->set_regular_price($price);
        $product->set_description($description);
        $product->set_category_ids([$category_id,]);
        $product->set_status($status);
        $product->set_manage_stock(true);
        $product->set_stock_quantity($qty);
        $product->set_stock_status($stock_status);

        $product_id = $product->save();

        // TODO add attributes to to woo product

        if ($product_id) {

            update_field('primawera_ean', $element_ean, $product_id);
            update_field('is_primavera_product', 1, $product_id);
            // add attribute to product
            PrimaweraCreateOrUpdateAttributesForProduct($product_id, $attributes_array);
//            foreach ($attributes_array as $attr_name => $attr_value) {
//                //PrimaweraCreateOrUpdateAttributeForProduct($product_id, $attr_name, $attr_value);
//
//            }


            $product_ids[] = $product_id;

            // START Import product image
            $upload_subfolder = 'products/' . $product_id;

            $image = pathinfo($image_url);
            $image_name = $image['basename'];
            $image_data = file_get_contents($image_url);
            $new_image_name = str_replace(' ', '_', $image_name);
            $unique_file_name = wp_unique_filename($upload_dir['path'], $new_image_name);
            $filename = basename($unique_file_name);

            if ($image['basename'] != '') {
                // Check folder permission and define file location
                if (wp_mkdir_p($upload_dir['path'] . '/' . $upload_subfolder)) {
                    $file = $upload_dir['path'] . '/' . $upload_subfolder . '/' . $filename;
                } else {
                    $file = $upload_dir['basedir'] . '/' . $upload_subfolder . '/' . $filename;
                }

                // Create the image  file on the server
                file_put_contents($file, $image_data);
                // Check image file type
                $wp_filetype = wp_check_filetype($filename, null);
                // Set attachment data
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'guid' => $upload_dir['url'] . '/' . $upload_subfolder . '/' . $filename,
                );

                // Create the attachment
                $attach_id = wp_insert_attachment($attachment, $file);
                // Include image.php
                require_once ABSPATH . 'wp-admin/includes/image.php';
                // Define attachment metadata
                $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                // Assign metadata to attachment
                wp_update_attachment_metadata($attach_id, $attach_data);

                // set image as main
                set_post_thumbnail($product_id, absint($attach_id));

                //add image to product gallery
                //update_post_meta($product_id, '_product_image_gallery', $attach_id);

            }
            // END Import product image
        }
    }

    }


    return $product_ids;

}

/**
 * @param $xmlFile string xml file path
 * @param $eansArray
 * @return array
 */
function PrimaweraProductGetPricesForEANs($xmlFile, $eansArray)
{
    $products_prices_array = [];

    $primEL = 'produkt';

    $xml = new XMLReader();
    $xml->open($xmlFile);

    // finding first primary element to work with
    while($xml->read() && $xml->name != $primEL){;}

    // looping through elements
    while($xml->name == $primEL) {
        // loading element data into simpleXML object
        $element = new SimpleXMLElement($xml->readOuterXML());
        $current_product_import = false;

        $ean_el = $element->xpath('/produkt/model');
        $ean = (string)$ean_el[0];
        //$eans = $element->xpath('/product/eans');
        //$ean = (string)$eans[0]->ean;
        //WP_CLI::log('ean - '.print_r($ean, true));

        if (in_array($ean, $eansArray)) {

            $price_el = $element->xpath('/produkt/cena');
            //$price = floatval((string)$element->xpath('/produkt/cena'));
            $price = floatval((string)$price_el[0]);
            $available_el = $element->xpath('/produkt/stan');
            $available = (string)$available_el[0];

            //$price = floatval((string)$element->xpath('/produkt/cena'));
            //$available = (string)$element->xpath('/produkt/stan');
            $qty_el = $element->xpath('/produkt/ilosc');
            $qty = intval((string)$qty_el[0]);

            $products_prices_array[$ean] = [
                'price' => $price,
                'available' => $available,
                'qty' => $qty,
            ];

        }

        // DO STUFF

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

    return $products_prices_array;
}

/**
 * @param $xmlFile string product prices xml file path
 * @return array
 */
function PrimaweraAllProductGetPrices($xmlFile)
{
    $products_prices_array = [];

    $primEL = 'produkt';

    $xml = new XMLReader();
    $xml->open($xmlFile);

    // finding first primary element to work with
    while($xml->read() && $xml->name != $primEL){;}

    // looping through elements
    while($xml->name == $primEL) {
        // loading element data into simpleXML object
        $element = new SimpleXMLElement($xml->readOuterXML());
        $current_product_import = false;
        $ean_el = $element->xpath('/produkt/model');
        //$ean = print_r($ean_el[0], true);
        $ean = (string)$ean_el[0];
        //$ean = (string)$eans[0]->ean;

        $price_el = $element->xpath('/produkt/cena');
        //$price = floatval((string)$element->xpath('/produkt/cena'));
        $price = floatval((string)$price_el[0]);
        $available_el = $element->xpath('/produkt/stan');
        $available = (string)$available_el[0];

        $qty_el = $element->xpath('/produkt/ilosc');
        $qty = intval((string)$qty_el[0]);

        $products_prices_array[$ean] = [
            'price' => $price,
            'available' => $available,
            'qty' => $qty,
        ];



        // DO STUFF

        // moving pointer
        $xml->next($primEL);
        // clearing current element
        unset($element);
    } // end while

    $xml->close();


    return $products_prices_array;
}

/**
 * @param $ean int|string  product ean for search
 * @return int[]  of product IDs
 */
function PrimaweraGetProductIDsByEAN($ean) {
    $args = array(
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'ids',
        'limit' => -1,
        'meta_key'      => 'primawera_ean',
        'meta_value'    => $ean,
    );
    $product_ids = wc_get_products( $args );

    return $product_ids;
}


/**
* @param $eans int[]|string[]  product eans for search
 * @return int[]  of product IDs
*/
function PrimaweraGetProductIDsByEANs($eans) {
    $args = array(
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'ids',
        'limit' => -1,
        'meta_key'      => 'primawera_ean',
        'meta_value'    => $eans,
        'meta_compare '    => 'IN',
    );
    $product_ids = wc_get_products( $args );

    return $product_ids;
}


/**
 * Create Woo one product attribute (if not exist) and assing it to product
 * @param $product_id int WooCommerce product ID
 * @param $attribute_name string WooCommerce attribute name
 * @param $attribute_value string WooCommerce attribute value
 * @return void
 */
function PrimaweraCreateOrUpdateAttributeForProduct($product_id, $attribute_name, $attribute_value)
{
    $attr_db_id = wc_attribute_taxonomy_id_by_name($attribute_name);
    if ( empty($attr_db_id) || 0 == $attr_db_id) {
        $args = array(
            'id' => '',
            'slug'    => wc_sanitize_taxonomy_name($attribute_name),
            'name'   => $attribute_name,
        );

        $new_attr_id = wc_create_attribute( $args );

        //$new_attr_obj = wc_get_attribute($new_attr_id);
        //$taxonomy = wc_attribute_taxonomy_name_by_id( $new_attr_id );
        //$taxonomy_obj = wc_get_attribute( $new_attr_id );

    }
    else
    {
        $new_attr_id = $attr_db_id;
    }

    // add attribute values
    if($new_attr_id) {
        $taxonomy_obj = wc_get_attribute( $new_attr_id );
        WP_CLI::log('attr taxonomy obj - '.print_r($taxonomy_obj, true));
        $taxonomy_slug = $taxonomy_obj->slug;
        $check_term = term_exists( $attribute_value, $taxonomy_slug );
        //get_term_by( 'id', $attribute_value, $taxonomy_slug, $output = OBJECT, $filter = 'raw' )

        if (!$check_term)
        {

            $term = wp_insert_term(
                $attribute_value, // The term
                $taxonomy_slug, // The taxonomy
//            array(
//                'slug' => wc_sanitize_taxonomy_name($attribute_value), // The slug for the term (optional)
//            )
            );

            WP_CLI::log('term created - '.print_r($term, true));

            if( !is_wp_error( $term ) ){

            }
        }
        else
        {
            $term = $check_term;
            WP_CLI::log('term exist - '.print_r($check_term, true));
        }

        WP_CLI::log('term array - '.print_r($term, true));

        if ($term) {
            $term_obj = get_term_by( 'id', $term['term_id'], $taxonomy_slug);
            //$attrs_data = [];
            $attrs_data = get_post_meta( $product_id, '_product_attributes', true);
            if(!is_array($attrs_data)) $attrs_data =[];

            WP_CLI::log('start attr data - '.print_r($attrs_data, true));
            $term_taxonomy_ids = wp_set_object_terms($product_id, [$term_obj->slug,], $taxonomy_slug, true);

            $attrs_data[$taxonomy_slug] = array(
                'name' => $taxonomy_slug,
                'value' => [$term_obj->slug,],
                'is_visible' => '1',
                'is_taxonomy' => '1',
            );
            WP_CLI::log('for update attr data - '.print_r($attrs_data, true));
            update_post_meta($product_id, '_product_attributes', $attrs_data);

            WP_CLI::log('attr update - '.print_r([$term_obj->slug,], true));
            WP_CLI::log('$taxonomy_slug - '.print_r($taxonomy_slug, true));
            WP_CLI::log('$product_id - '.print_r($product_id, true));
        }
    }

}

/**
 * @param int $product_id
 * @param array $attributes_array
 * @return void
 */
function PrimaweraCreateOrUpdateAttributesForProduct($product_id, $attributes_array)
{
    $attrs_data = [];
    $attrs_data = get_post_meta( $product_id, '_product_attributes', true);
    if(!is_array($attrs_data)) $attrs_data =[];

    foreach ($attributes_array as $attribute_name => $attr_value_sring) {

        $attr_db_id = wc_attribute_taxonomy_id_by_name($attribute_name);
        if ( empty($attr_db_id) || 0 == $attr_db_id) {
            $args = array(
                'id' => '',
                'slug'    => wc_sanitize_taxonomy_name($attribute_name),
                'name'   => $attribute_name,
            );

            $new_attr_id = wc_create_attribute( $args );

            //$new_attr_obj = wc_get_attribute($new_attr_id);
            //$taxonomy = wc_attribute_taxonomy_name_by_id( $new_attr_id );
            //$taxonomy_obj = wc_get_attribute( $new_attr_id );

        }
        else
        {
            $new_attr_id = $attr_db_id;
        }

        // add attribute values
        if($new_attr_id) {
            $taxonomy_obj = wc_get_attribute( $new_attr_id );
            //WP_CLI::log('attr taxonomy obj - '.print_r($taxonomy_obj, true));
            $taxonomy_slug = $taxonomy_obj->slug;

            $attr_values_array = explode(',', $attr_value_sring);
            $value_term_slugs = [];

            foreach ($attr_values_array as $attribute_value) {
                $attribute_value = trim($attribute_value);
                $check_term = term_exists( $attribute_value, $taxonomy_slug );
                //get_term_by( 'id', $attribute_value, $taxonomy_slug, $output = OBJECT, $filter = 'raw' )

                if (!$check_term)
                {

                    $term = wp_insert_term(
                        $attribute_value, // The term
                        $taxonomy_slug, // The taxonomy
                        /*array(
                            'slug' => wc_sanitize_taxonomy_name($attribute_value), // The slug for the term (optional)
                        )*/
                    );

                    WP_CLI::log('term created - '.print_r($term, true));
                    WP_CLI::log('$attribute_value - '.print_r($attribute_value, true));
                    WP_CLI::log('term created, taxonomy - '.print_r($taxonomy_slug, true));

                    if( !is_wp_error( $term ) ){

                    }
                }
                else
                {
                    $term = $check_term;
                    WP_CLI::log('term exist - '.print_r($check_term, true));
                }

                //WP_CLI::log('term array - '.print_r($term, true));

                if ($term) {
                    WP_CLI::log('$taxonomy_slug - '.print_r($taxonomy_slug, true));
                    WP_CLI::log('term array - '.print_r($term, true));
                    $term_obj = get_term_by( 'id', $term['term_id'], $taxonomy_slug);
                    //$term_obj = get_term_by( 'name', $attribute_value, $taxonomy_slug);
                    //$attrs_data = [];

                    //WP_CLI::log('start attr data - '.print_r($attrs_data, true));
                    $term_taxonomy_ids = wp_set_object_terms($product_id, [$term_obj->slug,], $taxonomy_slug, true);

                    $value_term_slugs[] = $term_obj->slug;

                }
            }

            if (count($value_term_slugs)) {

                $attrs_data[$taxonomy_slug] = array(
                    'name' => $taxonomy_slug,
                    'value' => $value_term_slugs,
                    'is_visible' => '1',
                    'is_taxonomy' => '1',
                );
                //WP_CLI::log('for update attr data - '.print_r($attrs_data, true));
            }

        }

    }

    if (count($attrs_data)) {

        update_post_meta($product_id, '_product_attributes', $attrs_data);

        WP_CLI::log('attr update - '.print_r([$term_obj->slug,], true));
        WP_CLI::log('$taxonomy_slug - '.print_r($taxonomy_slug, true));
        WP_CLI::log('$product_id - '.print_r($product_id, true));
    }
    else {

    }




}


/**
 * @param $xmlFile string path to fill xml data
 * @param $woo_categor_id int WooCommerce category ID for import
 * @param $eans string[] array of EANs to import
 * @return int[] array of IDs Woocommerce products
 */
function PrimaweraEANsImportToCategory($xmlFile, $woo_categor_id, $eans) {
    $upload_dir = wp_upload_dir();
    $prices_xml_file =  $upload_dir['basedir'].'/primawera/primawera_prices.xml';

    $products_array = [];

    WP_CLI::log('eans - '.print_r($eans, true));
    $prices_array = PrimaweraProductGetPricesForEANs($prices_xml_file, $eans);
    WP_CLI::log('prices - '.print_r($prices_array, true));
    //print_r($prices_array);
    $primEL = 'product';

    $xml = new XMLReader();
    $xml->open($xmlFile);

    // finding first primary element to work with
    while($xml->read() && $xml->name != $primEL){;}

    // looping through elements
    while($xml->name == $primEL) {
        try {
            // loading element data into simpleXML object
            $element = new SimpleXMLElement($xml->readOuterXML());
        }
        catch (Exception $exception) {
            //echo $exception->getMessage();
            // moving pointer
            $xml->next($primEL);
            // clearing current element
            unset($element);
            continue;
        }

        $element_eans = $element->xpath('/product/eans');

        foreach ($element_eans as $ean) {
            $element_ean = (string)$ean->ean;
            if (in_array($element_ean, $eans)) {
                if (count(PrimaweraGetProductIDsByEAN($element_ean) ) == 0) {
                    WP_CLI::log($element_ean.' price data - '.print_r($prices_array[$element_ean], true));
                    $woo_products_ids = PrimaweraProductImportToCategory( $element_ean, $element, $prices_array[$element_ean], $woo_categor_id);

                    $products_array = array_merge($products_array, $woo_products_ids);

                }

                //$products_array[$element_ean] = $element;
            }
        }

        unset($element_ean);
        unset($element_eans);

        // DO STUFF

        // moving pointer
        $xml->next($primEL);
        // clearing current element
        unset($element);

    } // end while

    $xml->close();

    return $products_array;
}


