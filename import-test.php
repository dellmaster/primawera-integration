<?php
function cliPrimaweraGetProductsFromFullFileForCategory() {
    $upload_dir = wp_upload_dir();
    $primawera_filename = $upload_dir['basedir'].'/primawera/primawera_full.xml';
    $category_show = 'Pielęgnacja > Włosy > Pielęgnacja włosów > Odżywki';
    $xmlFile = $primawera_filename;
    //$prices_xml_file = 'https://orders.pvex.pl/pricelist.php?wh=all&token=404D9EC0AA35F0255B392D2A8F20091B8518&format=xml';
    $upload_dir = wp_upload_dir();
    $prices_xml_file =  $upload_dir['basedir'].'/primawera/primawera_prices.xml';

    $products_array = [];

    $prices_array = PrimaweraAllProductGetPrices($prices_xml_file);
    WP_CLI::log('prices - '.count($prices_array));
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
            $xml_sub_str = $xml->readOuterXML();
            if (!empty($xml_sub_str))
            {
                $element = new SimpleXMLElement($xml->readOuterXML());
                $current_product_show = false;
                $categories = $element->xpath('/product/categories/category');
                $prod_categories = [];
                foreach ($categories as $category) {
                    $cat_string = (string)$category;

                    $prod_categories[] = $cat_string;

                    if ($category_show == $cat_string)
                    {
                        $current_product_show = true;
                        WP_CLI::log('cat - '.$cat_string);
                    }
                }

                $eans = $element->xpath('/product/eans');
                $ean = (string)$eans[0]->ean;

                WP_CLI::log('ean - '.print_r($ean, true));

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
                    WP_CLI::log('product - '.print_r($product, true));
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
                // clearing current element
                unset($element);
            }






        // moving pointer


        }
        catch (Exception $exception) {
            WP_CLI::log('error - '.$exception->getMessage());
            WP_CLI::log(' - '.print_r($xml, true));

            //unset($element);
            //$xml->next($primEL);
            //continue;
            //echo $exception->getMessage();
            //unset($element);
            //continue;
        }

        $xml->next($primEL);


    } // end while

    $xml->close();
    //echo 'Cats - '.count($cats_array);
    //echo '<pre>';
    //print_r($cats_array);
    //echo '</pre>';

    //return $products_array;
}

function rbit_cli_register_commands_primawera_full_file_parse_test() {
    WP_CLI::add_command( 'parse_full_file_test', 'cliPrimaweraGetProductsFromFullFileForCategory' );
}

add_action( 'cli_init', 'rbit_cli_register_commands_primawera_full_file_parse_test' );



// TEST import of one product

function cliPrimaweraOneProductTestImport() {

    $upload_dir = wp_upload_dir();
    $primawera_full_filename = $upload_dir['basedir'].'/primawera/primawera_full.xml';

    //$product_eans = [3031445009836, 7350084610897, 5900717077591];
    $product_eans = [3423222010294, ];
    $category_id = 684;


    $product_ids = PrimaweraEANsImportToCategory($primawera_full_filename, $category_id, $product_eans);
    WP_CLI::log('woo product ids - '.print_r($product_ids, true));
}

function rbit_cli_register_commands_primawera_test_import_one_product() {
    WP_CLI::add_command( 'primawera_one_product_test_import', 'cliPrimaweraOneProductTestImport' );
}

add_action( 'cli_init', 'rbit_cli_register_commands_primawera_test_import_one_product' );
