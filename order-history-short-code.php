
add_shortcode( 'order-products', 'order_products' );

//order_product is will show order history
function order_products($atts) {
    $atts = shortcode_atts(
        array(
            'page' => 1,
            'ajax' => 0,
        ), $atts, 'bartag' );

    $current_user = wp_get_current_user();
    $current_language_code = apply_filters( 'wpml_current_language', null );
    if($current_language_code == 'en') { // English script
        $loadMoreText = 'Load More';
        $headingOrder = '<div style="margin-top: 20px;margin-bottom: 31px !IMPORTANT;text-align:center" class="vc_custom_heading mb-2 heading heading-border heading-middle-border heading-middle-border-center "><h2 style="font-weight:bold;font-size: 30px; line-height: 1; text-align: center; animation-delay: 400ms;" data-appear-animation="fadeInUp" data-appear-animation-delay="400" class="heading-tag appear-animation fadeInUp appear-animation-visible">Order History</h2></div>';

    }
    elseif($current_language_code == 'tr') { // turkish script
        $loadMoreText = 'Daha fazla yükle';
        $headingOrder = '<div style="margin-top: 20px;margin-bottom: 31px !IMPORTANT;text-align:center" class="vc_custom_heading mb-2 heading heading-border heading-middle-border heading-middle-border-center "><h2 style="font-weight:bold;font-size: 30px; line-height: 1; text-align: center; animation-delay: 400ms;" data-appear-animation="fadeInUp" data-appear-animation-delay="400" class="heading-tag appear-animation fadeInUp appear-animation-visible">Sipariş Geçmişi</h2></div>';
    }
    else { // japanese script
        $loadMoreText = 'もっと読み込む';
        $headingOrder = '<div style="margin-top: 20px;margin-bottom: 31px !IMPORTANT;text-align:center" class="vc_custom_heading mb-2 heading heading-border heading-middle-border heading-middle-border-center "><h2 style="font-weight:bold;font-size: 30px; line-height: 1; text-align: center; animation-delay: 400ms;" data-appear-animation="fadeInUp" data-appear-animation-delay="400" class="heading-tag appear-animation fadeInUp appear-animation-visible">注文履歴</h2></div>';

    }

    if ( 0 == $current_user->ID )
          return 'Please login...';

    // GET USER ORDERS (COMPLETED + PROCESSING)
    $customer_orders = get_posts( array(
        'numberposts' => 40,
        'meta_key'    => '_customer_user',
        'meta_value'  => $current_user->ID,
        'post_type'   => wc_get_order_types(),
        'post_status' => array_keys( wc_get_is_paid_statuses() ),
    ) );


    if ( ! $customer_orders )
        return 'Not Found';
    $product_ids = array();
    $product_status = array();
    foreach ( $customer_orders as $customer_order ) {
        $order = wc_get_order( $customer_order->ID );
        $items = $order->get_items();
        foreach ( $items as $item ) {
            $product_id = $item->get_product_id();
            //if(WC_Product::exist)
            if ( FALSE === get_post_status( $product_id ) )
                continue;
            else  if ( "publish" != get_post_status( $product_id ) )
                continue;
            $product_ids[] = $product_id;
            $product_status[] = get_post_status( $product_id );
        }
    }

    $product_ids = array_unique( $product_ids );
    $product_ids = array_chunk($product_ids,6);
    $total_page = count($product_ids);
    $currentPage = ($atts['page']);
    $nextPage = ($atts['page']+1);
    $product_ids = $product_ids[($atts['page']-1)];

    $product_ids_str = implode( ",", $product_ids );

    if(($total_page != $currentPage) && ($total_page > $currentPage) )
         $loadMoreBtn = '<div align="center" class="loadmore"><a data-page="'.$nextPage.'" class="loadmore-history" href="javascript:void(0)">'.$loadMoreText.'</a></div>';
    else
        $loadMoreBtn = "";


    if($atts['ajax'] == 0)
        return $headingOrder.'<div class="order-history">'.do_shortcode("[products columns='6' limit='6' ids='$product_ids_str']").'</div>'.$loadMoreBtn;
    else
       return ''.do_shortcode("[products columns='6' limit='6' ids='$product_ids_str']").''.$loadMoreBtn;

}


add_action( 'wp_ajax_nopriv_get_order_history_ajax', 'get_order_history_ajax' );
add_action( 'wp_ajax_get_order_history_ajax', 'get_order_history_ajax' );

function get_order_history_ajax() {
    $page = (isset($_POST["page"]) )? $_POST["page"] : 1;
    echo do_shortcode('[order-products page='.$page.' ajax=1]');
    wp_die();
}
