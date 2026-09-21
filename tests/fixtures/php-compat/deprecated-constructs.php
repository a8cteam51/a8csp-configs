<?php declare( strict_types=1 );
/**
 * Two deprecated constructs the PHPCompatibility check in quality.yml must flag.
 *
 * @package A8C\SpecialProjects\Configs\Tests
 */

$php_compat_probe = 'probe';

$php_compat_interpolated = "${php_compat_probe}";

$php_compat_encoded = \utf8_encode( $php_compat_probe );
