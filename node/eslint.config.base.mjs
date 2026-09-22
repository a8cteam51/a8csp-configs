/**
 * Shared flat ESLint baseline for WordPress projects; append project overrides after this array.
 */

import wordpress from '@wordpress/eslint-plugin';

export default [
	...wordpress.configs.recommended,
	...wordpress.configs[ 'test-unit' ].map( ( config ) => ( {
		...config,
		files: [ '**/test/**', '**/*.test.{js,jsx,ts,tsx,mjs,cjs}' ],
	} ) ),
	...wordpress.configs[ 'test-playwright' ].map( ( config ) => ( {
		...config,
		files: [ 'tests/EndToEnd/**' ],
	} ) ),
	{
		rules: {
			'no-console': [ 'warn', { allow: [ 'warn', 'error' ] } ],
		},
	},
	{
		ignores: [
			'vendor/**',
			'node_modules/**',
			'**/build/**',
			'**/*.min.js',
		],
	},
];
