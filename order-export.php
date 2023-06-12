<?php


function rbit_export_orders_data($order_id) {

    $primawera_api_login = 'test_user';
    $primawera_api_pass = 'password';
    $promawera_api_url = 'https://webhook.site/9e9d9eee-4914-4919-90b4-19cb0eda5d4a';
    // https://orders.pvex.pl/webapi
    //products is from Primavera product



    $order = wc_get_order( $order_id );
    //$types = array( 'line_item', 'fee', 'shipping', 'coupon' );
    $types = array( 'line_item' );
    $products_obj_array = [];
    foreach( $order->get_items( $types ) as $item_id => $item ) {
        if( $item->is_type( 'line_item' ) ) {
            $item_product_id = $item->get_product_id();
            $is_primavera_product = get_field('is_primavera_product', $item_product_id);
            if($is_primavera_product) {

                $is_primavera_ean = get_field('primawera_ean', $item_product_id);

                $product_quantity = $item->get_quantity();
                $product_item = new stdClass();
                $product_item->ean = $is_primavera_ean;
                $product_item->quantity = $product_quantity;
                $product_item->comment = "";

                $products_obj_array[] = $product_item;
            }
        }
    }

    if(count($products_obj_array) > 0) {
        $billing_email  = $order->get_billing_email();
        $billing_phone  = $order->get_billing_phone();
        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name  = $order->get_billing_last_name();

        $shipping_address_1  = $order->get_shipping_address_1();
        $shipping_address_2  = $order->get_shipping_address_2();
        $shipping_city       = $order->get_shipping_city();
        $shipping_postcode   = $order->get_shipping_postcode();
        $shipping_country    = $order->get_shipping_country();

        $order_contact = new stdClass();
        $order_contact->email = $billing_email;
        $order_contact->name = $billing_first_name;
        $order_contact->surname = $billing_last_name;
        $order_contact->phone = $billing_phone;

        $order_delivery = new stdClass();
        $order_delivery->street = $shipping_address_1.(!empty($shipping_address_2)?' '.$shipping_address_2 : '');
        $order_delivery->apartmentNo = '';
        $order_delivery->buildingNo = '';
        $order_delivery->zipcode = $shipping_postcode;
        $order_delivery->city = $shipping_city;
        $order_delivery->country = $shipping_country;


        $order_data = new stdClass();
        $order_data->contact = $order_contact;
        $order_data->delivery = $order_delivery;
        $order_data->warehouse = 'all';
        $order_data->comment = 'test order comment';
        $order_data->orderNumber = $order_id;
        $order_data->items = $products_obj_array;



        $order_json = json_encode($order_data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $promawera_api_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Authorization: Basic '. base64_encode("$primawera_api_login:$primawera_api_pass")
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $order_json);
        $curl_result = curl_exec($curl);
        curl_close($curl);

        // Parse curl response
        $response_data = json_decode($curl_result); // get object

        if ('true' == $response_data->success) {

            $order->update_meta_data( 'primawera_order_data', $curl_result );
            $order->add_order_note( __( "Order sent to Primawera", "rbit-orders-primawera" ) );
            $primawera_out_of_stock = 0;
            if (isset($response_data->omittedItems)) {
                $out_of_stock_message = 'Out of stock items. ';
                foreach ($response_data->omittedItems as $omittedItem) {
                    $out_of_stock_message .= 'EAN: '. $omittedItem->ean . ', quantity: '. $omittedItem->quantity . '; ';
                }
                $order->add_order_note( $out_of_stock_message );
                $primawera_out_of_stock = 1;

            }
            // set falg - order have primavera
            $order->update_meta_data( 'primawera_out_of_stock', $primawera_out_of_stock );
        }
        elseif ('false' == $response_data->success) {
            $order->add_order_note( __( "Primawera API error.", "rbit-orders-primawera" ).' '.$response_data->msg );
        }




        // set flag that order have Primawera product
        $order->update_meta_data( 'has_primawera_product', 1 );

        $order->save();
    }

}

add_action( 'woocommerce_payment_complete', 'rbit_export_orders_data' );


function primawera_order_export_test ()
{
    $order_id = 67976;

    rbit_export_orders_data($order_id);

}

function rbit_cli_register_commands_primawera_test_export_order() {
    WP_CLI::add_command( 'primawera_export_order', 'primawera_order_export_test' );
}

add_action( 'cli_init', 'rbit_cli_register_commands_primawera_test_export_order' );
