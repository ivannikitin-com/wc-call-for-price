jQuery( function( $ ) {
		$(document).on('click', 'a.button.callforprice', function(e){
			var product_id ='';
			if ($('form.variations_form.cart').length>0) {
				product_id = $('form.cart input[name=variation_id]').val();
			} else {
				product_id = $('form.cart button[name=add-to-cart]').val();
			}
			var quantity = $('form.cart input[name=quantity]').val();
			var product_price = '';
	    
			var data = {
				action: 'get_product_price',
				product_id: product_id,
				quantity: quantity
			};

			console.log("product_id="+data.product_id);

			// 'ajaxurl' не определена во фронте, поэтому мы добавили её аналог с помощью wp_localize_script()
			$.post( cfpajax.url, data, function(response) {
				var features = $.parseJSON(response);
				//Заполняем скрытые поля формы для формирования тела письма
				$('form#callforpriceform input[name="product-variation"]').val(features['product_title']);
				$('form#callforpriceform input[name="product-quantity"]').val(features['product_quantity']);
				$('form#callforpriceform input[name="product-price"]').val(features['product_price']);
				$('form#callforpriceform input[name="product-sku"]').val(features['product_sku']);
				//Формируем строку о запросе для всплывающего окна
				var order_text = features['product_title'] + ', Количество: '+ features['product_quantity'] + ', Цена: ' + features['product_price'];
				$('#modalCallForPrice div.wpcf7 .order_product_title').html(order_text);
				$('#modalCallForPrice .sku_wrapper .sku').html(features['product_sku']);
				$.magnificPopup.close();
	        });
		});
});
