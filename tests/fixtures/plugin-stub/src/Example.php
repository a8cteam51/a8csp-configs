<?php declare( strict_types=1 );
/**
 * Example fixture class for smoke-testing shared configs.
 *
 * @package A8C\SpecialProjects\Configs\Tests
 */

namespace A8C\SpecialProjects\Configs\Tests;

/**
 * A minimal, standards-compliant class used only to prove the shipped
 * rulesets can parse and analyze real PHP without crashing.
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
