/**
 * Spread this base into the consumer root config; `extends` drops `ignoreFiles`.
 * ignoreFiles covers dependencies and build output only — consumer source under assets/** is linted.
 */

module.exports = {
	extends: ['@wordpress/stylelint-config/scss'],
	ignoreFiles: [
		'vendor/**',
		'node_modules/**',
		'**/build/**',
		'**/*.min.css',
	],
};
