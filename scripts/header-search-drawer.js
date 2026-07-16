(function () {
	const desktopQuery = window.matchMedia('(min-width: 1152px)');

	function initialiseSearchDrawer(searchDrawer) {
		if (searchDrawer.dataset.pnsHeaderSearchInitialised === 'true') {
			return;
		}

		const toggle = Array.from(
			searchDrawer.querySelectorAll(
				'.pns-primary-navigation a.wp-block-navigation-item__content'
			)
		).find((link) => '/search/' === new URL(link.href).pathname);
		const drawer = searchDrawer.querySelector('.pns-header-search__drawer');
		const input = searchDrawer.querySelector('.pns-header-search__input');

		if (!toggle || !drawer || !input) {
			return;
		}

		searchDrawer.dataset.pnsHeaderSearchInitialised = 'true';
		let closeTimer;
		let closeTransitionHandler;

		function clearClosing() {
			window.clearTimeout(closeTimer);

			if (closeTransitionHandler) {
				drawer.removeEventListener(
					'transitionend',
					closeTransitionHandler
				);
				closeTransitionHandler = undefined;
			}
		}

		function setToggleDisclosure(enabled) {
			if (enabled) {
				toggle.setAttribute('aria-controls', drawer.id);
				toggle.setAttribute('aria-expanded', 'false');
				return;
			}

			toggle.removeAttribute('aria-controls');
			toggle.removeAttribute('aria-expanded');
		}

		function finishClosing() {
			clearClosing();

			if (!drawer.classList.contains('is-open')) {
				drawer.hidden = true;
				drawer.inert = false;
			}
		}

		function setOpen(open, restoreFocus = false, immediately = false) {
			const shouldOpen = open && desktopQuery.matches;
			clearClosing();

			if (desktopQuery.matches) {
				toggle.setAttribute('aria-expanded', String(shouldOpen));
			}

			if (shouldOpen) {
				drawer.hidden = false;
				drawer.inert = false;
				drawer.classList.remove('is-open');

				window.requestAnimationFrame(() => {
					if (!drawer.hidden) {
						drawer.classList.add('is-open');
						input.focus({ preventScroll: true });
					}
				});

				return;
			}

			if (restoreFocus) {
				toggle.focus({ preventScroll: true });
			}

			if (
				drawer.hidden ||
				immediately ||
				window.matchMedia('(prefers-reduced-motion: reduce)').matches
			) {
				drawer.classList.remove('is-open');
				finishClosing();
				return;
			}

			drawer.inert = true;
			closeTransitionHandler = (event) => {
				if (
					event.target === drawer &&
					'grid-template-rows' === event.propertyName
				) {
					finishClosing();
				}
			};
			drawer.addEventListener('transitionend', closeTransitionHandler);
			closeTimer = window.setTimeout(finishClosing, 500);
			drawer.classList.remove('is-open');
		}

		setToggleDisclosure(desktopQuery.matches);

		toggle.addEventListener('click', (event) => {
			if (!desktopQuery.matches) {
				return;
			}

			event.preventDefault();
			const isOpen = drawer.classList.contains('is-open');
			setOpen(!isOpen, isOpen);
		});

		searchDrawer.addEventListener('keydown', (event) => {
			if ('Escape' === event.key && !drawer.hidden) {
				event.preventDefault();
				setOpen(false, true);
			}
		});

		searchDrawer.addEventListener('focusout', () => {
			window.setTimeout(() => {
				if (
					!drawer.hidden &&
					!searchDrawer.contains(document.activeElement)
				) {
					setOpen(false);
				}
			});
		});

		document.addEventListener('pointerdown', (event) => {
			if (!drawer.hidden && !searchDrawer.contains(event.target)) {
				setOpen(false);
			}
		});

		desktopQuery.addEventListener('change', (event) => {
			if (!event.matches) {
				setOpen(false, false, true);
			}

			setToggleDisclosure(event.matches);
		});
	}

	document
		.querySelectorAll('.pns-header-search')
		.forEach(initialiseSearchDrawer);
})();
