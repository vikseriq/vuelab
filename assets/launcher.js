(function ($) {
	$(document).ready(function () {
		document.dispatchEvent(new CustomEvent('vueReady'));
		vueLaunch($('body'));
	});

	function vueLaunch(scope) {
		var tags = [
			'.js-v-scope'
		];
		if (!window.vueLab)
			window.vueLab = {};

		tags.forEach(function (tag) {
			$(tag, scope).each(function () {
				var globalName = this.getAttribute('data-instance')
					|| ('Vue' + this.tagName + Math.ceil(Math.random() * 10000));

				window[globalName] = new Vue({
					el: this
				})
			})
		});
	}
})(jQuery);