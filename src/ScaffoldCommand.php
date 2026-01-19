<?php
namespace TN\WPUnitScaffold;

class ScaffoldCommand
{
	private $projectRoot;
	private $templatesDir;
	private $wpRoot;

	public function __construct()
	{
		// Project root is where composer.json is (where vendor/ is installed)
		$this->projectRoot  = dirname( __DIR__, 4 );
		$this->templatesDir = dirname( __DIR__ ) . '/templates';
		
		// Find WordPress root
		$this->wpRoot = $this->findWordPressRoot();
		
		if ( ! $this->wpRoot ) {
			echo "❌ Error: WordPress installation not found.\n";
			echo "   Could not locate wp-load.php in parent directories.\n";
			echo "   Please run this command from within a WordPress installation.\n\n";
			exit( 1 );
		}
	}

	public function run()
	{
		echo "🚀 TN WordPress Unit Tests Scaffold\n\n";
		echo "📍 WordPress root detected: " . $this->wpRoot . "\n";
		echo "📍 Project root: " . $this->projectRoot . "\n";

		if ( $this->filesExist() ) {
			echo "⚠️  Files already exist. Overwrite? (y/n): ";

			$handle = fopen( "php://stdin", "r" );
			$line   = fgets( $handle );
			if ( trim( $line ) !== 'y' ) {
				echo "❌ Aborted.\n";
				return;
			}
			fclose( $handle );
		}

		if ( ! $this->createDirectory( $this->projectRoot . '/tests' ) ) {
			echo "\n❌ Setup failed: Could not create tests directory.\n";
			exit( 1 );
		}

		$files = array(
			'bootstrap.php'              => '/tests/bootstrap.php',
			'phpunit.xml'                => '/phpunit.xml',
			'WordPressTest.php'          => '/tests/WordPressTest.php',
			'wp-tests-config-sample.php' => '/tests/wp-tests-config-sample.php'
		);

		$hasErrors = false;
		foreach ( $files as $template => $destination ) {
			if ( ! $this->copyFile( $template, $destination ) ) {
				$hasErrors = true;
			}
		}
		
		if ( $hasErrors ) {
			echo "\n⚠️ Setup completed with errors. Please check the messages above.\n";
			exit( 1 );
		} else {
			echo "\n✅ Setup complete!\n";
		}
		
		echo "\nNext steps:\n";
		echo "  1. Run: composer require --dev phpunit/phpunit (if not yet installed)\n";
		echo "  2. Run: composer require --dev wp-phpunit/wp-phpunit (if not yet installed)\n";
		echo "  3. Run: composer require --dev yoast/phpunit-polyfills:\"^2.0\" (if not yet installed)\n";
		echo "  ➡️ 4. After completing the setup in phpunit.xml and wp-tests-config-sample.php run: vendor/bin/phpunit to check if testing works.\n\n";
		echo "  ➡️ 5. Consider adding testing files and folders to .gitignore.\n\n";
	}

	/**
	 * Find WordPress root by looking for wp-load.php
	 */
	private function findWordPressRoot()
	{
		$currentDir = $this->projectRoot;
		$maxLevels  = 10; // Safety limit
		
		for ( $i = 0; $i < $maxLevels; $i++ ) {
			if ( file_exists( $currentDir . '/wp-load.php' ) ) {
				return realpath( $currentDir );
			}
			
			$parentDir = dirname( $currentDir );
			
			// Reached filesystem root
			if ( $parentDir === $currentDir ) {
				break;
			}
			
			$currentDir = $parentDir;
		}
		
		return false;
	}

	private function filesExist()
	{
		return 	file_exists( $this->projectRoot . '/tests/wp-tests-config-sample.php' ) ||
				file_exists( $this->projectRoot . '/tests/bootstrap.php' ) ||
				file_exists( $this->projectRoot . '/tests/WordPressTest.php' ) ||
				file_exists( $this->projectRoot . '/phpunit.xml' );
	}

	private function createDirectory( $path )
	{
		if ( ! is_dir( $path ) ) {
			if ( ! mkdir( $path, 0755, true ) ) {
				echo "❌ Failed to create directory: " . basename( $path ) . "/\n";
				return false;
			}
			echo "📁 Created: " . basename( $path ) . "/\n";
		}
		return true;
	}

	private function copyFile( $template, $destination )
	{
		$source = $this->templatesDir . '/' . $template;
		$dest   = $this->projectRoot . $destination;

		if ( ! file_exists( $source ) ) {
			echo "❌ Template not found: " . $template . "\n";
			return false;
		}

		if ( copy( $source, $dest ) ) {
			echo "✅ Created: " . $destination . "\n";
			return true;
		} else {
			echo "❌ Failed to copy: " . $destination . "\n";
			return false;
		}
	}
}