import process from 'node:process';

const siteUrl = new URL(process.env.PNS_SITE_URL ?? 'http://localhost:10008');
const routes = [
	{ path: '/', role: 'editorial' },
	{ path: '/news/', role: 'editorial' },
	{ path: '/herstories/', role: 'editorial' },
	{ path: '/shop/', role: 'storefront' },
];

function contains(html, pattern) {
	return pattern.test(html);
}

function count(html, pattern) {
	return [...html.matchAll(pattern)].length;
}

function inspectRoute(html) {
	return {
		cartWidgets: count(html, /class=['"][^'"]*ec-cart-widget/g),
		ecwidCss: contains(html, /id=['"]ecwid-css-css['"]/),
		ecwidFrontendScript: contains(
			html,
			/(?:id=['"]ecwid-frontend-js-js(?:-extra)?['"]|data-handles=['"][^'"]*\becwid-frontend-js\b)/
		),
		ecwidHeadHints: count(
			html,
			/<link\b(?=[^>]*href=['"][^'"]*(?:app\.ecwid\.com|ecomm\.events|d1q3axnfhmyveb\.cloudfront\.net|dqzrr9k4bjpzk\.cloudfront\.net|d1oxsl77a1kjht\.cloudfront\.net))[^>]*>/g
		),
		staticTeaser: contains(html, /class=['"][^'"]*ran-ecwid-shop-teaser/),
		storefront: contains(
			html,
			/(?:static|dynamic)-ec-store-container|pns-shop-storefront/
		),
	};
}

function failuresForRoute(route, assets) {
	if ('editorial' === route.role) {
		return [
			assets.ecwidCss && 'Ecwid stylesheet is still present',
			assets.ecwidFrontendScript &&
				'Ecwid frontend script is still present',
			0 !== assets.ecwidHeadHints &&
				'Ecwid resource hints are still present',
			'/' === route.path &&
				!assets.staticTeaser &&
				'Static Ecwid teaser is missing',
		].filter(Boolean);
	}

	return [
		!assets.ecwidCss && 'Ecwid stylesheet is missing',
		!assets.ecwidFrontendScript && 'Ecwid frontend script is missing',
		!assets.storefront && 'Ecwid storefront markup is missing',
		0 === assets.cartWidgets && 'Ecwid cart markup is missing',
	].filter(Boolean);
}

const reports = [];

for (const route of routes) {
	const url = new URL(route.path, siteUrl);
	const response = await fetch(url);

	if (!response.ok) {
		throw new Error(`${route.path} returned ${response.status}`);
	}

	const assets = inspectRoute(await response.text());
	reports.push({
		path: route.path,
		role: route.role,
		...assets,
		failures: failuresForRoute(route, assets),
	});
}

console.table(
	reports.map(({ failures, ...report }) => ({
		...report,
		status: failures.length ? 'FAIL' : 'PASS',
	}))
);

const failedReports = reports.filter(({ failures }) => failures.length);

if (failedReports.length) {
	for (const { failures, path } of failedReports) {
		console.error(`${path}: ${failures.join('; ')}`);
	}

	process.exitCode = 1;
}
