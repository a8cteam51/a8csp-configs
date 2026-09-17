<?php declare( strict_types=1 );
/**
 * Fixture carrying two constructs PHP 8.2 deprecated. Only the PHPCompatibility pre-release
 * majors sniff them, so a scan that reports nothing here proves the `testVersion` checks
 * resolved to the stable releases and are passing vacuously.
 *
 * @package A8C\SpecialProjects\Configs\Tests
 */

$php_compat_probe = 'probe';

$php_compat_interpolated = "${php_compat_probe}";

$php_compat_encoded = \utf8_encode( $php_compat_probe );
