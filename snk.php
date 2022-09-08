<?php
//Including in function.php by your theme.
//Work with google tag manager
//Tracking will be once for one order.

//Hook
add_action( 'woocommerce_thankyou', 'checkout_success_google_track' );

function checkout_success_google_track( $order_id ) {
//get order and preapere it data
    $order = wc_get_order( $order_id );
    $items = $order->get_items();
    $product_array = array();
    foreach ( $items as $item_id => $item ) {
        $product = $item->get_product();
        $terms = get_the_terms( $product->get_id(), 'product_cat' );
        $categories_txt = '';
        foreach ($terms as $term){
            $categories_txt.=$term->name.' / ';
        }

        $categories_txt = substr($categories_txt,0,-2);

        $new_std = new stdClass();
        $new_std->sku = $product->get_sku();
        $new_std->name = $product->get_name();
        $new_std->category = $categories_txt;
        $new_std->price = $product->get_price();
        $new_std->quantity = $item->get_quantity();
        $product_array[] = $new_std;
    }

//create order_id for checking
    echo '<script>var checkout_order_id = "'.$order_id.'";</script>';


//google script
    echo "<script>
        function checkout_success_google_track() {
            window.dataLayer = window.dataLayer ||	[];
            dataLayer.push({
                'transactionId': '".$order_id."',
                'transactionTotal': ".$order->get_total().", 
                'transactionShipping': ".$order->get_shipping_total().",
                'transactionProducts': ".json_encode($product_array)."
            });
            dataLayer.push({
            'event': 'purchase'
            });
        }
        
        //init. This script will work only once.
        jQuery(document).ready(function (){
            check_storage = window.localStorage.getItem('custom_last_order_id');
            if(typeof checkout_order_id === \"undefined\" || !checkout_order_id){
                return false;
            }
            
            if( !check_storage || check_storage != checkout_order_id){
                window.localStorage.setItem('custom_last_order_id', checkout_order_id);
                checkout_success_google_track();
            }else{
                
            }
            
        });
    </script>
    ";

}
