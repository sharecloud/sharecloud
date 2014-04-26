// Tooltip plugin
(function($) {
	$.fn.tooltip = function(){
		$(this).each(function(){
			var tip		= $(this).attr('title');
			var offset	= 15;
			
			if(tip != '' && $(this).attr('tooltip') != 'off') {
				$(this).attr('title', '');
				$(this).bind({
					mouseenter: function(e) {
						if($('.tooltip').css('opacity') != 0) {
							$('.tooltip').stop().remove();
						}
											
						if($(this).attr('tooltip') != 'off') {					
							$('<div>', {
								'class': 'tooltip',
								id: 'tooltip',
								css: {
									display: 'none',
									position: 'absolute',
									top: e.pageY+offset,
									left: e.pageX+offset
								},
								html: tip
							})
							.appendTo('body')
							.fadeIn(100);
						}
					},
					mouseleave: function() {
						$('.tooltip').fadeOut(
						100,
						function(){
							$(this).remove();
						});
					},
					mousemove: function(e) {
						$('.tooltip').css({
							top: e.pageY+offset,
							left: e.pageX+offset
						});
					}
				});
			}
		});
	}
})(jQuery);