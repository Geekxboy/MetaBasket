jQuery(document).ready(function(){
	jQuery('[data-toggle="tooltip"]').tooltip();   
	
	jQuery( ".refresh-button" ).click(function(e){
		var data = jQuery(this).data('transid');
		e.preventDefault();
		
		console.log("Status check running");
		jQuery.ajax({
			type : "post",
			dataType : "json",
			url : metabasketAjax.ajaxurl,
			data : {action: "meta_basket_check_status"},
			success: function(response) {
				if(response.type == "success") {
					console.log("Status check completed");
					location.reload();
				}
			},
			error: function(response) {
				jQuery(".allowance-status").html("Error connecting to your LiveWallet. Please try again later!");
			}
		});
	});
	
	jQuery('input#_token_enjin_id_text_field').each(function(){
		jQuery(this).hide();
		var div = jQuery("<div>", {"class": "box-enjin-id"});
		jQuery(this).after(div);
		
		var tokenID = jQuery(this).val();
		
		console.log(tokenID);
		
		var arr = tokenID.split(',');
		jQuery.each( arr, function( index, value ) {
			var id = value;
			var amount = 1;
			
			if (id.includes("|")) {
				var vals = id.split('|');
				id = vals[1];
				amount = vals[0];
			}
			
			if (value != "") {
				
				var input = jQuery('<div><input class="enjin_num_input" value="' + amount + '" type="number"><input class="enjin_id_input" value="' + id + '"><button class="delete_token_id"><span class="dashicons dashicons-trash"></span></button></div>');
				jQuery(div).append(input);
			}
		});
		
		var addBtn = jQuery("<button>", {"class": "add_token_id"});
		jQuery(addBtn).html('<span class="dashicons dashicons-plus"></span>');
		jQuery(div).after(addBtn);
	});
	
	jQuery(document).on('keyup','.enjin_id_input',function(){
		updateTokens();
	});
	
	jQuery(document).on('change','.enjin_num_input',function(){
		updateTokens();
	});
	
	jQuery(document).on('click','.delete_token_id',function(e){
		e.preventDefault();
		jQuery( this ).parent().remove();
		updateTokens();
	});
	
	jQuery(".add_token_id").click(function(e){
		e.preventDefault();
		var div = jQuery(".box-enjin-id");
		
		var input = jQuery('<div><input class="enjin_num_input" value="1" type="number"><input class="enjin_id_input"><button class="delete_token_id"><span class="dashicons dashicons-trash"></span></button></div>');
		jQuery(div).append(input);
	});
	
	function updateTokens() {
		var idString = "";
		jQuery( ".enjin_id_input" ).each(function() {
			var textVal = jQuery(this).val();
			var amount = jQuery(this).parent().find(".enjin_num_input").val();
			if (textVal != "") {
				idString = idString + amount + "|" + textVal + ",";
			}
		});
		
		jQuery('input#_token_enjin_id_text_field').val(idString);
		//console.log(idString);
	}
});