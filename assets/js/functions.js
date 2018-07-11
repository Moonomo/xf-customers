/* global xfCustomersi18n */
(function ( $ ) {
	'use strict';

	$(document).ready(function () {

		$('.xf-customer-form').on('click', '.xf-btn', function ( event ) {
			event.preventDefault();

			var data = {},
				nonce_field = xfCustomersi18n.nonce_field_key,
				scope_field = xfCustomersi18n.scope_field_key,
				date_field = xfCustomersi18n.date_field_key,
				$btn = $(this),
				$form = $btn.parents('form');

			$btn.prop('disabled', true);

			data[ nonce_field ] = $form.find('input[name="' + nonce_field + '"]').val();
			data[ scope_field ] = $form.find('input[name="' + scope_field + '"]').val();
			data[ date_field ] = $form.find('input[name="' + date_field + '"]').val();
			data[ 'fullname' ] = $form.find('input[name="fullname"]').val();
			data[ 'phone' ] = $form.find('input[name="phone"]').val();
			data[ 'email' ] = $form.find('input[name="email"]').val();
			data[ 'budget' ] = $form.find('input[name="budget"]').val();
			data[ 'message' ] = $form.find('textarea').val();

			$.post(
				xfCustomersi18n.ajax_url,
				data
			)
				.done(function ( response ) {
					switch (response.type) {
						case 'success':
							if (response.data) {
								var $parent = $form.parent();
								$parent.text('');
								$parent.append(response.data);
							}
							break;
						case 'error' :
							if (response.data) {
								$form.find('.ajax-response').text('');
								$form.find('.ajax-response').append(response.data);
							}
							break;
					}
				})
				.fail(function () {
				})
				.always(function () {
					$btn.prop('disabled', false);
				});
		});
	});

})(jQuery);