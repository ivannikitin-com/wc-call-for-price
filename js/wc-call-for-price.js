jQuery( function( $ ) {
		$(document).on('click', 'a.button.callforprice', function(e){
			var product_id ='';
			if ($('form.variations_form.cart').length>0) {
				product_id = $('form.cart input[name=variation_id]').val();
			} else {
				//product_id = $('form.cart button[name=add-to-cart]').val();
				product_id = $('input[name=product-id').val();
			}
			var quantity = $('form.cart input[name=quantity]').val();
			var product_price = '';
	    
			var data = {
				action: 'get_product_price',
				product_id: product_id,
			};

			console.log("product_id="+data.product_id);

			// 'ajaxurl' не определена во фронте, поэтому мы добавили её аналог с помощью wp_localize_script()
	        $.ajax({
				type:'POST',
				url:cfpajax.url,
				data:'action=get_product_price&product_id='+product_id,
				success:function(response){
					//Заполняем скрытые поля формы для формирования тела письма
					$('form#callforpriceform input[name="product-variation"]').val(response['product_title']);
					$('form#callforpriceform input[name="product-price"]').val(response['product_price']);
					$('form#callforpriceform input[name="product-sku"]').val(response['product_sku']);
					//Формируем строку о запросе для всплывающего окна
					var order_text = response['product_title'];
					$('#modalCallForPrice div.wpcf7 .order_product_title').html(order_text);
					$('#modalCallForPrice .sku_wrapper .sku').html(response['product_sku']);
					$.magnificPopup.close();
				}
			});
		});
});
