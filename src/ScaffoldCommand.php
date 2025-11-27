<?php
namespace TN\WPUnitScaffold;

class ScaffoldCommand
{
    private $projectRoot;
    private $templatesDir;
    private $wpRoot;
    private $levelsToWpRoot;

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
        
        // Calculate how many levels up from tests/ to WordPress root
        $this->levelsToWpRoot = $this->calculateLevelsToWpRoot();
    }

    public function run()
    {
        echo "🚀 TN WordPress Unit Tests Scaffold\n\n";
        echo "📍 WordPress root detected: " . $this->wpRoot . "\n";
        echo "📍 Project root: " . $this->projectRoot . "\n";
        echo "📍 Levels to WP root from tests/: " . $this->levelsToWpRoot . "\n\n";

        if ( $this->filesExist() ) {
            echo "⚠️  Files already exist. Overwrite? (y/n): ";

            $handle = fopen( "php://stdin", "r" );
            $line   = fgets( $handle );
            if ( trim( $line ) !== 'y' ) {
                echo "❌ Aborted.\n";
                return;
            }
        }

        $this->createDirectory( $this->projectRoot . '/tests' );

        $files = array(
            'WordPressTest.php' => '/tests/WordPressTest.php',
            'phpunit.xml'       => '/phpunit.xml'
        );

        foreach ( $files as $template => $destination ) {
            $this->copyFile( $template, $destination );
        }
        
        // Create bootstrap.php with dynamic path
        $this->createBootstrap();

        echo "\n✅ Setup complete!\n";
        echo "\nNext steps:\n";
        echo "  1. Run: composer require --dev phpunit/phpunit (if not installed)\n";
        echo "  2. Run: vendor/bin/phpunit\n\n";
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

    /**
     * Calculate how many dirname() levels needed from tests/ to WordPress root
     */
    private function calculateLevelsToWpRoot()
    {
        $testsDir = $this->projectRoot . '/tests';
        $wpRoot   = $this->wpRoot;
        
        // Normalize paths
        $testsDir = str_replace( '\\', '/', realpath( $testsDir ) ?: $testsDir );
        $wpRoot   = str_replace( '\\', '/', $wpRoot );
        
        // Count directory levels between tests/ and WP root
        $testsParts = explode( '/', $testsDir );
        $wpParts    = explode( '/', $wpRoot );
        
        // Find common base
        $commonLength = 0;
        $minLength    = min( count( $testsParts ), count( $wpParts ) );
        
        for ( $i = 0; $i < $minLength; $i++ ) {
            if ( $testsParts[ $i ] === $wpParts[ $i ] ) {
                $commonLength++;
            } else {
                break;
            }
        }
        
        // Levels up from tests/ to common base, then to WP root
        $levelsUp = count( $testsParts ) - $commonLength;
        
        // We need to go up one more level because we're inside tests/ directory
        // and dirname(__DIR__) goes to project root first
        return $levelsUp;
    }

    /**
     * Create bootstrap.php with correct path to wp-load.php
     */
    private function createBootstrap()
    {
        $destination = $this->projectRoot . '/tests/bootstrap.php';
        
        $content = "<?php\n\n";
        $content .= "require_once dirname(__DIR__, {$this->levelsToWpRoot}) . '/wp-load.php';\n";
        
        if ( file_put_contents( $destination, $content ) ) {
            echo "✅ Created: /tests/bootstrap.php (with {$this->levelsToWpRoot} levels to WP root)\n";
        } else {
            echo "❌ Failed: /tests/bootstrap.php\n";
        }
    }

    private function filesExist()
    {
        return file_exists( $this->projectRoot . '/tests/bootstrap.php' ) ||
               file_exists( $this->projectRoot . '/tests/WordPressTest.php' ) ||
               file_exists( $this->projectRoot . '/phpunit.xml' );
    }

    private function createDirectory( $path )
    {
        if ( ! is_dir( $path ) ) {
            mkdir( $path, 0755, true );
            echo "📁 Created: " . basename( $path ) . "/\n";
        }
    }

    private function copyFile( $template, $destination )
    {
        $source = $this->templatesDir . '/' . $template;
        $dest   = $this->projectRoot . $destination;

        if ( copy( $source, $dest ) ) {
            echo "✅ Created: " . $destination . "\n";
        } else {
            echo "❌ Failed: " . $destination . "\n";
        }
    }
}