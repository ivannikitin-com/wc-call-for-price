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

	        $.ajax({
				type:'POST',
				url:cfpajax.url,
				data:'action=get_product_info&product_id='+product_id,
				success:function(response){
					//Заполняем скрытые поля формы для формирования тела письма
					$('form#callforpriceform input[name="product-variation"]').val(response['product_title']);
					$('form#callforpriceform input[name="product-sku"]').val(response['product_sku']);
					//Формируем строку о запросе для всплывающего окна
					/*$('#modalCallForPrice div.wpcf7 .order_product_title').html(response['product_title']);
					$('#modalCallForPrice .sku_wrapper .sku').html(response['product_sku']);*/
					$.magnificPopup.close();
				}
			});
		});
});
