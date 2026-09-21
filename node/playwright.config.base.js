/**
 * Spread nested keys (`use`, `webServer`, `projects`) individually when overriding the returned config.
 */

module.exports = ( { port } ) => {
	// @wordpress/scripts reads both variables while it is being required and derives use.baseURL,
	// webServer.port, outputDir and the storage-state path from them, which is why the require sits
	// below them rather than at module scope. `??=` leaves an exported WP_BASE_URL authoritative.
	process.env.WP_BASE_URL ??= `http://localhost:${ port }`;

	// Keeps Playwright output (storage states, test-results) out of the consumer's repository root.
	process.env.WP_ARTIFACTS_PATH ??= 'tests/.cache/artifacts';

	const wpBaseConfig = require( '@wordpress/scripts/config/playwright.config.js' );

	return {
		...wpBaseConfig,
		testDir: 'tests/EndToEnd',
		webServer: {
			...wpBaseConfig.webServer,
			// The @wordpress/scripts default is `npm run wp-env start`, which resolves to a script named `wp-env`.
			command: 'npm run wp-env:start',
		},
	};
};
