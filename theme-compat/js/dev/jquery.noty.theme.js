(function($) {
	$.noty.layouts.topCenter = {
		name: 'topCenter',
		options: {},
		container: {
			object: '<ul id="noty_topCenter_layout_container" />',
			selector: 'ul#noty_topCenter_layout_container',
			style: function() {
				$(this).css({
					width: '600px'
				});
				
				$(this).css({
					left: ($(window).width() - $(this).outerWidth()) / 2 + 'px'
				});
			}
		},
		parent: {
			object: '<li />',
			selector: 'li',
			css: {}
		},
		css: {
			display: 'none'
		},
		addClass: ''
	};

})(jQuery);


(function($) {
	$.noty.themes.default = {
		name: 'default',
		helpers: {
			borderFix: function() {
				if (this.options.dismissQueue) {
					var selector = this.options.layout.container.selector + ' ' + this.options.layout.parent.selector;
					$(selector).css({borderRadius: '0px 0px 0px 0px'});
					$(selector).first().css({'border-top-left-radius': '5px', 'border-top-right-radius': '5px'});
					$(selector).last().css({'border-bottom-left-radius': '5px', 'border-bottom-right-radius': '5px'});
				}
			}
		},
		style: function() {},
		callback: {
			onShow: function() { $.noty.themes.default.helpers.borderFix.apply(this); },
			onClose: function() { $.noty.themes.default.helpers.borderFix.apply(this); }
		}
	};

})(jQuery);