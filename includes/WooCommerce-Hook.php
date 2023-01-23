<?php
if (!defined('ABSPATH')) exit;

// Register checkout fields
add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );
add_filter( 'woocommerce_checkout_fields' , 'meta_basket_checkout_fields' );
function meta_basket_checkout_fields( $fields ) {
	unset($fields['order']['order_comments']);

	return $fields;
}
add_action( 'woocommerce_after_order_notes', 'nft_address_checkout_field' );

function nft_address_checkout_field( $checkout ) {
	$user = wp_get_current_user();
	
    echo '<div id="nft_address_checkout_field"><h4>' . __('NFT Address') . '</h4>';
	echo '<span class="description">Please provide your ETH/ENJ/JENJ wallet address where we will send your NFTs.</span>';
	
    woocommerce_form_field( 'nft_address_field', 
		array(
			'type'          => 'text',
			'class'         => array('nft-address-field form-row-wide'),
			'placeholder'   => __('0x....'),
        ), 
		get_the_author_meta( 'ethereumAddress', $user->ID ));
		

    echo '</div>';
}

add_action('woocommerce_checkout_process', 'nft_address_checkout_field_process');
function nft_address_checkout_field_process() {
    if ( ! $_POST['nft_address_field'] )
        wc_add_notice( __( 'Please provide your ETH/ENJ/JENJ wallet address where we will send your NFTs.' ), 'error' );
}

add_action( 'woocommerce_checkout_update_order_meta', 'nft_address_checkout_field_update_order_meta' );
function nft_address_checkout_field_update_order_meta( $order_id ) {
	
	$user = wp_get_current_user();
	
    if ( ! empty( $_POST['nft_address_field'] ) ) {
		update_user_meta( $user->ID, 'ethereumAddress', sanitize_text_field( $_POST['nft_address_field'] ) );
        update_post_meta( $order_id, 'NFT Address', sanitize_text_field( $_POST['nft_address_field'] ) );
    }
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'nft_address_checkout_field_display_admin_order_meta', 10, 1 );
function nft_address_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('NFT Address').':</strong> ' . get_post_meta( $order->id, 'NFT Address', true ) . '</p>';
}



// Register new status
function register_sending_item_order_status() {
    register_post_status( 'wc-sending-items', array(
        'label'                     => 'Sending Items',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Sending Items (%s)', 'Sending Items (%s)' )
    ) );
	
	register_post_status( 'wc-send-error', array(
        'label'                     => 'Error Sending',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Error Sending (%s)', 'Error Sending (%s)' )
    ) );
}
add_action( 'init', 'register_sending_item_order_status' );

// Add to list of WC Order statuses
function add_sending_item_to_order_statuses( $order_statuses ) {
 
    $new_order_statuses = array();
 
    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {
 
        $new_order_statuses[ $key ] = $status;
 
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-sending-items'] = 'Sending Items';
			$new_order_statuses['wc-send-error'] = 'Error Sending';
        }
    }
 
    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_sending_item_to_order_statuses' );

// Display Fields
add_action('woocommerce_product_options_general_product_data', 'woocommerce_enjin_gateway_fields');
function woocommerce_enjin_gateway_fields() {
	$options = get_option( 'enjin_settings' );
	require_once (__DIR__ . '/EnjinAPI.php');
	
	$enjin = new EnjinAPIMeta($options['enjin_app_id'], $options['enjin_app_secret'], $options['enjin_app_type'], $options['enjin_identity_id']);
	$enjin->authorize();
	
	$customAttr = [];
	$placeholder = "Token ID";
	$message = "";
	
	if (!$enjin->isAuthorized()) {
		$customAttr['readonly'] = 'readonly';
		$message = "Enjin not connected. <a href='" . admin_url( 'admin.php?page=meta-basket' ) . "'>Click here to connect</a>.";
	}
	
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';
    // Enjin Token ID
    woocommerce_wp_text_input(
        array(
            'id' => '_token_enjin_id_text_field',
            'placeholder' => $placeholder,
            'label' => __('Enjin Token ID', 'woocommerce'),
			'custom_attributes' => $customAttr
        )
    );
	echo '</div>';
	
	echo '<div class="product_custom_field">';
    // Request Type
	woocommerce_wp_select( 
		array( 
			'id'      => '_token_enjin_request_text_field', 
			'label'   => __( 'Request Type', 'woocommerce' ), 
			'options' => array(
				'mint'   => __( 'Mint Token', 'woocommerce' ),
				'send'   => __( 'Send From Wallet', 'woocommerce' )
			),
			'description' => __( ($message == "" ? "Are you sending the token from your wallet or minting a new token?" : $message), 'woocommerce' ),
			'custom_attributes' => $customAttr
		)
	);
	echo '</div>';
}

// Save Fields
add_action('woocommerce_process_product_meta', 'woocommerce_enjin_gateway_fields_save');
function woocommerce_enjin_gateway_fields_save($post_id) {
    $woocommerce_custom_product_text_field = $_POST['_token_enjin_id_text_field'];
    if (!empty($woocommerce_custom_product_text_field)) {
		update_post_meta($post_id, '_token_enjin_id_text_field', esc_attr($woocommerce_custom_product_text_field));
	}
	
	$woocommerce_custom_product_request_field = $_POST['_token_enjin_request_text_field'];
    if (!empty($woocommerce_custom_product_request_field)) {
		update_post_meta($post_id, '_token_enjin_request_text_field', esc_attr($woocommerce_custom_product_request_field));
	}
}

// Process enjin items after payment is processed
add_action('woocommerce_order_status_pending_to_completed', 'enjin_gateway_process_order');
add_action('woocommerce_order_status_pending_to_processing', 'enjin_gateway_process_order');
//add_action('woocommerce_order_status_processing', 'enjin_gateway_process_order', 10, 1);
function enjin_gateway_process_order($order_id) {
	processOrder($order_id);
}

function processOrder($order_id) {
	require_once (__DIR__ . '/EnjinAPI.php');
    $options = get_option( 'enjin_settings' );
	
    $order = new WC_Order( $order_id );
    $items = $order->get_items();
	$user = $order->get_user();
	$user_id = $order->get_user_id();
	
	$enjin = new EnjinAPIMeta($options['enjin_app_id'], $options['enjin_app_secret'], $options['enjin_app_type'], $options['enjin_identity_id']);
	$enjin->authorize();
	
	if ($enjin->isAuthorized()) {
		$ethereumAddress = esc_attr( get_the_author_meta( 'ethereumAddress', $user->ID ) );
	
		if ($ethereumAddress != "") {
			
			foreach ($items as $item) {
				if (get_post_meta($item['product_id'], '_token_enjin_id_text_field', true)) {
					$requestType = get_post_meta($item['product_id'], '_token_enjin_request_text_field', true);
					if (!$requestType) {
						$requestType = "mint";
					}
					
					if ($requestType == "send") {
						$data = get_post_meta($item['product_id'], '_token_enjin_id_text_field', true);
						$ids = explode(',',$data);
						foreach($ids as $key) {
							if ($key != "") {
								$id = $key;
								$amount = 1;
								
								if (strpos($key, '|') !== false) {
									$id = explode('|',$key)[1];
									$amount = explode('|',$key)[0];
								}
								
								$response = $enjin->sendItem($ethereumAddress, $id, $item['qty'] * $amount);
								$order->add_order_note( _("Inline Response: " + json_encode($response)));
							}
						}
					} else {
						$data = get_post_meta($item['product_id'], '_token_enjin_id_text_field', true);
						$ids = explode(',',$data);
						foreach($ids as $key) {					
							if ($key != "") {
								$id = $key;
								$amount = 1;
								
								if (strpos($key, '|') !== false) {
									$id = explode('|',$key)[1];
									$amount = explode('|',$key)[0];
								}
								
								error_log("sending " . ($item['qty'] * $amount) . "x " . $id . " to " . $ethereumAddress);
								
								$response = $enjin->mintItem($ethereumAddress, $id, $item['qty'] * $amount);
								$order->add_order_note( _("Inline Response: " . json_encode($response)));
							}
						}
					}
					
					error_log(json_encode($response));
					
					if (isset($response["CreateEnjinRequest"]["id"])) {
						$order->add_order_note( _("Order Processed: Sent " . $item['qty'] . "x " . get_post_meta($item['product_id'], '_token_enjin_id_text_field', true) . " to address: " . $ethereumAddress));
						
						$item->update_meta_data( 'wc_enjin_transaction_id', $response["CreateEnjinRequest"]["id"] );
						$item->update_meta_data( 'wc_enjin_tc_status', "Pending" );
						$item->update_meta_data( 'wc_enjin_tp_trans_id', "" );
						
						$order->update_status( 'wc-sending-items' );
					} else {
						$order->add_order_note(_("Error: " . json_encode($response)));
					}
				}
			}
		}
	} else {
		$order->add_order_note(_("Could not Authorize meta Basket"));
	}
}



// Edit Meta Data Display
add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'edit_enjin_transaction_meta_data', 10, 1 );
function edit_enjin_transaction_meta_data($formatted_meta){
    $temp_metas = [];
    foreach($formatted_meta as $key => $meta) {
        if ( isset( $meta->key ) && $meta->key == 'wc_enjin_tc_status' ) {
			$meta->display_key = "Transaction Status";
			
			$value = $meta->value;
			
			switch(strtolower($value)) {
				case "pending":
					$value = '<a href="#" data-toggle="tooltip" data-placement="bottom" title="Transaction is created on the Enjin Platform, but has not yet been signed by the user/dev.">Pending</a>';
					break;
				case "tp processing":
					$value = '<a href="#" data-toggle="tooltip" data-placement="bottom" title="Transaction has been signed and is waiting for the Enjin Platform to process the transaction for broadcast.">TP Processing</a>';
					break;
				case "broadcast":
					$value = '<a href="#" data-toggle="tooltip" data-placement="bottom" title="Transaction has been signed and has been broadcast but has not yet been confirmed on the blockchain.">Broadcast</a>';
					break;
				case "executed":
					$value = '<a href="#" data-toggle="tooltip" data-placement="bottom" title="The transaction has received confirmation on the blockchain and the Enjin Platform.">Executed</a>';
					break;
				case "canceled user":
					$value = '<a href="#" data-toggle="tooltip" data-placement="bottom" title="The user has canceled the PENDING transaction/not signed.">Canceled User</a>';
					break;
				case "canceled platform":
					$value = '<a href="#" data-toggle="tooltip" data-placement="bottom" title="The Enjin Platform has canceled the PENDING transaction.">Canceled Platform</a>';
					break;
				case "failed":
					$value = '<a href="#" data-toggle="tooltip" data-placement="bottom" title="Transaction has failed on the Enjin Platform.">Failed</a>';
					break;
				case "dropped":
					$value = '<a href="#" data-toggle="tooltip" data-placement="bottom" title="Transaction was not mined on the blockchain and has since been dropped.">Dropped</a>';
					break;
				default:
					$value = '<a href="#" data-toggle="tooltip" data-placement="bottom" title="Transaction is created on the Enjin Platform, but has not yet been signed by the user/dev.">Pending</a>';
					break;
			}
			
			$meta->display_value = $value;
			
            $temp_metas[ $key ] = $meta;
        }
		
		if ( isset( $meta->key ) && $meta->key == 'wc_enjin_tp_trans_id' ) {
			if ($meta->value != "") {
				$meta->display_key = "Transaction ID";
				
				$temp_metas[ $key ] = $meta;
			}
        }
		if ( is_admin() ) {
			if ( isset( $meta->key ) && $meta->key == 'wc_enjin_transaction_id' ) {
				$meta->display_key = "TP Transaction ID";
				
				if (is_admin()) {
					$meta->display_value = $meta->value . "" . "<button class='button refresh-button' data-transid='" . $meta->value  . "'>Check Status</button>";
				}
				
				$temp_metas[ $key ] = $meta;
			}
		}
    }
    return $temp_metas;
}

add_action( 'enjin_status_schedule_hook', 'enjin_status_schedule_hook_action', 10, 0 );

function enjin_status_schedule_hook_action(){
    $args = array(
		'status' => 'wc-sending-items',
	);
	$orders = wc_get_orders( $args );
	
	// Load settings and connect to enjin
	$options = get_option( 'enjin_settings' );

	$enjin = new EnjinAPIMeta($options['enjin_app_id'], $options['enjin_app_secret'], $options['enjin_app_type'], $options['enjin_identity_id']);
	$enjin->authorize();
	
	foreach ($orders as $order) {
		$items = $order->get_items();
		
		foreach ($items as $item) {
			if ($item->get_meta("wc_enjin_transaction_id") != "") {
				$transid = $item->get_meta("wc_enjin_transaction_id");
				$data = $enjin->getTransactionData($transid);

				$status = $data['state'];
				
				wc_update_order_item_meta($item->get_id(), 'wc_enjin_tc_status', ucwords(str_replace("_", " ", strtolower($status))) );

				if (!is_null($data['transactionId'])) {
					wc_update_order_item_meta($item->get_id(), 'wc_enjin_tp_trans_id', $data['transactionId'] );
				}
				
				if (!is_null($data['token']['reserve'])) {
					update_post_meta($item->get_id(), '_stock', $data['token']['reserve']);
					wc_delete_product_transients( $item->get_id() );
				}
				
				if ($status == "CANCELED_USER" || $status == "CANCELED_PLATFORM" || $status == "DROPPED" || $status == "FAILED") {
					$order->update_status( 'wc-send-error' );
				}
				
				if ($status == "EXECUTED") {
					$order->update_status( 'completed' );
				}
			}
		}
	}
}
