/**
 * Spread this base into the consumer root config; `extends` drops `ignoreFiles`.
 */

module.exports = {
	extends: [ '@wordpress/stylelint-config/scss' ],
	ignoreFiles: [
		'vendor/**',
		'node_modules/**',
		'**/build/**',
		'**/*.min.css',
	],
	reportDescriptionlessDisables: true,
	reportInvalidScopeDisables: true,
	reportNeedlessDisables: true,
	rules: {
		// Core, WooCommerce and prefixed post-type body classes are not the developer's to rename.
		'selector-class-pattern': null,
	},
};
