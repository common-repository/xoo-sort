jQuery(document).ready(function($) {
	$('.xse-trigger').on('change', function() {
		var parent = $(this).closest( "div" ).attr('id');
		var data = {
			action: 'xse_trigger',
			xse_nonce: xse_vars.xse_nonce
		}
		data['option'] = {};
		data['option'][parent] = {}
		data['option'][parent][event.target.id] = document.getElementById(event.target.id).checked;
		$.post(ajaxurl, data, function(response) {
		});
	});
});