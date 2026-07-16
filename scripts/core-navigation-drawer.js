(function () {
	const navigationSelector = 'header .pns-primary-navigation';
	const drawerSelector =
		'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open';
	const openButtonSelector =
		'header .pns-primary-navigation > .wp-block-navigation__responsive-container-open';
	const submenuSelector = '.wp-block-navigation-item.has-child';
	const parentLabelSelector = ':scope > .wp-block-navigation-item__content';
	const toggleSelector = ':scope > .wp-block-navigation-submenu__toggle';

	function getOpenDrawer(element) {
		return element?.closest?.(drawerSelector);
	}

	function getSubmenuItem(element) {
		const submenuItem = element?.closest?.(submenuSelector);

		if (!submenuItem || !getOpenDrawer(submenuItem)) {
			return null;
		}

		return submenuItem;
	}

	function closeExpandedSubmenus(drawer) {
		drawer
			.querySelectorAll(
				'.wp-block-navigation-submenu__toggle[aria-expanded="true"]'
			)
			.forEach((toggle) => toggle.click());
	}

	function setCloseButtonPosition(openButton) {
		const navigation = openButton?.closest?.(navigationSelector);

		if (!navigation) {
			return;
		}

		const icon = openButton.querySelector('svg');
		const rect = (icon || openButton).getBoundingClientRect();

		if (!rect.width || !rect.height) {
			return;
		}

		navigation.style.setProperty(
			'--pns--navigation--drawer-close-block-start',
			`${rect.top}px`
		);
		navigation.style.setProperty(
			'--pns--navigation--drawer-close-block-size',
			`${rect.height}px`
		);
		navigation.style.setProperty(
			'--pns--navigation--drawer-close-inline-end',
			`${window.innerWidth - rect.right}px`
		);
	}

	document.addEventListener(
		'pointerdown',
		(event) => {
			const openButton = event.target.closest(openButtonSelector);

			if (openButton) {
				setCloseButtonPosition(openButton);
			}
		},
		true
	);

	document.addEventListener(
		'click',
		(event) => {
			const openButton = event.target.closest(openButtonSelector);

			if (openButton) {
				setCloseButtonPosition(openButton);
			}
		},
		true
	);

	document.addEventListener(
		'pointerenter',
		(event) => {
			if (getSubmenuItem(event.target)) {
				event.stopPropagation();
			}
		},
		true
	);

	document.addEventListener(
		'pointerleave',
		(event) => {
			if (getSubmenuItem(event.target)) {
				event.stopPropagation();
			}
		},
		true
	);

	document.addEventListener(
		'click',
		(event) => {
			const drawer = getOpenDrawer(event.target);

			if (!drawer) {
				return;
			}

			const parentLabel = event.target.closest(
				'.wp-block-navigation-item.has-child > .wp-block-navigation-item__content'
			);

			if (
				!parentLabel ||
				parentLabel.closest(drawerSelector) !== drawer
			) {
				return;
			}

			const submenuItem = parentLabel.closest(submenuSelector);
			const directParentLabel =
				submenuItem?.querySelector(parentLabelSelector);
			const toggle = submenuItem?.querySelector(toggleSelector);

			if (parentLabel !== directParentLabel || !toggle) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();
			toggle.click();
		},
		true
	);

	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			if (
				mutation.type !== 'attributes' ||
				mutation.attributeName !== 'class'
			) {
				return;
			}

			const drawer = mutation.target;

			if (
				drawer instanceof HTMLElement &&
				drawer.matches(drawerSelector)
			) {
				closeExpandedSubmenus(drawer);
			}
		});
	});

	document
		.querySelectorAll(
			'header .pns-primary-navigation > .wp-block-navigation__responsive-container'
		)
		.forEach((drawer) =>
			observer.observe(drawer, {
				attributes: true,
				attributeFilter: ['class'],
			})
		);
})();
