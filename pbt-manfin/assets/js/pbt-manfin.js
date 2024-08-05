var $ = jQuery.noConflict();

// A $( document ).ready() block.
function pbt_ajax_update_product(id){ 						
	jQuery.ajax({
		type : "get",
		dataType : "json",
		url : pbt_manfin_params.siteurl + "/wp-json/pbt/v1/product-update/" + id,
		data : "",
		success: function(response) {							
			// alert("success");
			console.log(response);
			if(response.type == "success") {
				console.log(response.message);
				// return false;
			}
			else {
				console.log("Nu s-a updatat produs CodStoc : " + id );
				// return false;
			}
		}
	});														
}

function pbt_ajax_update_product_delayed(ids, i){ 						
	jQuery.ajax({
		type : 'get',
		dataType : 'json',
		url : pbt_manfin_params.siteurl + '/wp-json/pbt/v1/product-update/' + ids[i],
		data : '',
		success: function(response) {							
			// alert("success");
			// console.log(ids);
			console.log(response);
			console.log(response.message);
			$('<p class="pbt_log_succes">' + response.message + '</p>').prependTo($('#pbt_manfin_log'));
			if(i < ids.length){
				i++;
				setTimeout( pbt_ajax_update_product_delayed( ids, i ) , 3000);
			}
		},
		error: function(response) {							
			// alert("error");
			console.log(response);
			console.log(response.message);			
			$('<p class="pbt_log_error">' + response.message + '</p>').prependTo($('#pbt_manfin_log'));
			if(i < ids.length){
				i++;
				setTimeout( pbt_ajax_update_product_delayed(ids, i ) , 3000);
			}
		}
	});														
}

$( document ).ready(function() {
    console.log( 'ready!' );
	if ( typeof pbt_manfin_params === 'undefined' || typeof pbt_manfin_params === 'undefined' ) {
		return false;
	}
	$('#pbt-update-all').click(function(){
		jQuery.ajax({
			type : 'get',
			dataType : 'json',
			url : pbt_manfin_params.siteurl + '/wp-json/pbt/v1/get-manfin-ids/',
			data : '',
			success: function(response) {				
				console.log(response);
				$('<p class="pbt_log_succes">' + response.message + '</p>').prependTo($('#pbt_manfin_log'));
				var i = 0, ids = response.ids;
				setTimeout( pbt_ajax_update_product_delayed(ids, i) , 3000);

			},
			error: function(response){
				$('<p class="pbt_log_error">' + response.message + '</p>').prependTo($('#pbt_manfin_log'));
			}
		});
	});
});