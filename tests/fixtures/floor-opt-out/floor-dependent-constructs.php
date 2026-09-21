<?php declare( strict_types=1 );
/**
 * Two constructs whose reports depend on the floors, for the floor opt-out check in quality.yml.
 *
 * @package A8C\SpecialProjects\Configs\Tests
 */

// New in PHP 8.4 and not polyfilled by WordPress, so PHPCompatibilityWP reports it only when testVersion starts below 8.4.
$floor_probe_power = \fpow( 2.0, 3.0 );

// Deprecated in WordPress 6.9: an error when minimum_wp_version is 6.9 or later, a warning below it.
$floor_probe_utf8 = \seems_utf8( 'probe' );
