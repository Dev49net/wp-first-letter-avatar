/*
	Plugin: WP First Letter Avatar
	Plugin website: http://dev49.net
 */


/* WP First Letter Avatar */



var wpfla_data_attribute = wpfla_vars_data.img_data_attribute;
var wpfla_ajaxurl = wpfla_vars_data.ajaxurl;
var wpfla_nonce = wpfla_vars_data.wp_nonce;


jQuery(document).ready(function($){

	$('[' + wpfla_data_attribute + ']').each(function(){

		var gravatar_uri = $(this).attr(wpfla_data_attribute);
		var current_object = $(this); // assign this img to variable
		$(current_object).removeAttr(wpfla_data_attribute); // remove data attribute - not needed anymore

		var data = {
			'action' : 'wpfla_gravatar_verify',
			'verification' : wpfla_nonce,
			'gravatar_uri' : gravatar_uri
		};

		$.post(wpfla_ajaxurl, data, function(response){
			if (response.indexOf('1') >= 0){ // if the response contains '1'...
				$(current_object).attr('src', gravatar_uri); // replace image src with gravatar uri
			}
		});

	});

});
