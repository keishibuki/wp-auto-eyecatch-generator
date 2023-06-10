(function($) {
	$('.btn-eyecatch-generate').on('click', function(e) {
		e.preventDefault();
		const button = $(this);
		const postId = $(this).parents('tr').attr('id').replace('post-', '');

		$.ajax({
			type: 'post',
			url: 'admin-ajax.php',
			data:{
				action: 'eyecath_generate',
				mode: 'makethumb',
				postId: postId,
			},
			timeout: 5000,
			beforeSend: function() {
				button.attr('disabled', true);
			},
			complete: function() {
				button.attr('disabled', false);
			},
			success: function(response) {
				console.log(response);
				window.location.reload();
			}
		});
	});
})(jQuery);