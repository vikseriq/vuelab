(function (__global) {
	if (typeof __global.vueLauncher !== 'undefined')
		return;

	__global.vueLauncher = {
		launchScope: 'body',

		selectors: [
			'.js-v-scope'
		],

		// Launch Vue instances in all `vueLauncher.selectors` inside `scope`
		launch: function (scope) {
			for (var selector_i in this.selectors) {
				var places = document.querySelectorAll(scope + ' ' + this.selectors[selector_i]);
				if (!places) {
					continue;
				}
				for (var i = 0; i < places.length; i++) {
					var element = places[i];
					if (typeof element.__with_vue !== 'undefined'){
						// already have instance
						continue;
					}
					element.__with_vue = true;
					// get instance name from attribute or generate random
					var instanceName = element.getAttribute('data-instance')
						|| ([
							'Vue',
							element.tagName.replace(/([^A-Za-z])/g, '_'),
							Math.ceil(Math.random() * 10000)
						].join(''));

					__global[instanceName] = new Vue({
						el: element,
						computed: {
							it: function () {
								if (this.$children.length === 1) {
									return this.$children[0];
								} else {
									return null;
								}
							}
						}
					})
				}
			}
		},

		globalLaunch: function () {
			// indicate that Vue all set
			document.dispatchEvent(new CustomEvent('vueReady'));
			// launch in whole body
			__global.vueLauncher.launch(__global.vueLauncher.launchScope);
		},

		awaitVue: {
			retry: 6,
			delay: 200,
			factor: 2
		},

		// Awaiting logic for global Vue lib script registration.
		// Useful to deal with `async` scripts.
		// Remove `awaitVue` if sure that Vue loaded before `vueLauncher.boot` executed.
		onReady: function () {
			var self = __global.vueLauncher;
			if (typeof __global.Vue !== 'undefined' || typeof __global.Vue !== 'undefined') {
				debugger;
				// Vue exists - start
				self.globalLaunch();
				// destroy self
				self.onReady = function () {};
			} else {
				if (self.awaitVue.retry > 0) {
					// schedule next
					setTimeout(self.onReady, self.awaitVue.delay);
					self.awaitVue.delay *= self.awaitVue.factor;
					self.awaitVue.retry--;
				} else {
					// no Vue - no fun
					if (console && typeof console.warn === 'function') {
						console.warn('vueLauncher: no global Vue instance presented. Launch cancelled.');
					}
				}
			}
		}
	};

	// Boot on document ready.
	// Remove and call vueLauncher.globalLaunch if no need to start on document.ready.
	if (document.readyState !== 'loading') {
		__global.vueLauncher.onReady();
	} else {
		document.addEventListener('DOMContentLoaded', __global.vueLauncher.onReady);
	}

})(window);