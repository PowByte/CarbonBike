// A $( document ).ready() block.
var $ = jQuery.noConflict();
$( document ).ready(function() {
    console.log( "ready!" );
	var codStoc;
	$("#pbt-update-all").click(function(){
		jQuery.ajax({
			type : "get",
			dataType : "json",
			url : "https://powbyte.ro/~galatek/wp-json/pbt/v1/get-manfin-ids/",
			data : "",
			success: function(response) {				
				console.log(response);
				if(response.type == "success") {
					// jQuery("#vote_counter").html(response.vote_count)				
					// console.log(response.ids);
					
					var i;
					for (i = 0; i < response.ids.length; ++i) {
						// do something with `substr[i]`
						codStoc = response.ids[i];
						// console.log(codStoc);
						setTimeout(function() { 
							jQuery.ajax({
								type : "get",
								dataType : "json",
								url : "https://powbyte.ro/~galatek/wp-json/pbt/v1/product-update/" + codStoc,
								data : "",
								success: function(response) {							
									// alert("success");
									console.log(response);
									if(response.type == "success") {
										console.log(response.message);
										// return false;
									}
									else {
										console.log(("Nu s-a updatat produs CodStoc : " + codStoc));
										// return false;
									}
								}
							});	
						}, 2000);
					}
					
				}else {
					console.log("nu am putut extrage id-uri")
				}
			}
		});
	});
		
	
	
	
});