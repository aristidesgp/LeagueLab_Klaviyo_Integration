jQuery(document).ready(function ($) {
	$("#related-products-close").click(function () {
		$("#related-products-popup").fadeOut();
	});
	$(".add-to-cart").click(function (e) {
		$("#related-products-popup").fadeOut();
		e.preventDefault();
		var productId = $(this).data("product-id");
		LLKI_Add_To_Card(productId);
	});
	function LLKI_Add_To_Card(obj) {
		$.ajax({
			type: "POST",
			url: parameters.ajax_url,
			data: {
				action: "LLKI_add_to_cart",
				productId: obj,
			},
			dataType: "json",
			beforeSend: function () {},
			complete: function () {},
			success: function (response) {
				if (response.success) {
					//location.reload();
				} else {
					alert("We could not add the product");
				}
			},
		});
	}
});
