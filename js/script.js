/*
 Plugin: WP First Letter Avatar
 Plugin website: https://github.com/DanielAGW/wp-first-letter-avatar
 */


/* WP First Letter Avatar */



var data_attribute = wpfla_vars_data.img_data_attribute;
var ajaxurl = wpfla_vars_data.ajaxurl;


jQuery(document).ready(function($){

	$('[' + data_attribute + ']').each(function(){

		var gravatar_uri = $(this).attr(data_attribute);
		var current_object = $(this); // assign this img to variable
		$(current_object).removeAttr(data_attribute); // remove data attribute - not needed anymore

		var data = {
			'action' : 'gravatar_verify',
			'gravatar_uri' : gravatar_uri
		};

		$.post(ajaxurl, data, function(response){
			if (response.indexOf('1') >= 0){ // if the response contains '1'...
				$(current_object).attr('src', gravatar_uri); // replace image src with gravatar uri
			}
		});

	});

});
