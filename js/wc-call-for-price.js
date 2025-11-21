jQuery( function( $ ) {
		/*$(document).on('click', 'a.button.callforprice', function(e){
			var product_id ='';
			if ($('form.variations_form.cart').length>0) {
				product_id = $('form.cart input[name=variation_id]').val();
			} else {
				//product_id = $('form.cart button[name=add-to-cart]').val();
				product_id = $('input[name=product-id').val();
			}
			var quantity = $('form.cart input[name=quantity]').val();
			var product_price = '';

	        $.ajax({
				type:'POST',
				url:cfpajax.url,
				data:'action=get_product_info&product_id='+product_id,
				success:function(response){
					//Заполняем скрытые поля формы для формирования тела письма
					$('form#callforpriceform input[name="product-variation"]').val(response['product_title']);
					//$('form#callforpriceform input[name="product-sku"]').val(response['product_sku']);
					//Формируем строку о запросе для всплывающего окна
					/*$('#modalCallForPrice div.wpcf7 .order_product_title').html(response['product_title']);
					$('#modalCallForPrice .sku_wrapper .sku').html(response['product_sku']);
					$.magnificPopup.close();
				}
			});
		});*/
		$(document).on('click', '.price_request_wrap', function(e){
			console.log("click price_request_wrap");
			console.log (bocajax);
			var product_id = '';
			var product_sku = '';
			if ($('form.variations_form.cart').length>0) {
				product_id = $('form.cart input[name=variation_id]').val();
			} else {
				product_id = $('form.cart button[name=add-to-cart]').val();
			}
			
			product_sku = $('#callforpriceform .sku').text();
	    
			var data = {
				action: 'get_product_info',
				product_id: product_id,
				product_sku: product_sku
			};

			$.post( cfpajax.url, data, function(response) {
				console.log(response);
				var features = $.parseJSON(response);

				//Заполняем скрытые поля формы для формирования тела письма
				$('form#callforpriceform input[name="product-title"]').val(features['product_title']);
				$('form#callforpriceform input[name="product-id"]').val(features['product_id']);
				$('form#callforpriceform input[name="product-sku"]').val(features['product_sku']);
				$('form#callforpriceform input[name="product-url"]').val(features['product_url']);
				//Формируем строку о заказе для всплывающего окна
				var order_text = features['product_title'];
				$('#modalCallForPrice div.wpcf7 .order_product_title').html(order_text);
				$('#modalCallForPrice .sku_wrapper .sku').html(features['product_sku']);
				$.magnificPopup.close();
	        });
		});
		
});
