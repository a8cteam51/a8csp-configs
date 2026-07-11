<?php declare( strict_types=1 );

namespace A8C\SpecialProjects\Configs\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the discovery logic in `php/quality-assurance/phpstan.dist.neon.php`.
 * The file is procedural — uses `getcwd()` as its project root — so tests `chdir` to a
 * fixture directory, `require` the file, and assert on the returned config array.
 */
final class PhpstanBootstrapTest extends TestCase {

	private const CONFIG_FILE = __DIR__ . '/../../php/quality-assurance/phpstan.dist.neon.php';

	private string $project_dir;
	private string $original_cwd;

	protected function setUp(): void {
		$tmp                = \realpath( \sys_get_temp_dir() ) ?: \sys_get_temp_dir();
		$this->project_dir  = $tmp . '/a8csp-configs-phpstan-' . \uniqid();
		$this->original_cwd = \getcwd() ?: '/';
		\mkdir( $this->project_dir );
		\chdir( $this->project_dir );
	}

	protected function tearDown(): void {
		\chdir( $this->original_cwd );
		$this->rrmdir( $this->project_dir );
	}

	#[Test]
	public function scans_scoped_dependencies_directory_when_present(): void {
		\mkdir( $this->project_dir . '/dependencies' );

		$config = require self::CONFIG_FILE;

		self::assertSame(
			array( $this->project_dir . '/dependencies' ),
			$config['parameters']['scanDirectories']
		);
	}

	#[Test]
	public function omits_scan_directories_when_no_dependencies_directory_exists(): void {
		\mkdir( $this->project_dir . '/src' );

		$config = require self::CONFIG_FILE;

		self::assertArrayNotHasKey( 'scanDirectories', $config['parameters'] );
	}

	#[Test]
	public function detects_conventional_root_files(): void {
		\file_put_contents( $this->project_dir . '/functions-bootstrap.php', "<?php\n" );
		\file_put_contents( $this->project_dir . '/functions.php', "<?php\n" );

		$config = require self::CONFIG_FILE;

		self::assertSame(
			array(
				$this->project_dir . '/functions-bootstrap.php',
				$this->project_dir . '/functions.php',
			),
			$config['parameters']['paths']
		);
	}

	#[Test]
	public function detects_each_conventional_source_directory(): void {
		foreach ( array( 'src', 'includes', 'models', 'blocks', 'templates' ) as $directory ) {
			\mkdir( $this->project_dir . '/' . $directory );
		}

		$config = require self::CONFIG_FILE;

		self::assertSame(
			array(
				$this->project_dir . '/src',
				$this->project_dir . '/includes',
				$this->project_dir . '/models',
				$this->project_dir . '/blocks',
				$this->project_dir . '/templates',
			),
			$config['parameters']['paths']
		);
	}

	#[Test]
	public function ignores_unconventional_files_and_directories(): void {
		\file_put_contents( $this->project_dir . '/random.php', "<?php\n" );
		\mkdir( $this->project_dir . '/lib' );

		$config = require self::CONFIG_FILE;

		self::assertSame( array(), $config );
	}

	#[Test]
	public function yields_an_empty_config_for_an_empty_project(): void {
		$config = require self::CONFIG_FILE;

		self::assertSame( array(), $config );
	}

	private function rrmdir( string $dir ): void {
		if ( ! \is_dir( $dir ) ) {
			return;
		}
		foreach ( \scandir( $dir ) ?: array() as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}
			$path = $dir . '/' . $entry;
			if ( \is_dir( $path ) ) {
				$this->rrmdir( $path );
			} else {
				\unlink( $path );
			}
		}
		\rmdir( $dir );
	}
}
