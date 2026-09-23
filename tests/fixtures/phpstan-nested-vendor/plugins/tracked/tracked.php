<?php declare( strict_types=1 );
/**
 * Plugin Name: Tracked
 * Description: Fixture plugin a site tracks together with its committed vendor/ directory.
 * Version: 1.0.0
 * Requires PHP: 8.5
 * Requires at least: 7.1
 *
 * @package A8C\SpecialProjects\Configs\Tests
 */

use Acme\Bundled\Client;
use Acme\Installed\Sdk;

/**
 * Uses one class from each vendor/ directory, so both must still be scanned.
 *
 * @return string
 */
function tracked_greeting(): string {
	return ( new Client() )->greet( Sdk::NAME );
}
