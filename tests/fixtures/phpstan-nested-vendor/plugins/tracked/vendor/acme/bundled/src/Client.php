<?php

namespace Acme\Bundled;

class Client {
	public function greet( string $name ): string {
		return "Hello, {$name}!";
	}

	// Third-party code is not the site's to fix: this error fails the smoke if vendor/ is analysed.
	public function count(): int {
		return 'many';
	}
}
