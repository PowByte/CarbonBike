var $ = jQuery.noConflict();
$(document).ready(function(){
	console.log('manfin ready');
	
	// if ($('select#billing_type').val() == 'Persoana Juridica') {
		// $('.company-field').removeClass('company-hide');
	// } else {
		// $('.company-field').addClass('company-hide');
	// }
	if ($('#billing_type').val() == 'Persoana Juridica') {
		$('.company-field').removeClass('company-hide');
	} else {
		$('.company-field').addClass('company-hide');
	}
	
	$('select#billing_type').change(function(){
		console.log();
		if ($(this).val() == 'Persoana Juridica') {
			$('.company-field').removeClass('company-hide');
		} else {
			$('.company-field').addClass('company-hide');
		}
	});
});
