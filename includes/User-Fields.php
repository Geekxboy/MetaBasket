<?php
if (!defined('ABSPATH')) exit;

add_action( 'meta_plugin_enjin_user_fields', 'meta_basket_fields', 1 );

function meta_basket_fields() {
	add_action( 'show_user_profile', 'enjin_extra_user_profile_fields' );
	add_action( 'edit_user_profile', 'enjin_extra_user_profile_fields' );

	function enjin_extra_user_profile_fields( $user ) { 
		?>
		<h3 id="reewardio-link"><?php _e("Enjin Account Information", "blank"); ?></h3>

		<table class="form-table">
			<tr>
				<th><label for="ethereumAddress"><?php _e("NFT Address"); ?></label></th>
				<td>
					<input type="text" name="ethereumAddress" id="ethereumAddress" value="<?php echo esc_attr( get_the_author_meta( 'ethereumAddress', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please provide your ETH/ENJ/JENJ wallet address where we will send your NFTs. </br> We recommend the <a href="https://enjin.io/products/wallet" target="_blank">Enjin Wallet</a>.</span>
				</td>
			</tr>
		</table>
		<?php
	}

	add_action( 'personal_options_update', 'enjin_save_extra_user_profile_fields' );
	add_action( 'edit_user_profile_update', 'enjin_save_extra_user_profile_fields' );

	function enjin_save_extra_user_profile_fields( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) ) { 
			return false; 
		}

		$code = $_POST['ethereumAddress'];
		update_user_meta( $user_id, 'ethereumAddress', $code);
	}


	/* Woocommerce Settings */
	add_action( 'woocommerce_edit_account_form', 'add_enjin_address_to_edit_account_form' );
	function add_enjin_address_to_edit_account_form() {
		$user = wp_get_current_user();
		?>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" style="margin-bottom: 50px;">
			<label for="ethereumAddress"><?php _e( 'NFT Address', 'woocommerce' ); ?></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-control" name="ethereumAddress" id="ethereumAddress" value="<?php echo esc_attr( $user->ethereumAddress ); ?>" />
			<span class="description">Please provide your ETH/ENJ/JENJ wallet address where we will send your NFTs. </br> We recommend the <a href="https://enjin.io/products/wallet" target="_blank">Enjin Wallet</a>.</span>
		</p>
		<?php
	}

	add_action( 'woocommerce_save_account_details', 'save_enjin_address_account_details', 12, 1 );
	function save_enjin_address_account_details( $user_id ) {

		if( isset( $_POST['ethereumAddress'] ) )
			update_user_meta( $user_id, 'ethereumAddress', sanitize_text_field( $_POST['ethereumAddress'] ) );
	}
}