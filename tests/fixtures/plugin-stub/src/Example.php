<?php declare( strict_types=1 );
/**
 * Example fixture class for smoke-testing shared configs.
 *
 * @package A8C\SpecialProjects\Configs\Tests
 */

namespace A8C\SpecialProjects\Configs\Tests;

/**
 * A standards-compliant class for the PHPCS and PHPStan smokes.
 */
class Example {
	/**
	 * Says hello.
	 *
	 * @param string $name Name to greet.
	 *
	 * @return string
	 */
	public function say_hello( string $name ): string {
		return "Hello, {$name}!";
	}
}
