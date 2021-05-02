<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'enjin_setup_menu', 99);
add_action('admin_init', 'enjin_register_mysettings');

function enjin_checkout_not_linked( ) {
	$url = get_permalink( get_option('woocommerce_myaccount_page_id') ) . 'edit-account/';
	echo '<div style="color:red;font-weight:700;width: 100%;">You must <a href="' . $url . '">enter your ERC-1155 Ethereum Address</a> to checkout</div></br>';
}

function enjin_replace_order_button_html( $order_button ) {
    $style = ' style="color:#fff;cursor:not-allowed;background-color:#999;"';
	enjin_checkout_not_linked( );
    return '<a class="button alt"'.$style.' name="woocommerce_checkout_place_order" id="place_order" >Place Order</a>';
}

function enjin_setup_menu() {
	add_menu_page( 'MyMeta Basket', 'MyMeta Basket', 'manage_options', 'mymeta-basket', 'nifty_basket_menu_callback', plugins_url( 'nifty-basket/Icon.png' ) );
	add_submenu_page( 'mymeta-basket', 'Enjin Settings', 'Enjin Settings', 'manage_options', 'mymeta-basket-enjin', 'enjin_gateway_menu_callback' );
}

function enjin_register_mysettings() {
    register_setting( 'enjin-gateway', 'enjin_gateway_settings' );
}

function enjin_gateway_menu_callback() {
	$options = get_option( 'enjin_settings' );
	
	if (isset($_POST['submit'])) {
		$enjin_app_id = $_POST['enjin_app_id'];
		$enjin_app_secret = $_POST['enjin_app_secret'];
		$enjin_identity_id = $_POST['enjin_identity_id'];
		$enjin_app_type = $_POST['enjin_app_type'];
		
		$options['enjin_app_id'] = $enjin_app_id;
		
		if ($enjin_app_secret != "HIDDEN") {
			$options['enjin_app_secret'] = $enjin_app_secret;
		}
		$options['enjin_identity_id'] = $enjin_identity_id;
		$options['enjin_app_type'] = $enjin_app_type;
		
		
		update_option('enjin_settings', $options);
		
		?>
		<div class="updated notice" style="margin: 5px 0;">
			<p>Your settings have been updated</p>
		</div>
		<?php
		
		$enjin = new EnjinAPIMeta($options['enjin_app_id'], $options['enjin_app_secret'], $options['enjin_app_type'], $options['enjin_identity_id']);
		$enjin->authorize();
		
		if ($enjin->isAuthorized()) {
			?>
			<div class="updated notice" style="margin: 5px 0;">
				<p>Enjin App Authorization Successful</p>
			</div>
			<?php
		} else {
			?>
			<div class="error notice" style="margin: 5px 0;">
				<p>Enjin App Authorization Failed</p>
			</div>
			<?php
		}
	}
	
	?>	
	<form method="post" enctype="multipart/form-data" novalidate="novalidate">
		<h1>Enjin App Settings</h1>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="enjin_app_id">App ID</label>
					</th>
					<td>
						<input name="enjin_app_id" type="text" id="enjin_app_id" value="<?php echo $options['enjin_app_id']; ?>" class="regular-text">
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="enjin_app_secret">App Secret</label>
					</th>
					<td>
						<input name="enjin_app_secret" type="text" id="enjin_app_secret" value="<?php if ($options['enjin_app_secret'] != '') { echo "HIDDEN";} ?>" class="regular-text">
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="enjin_identity_id">Identity ID</label>
					</th>
					<td>
						<input name="enjin_identity_id" type="text" id="enjin_identity_id" value="<?php echo $options['enjin_identity_id']; ?>" class="regular-text">
					</td>
				</tr>
				
				<tr>
					<th scope="row">Enjin App Type</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span>Enjin App Type</span></legend>
							<label>
								<input type="radio" name="enjin_app_type" id="enjin_app_type-0" value="Jumpnet" <?php if ($options['enjin_app_type'] == "Jumpnet") { echo 'checked="checked"'; } ?>>
								JumpNet
							</label>
							<br>
							<label>
								<input type="radio" name="enjin_app_type" id="enjin_app_type-1" value="Mainnet" <?php if ($options['enjin_app_type'] == "Mainnet") { echo 'checked="checked"'; } ?>>
								Mainnet
							</label>
							<br>
							<label>
								<input type="radio" name="enjin_app_type" id="enjin_app_type-2" value="Testnet" <?php if ($options['enjin_app_type'] == "Testnet") { echo 'checked="checked"'; } ?>>
								Testnet
							</label>
						</fieldset>
					</td>
				</tr>


			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>

	<?php
}