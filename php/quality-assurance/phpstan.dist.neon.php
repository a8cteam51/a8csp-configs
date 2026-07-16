<?php declare( strict_types=1 );

$config = array();
$workingDirectory = getcwd();

foreach ( array( 'dependencies' ) as $discoverDirectory ) {
	if ( is_dir( $workingDirectory . '/' . $discoverDirectory ) ) {
		$config['parameters']['scanDirectories'][] = $workingDirectory . '/' . $discoverDirectory;
	}
}

foreach ( array( 'functions-bootstrap.php', 'functions.php', 'uninstall.php' ) as $analyzeFile ) {
	if ( is_file( $workingDirectory . '/' . $analyzeFile ) ) {
		$config['parameters']['paths'][] = $workingDirectory . '/' . $analyzeFile;
	}
}
foreach ( array( 'src', 'includes', 'models', 'blocks', 'templates' ) as $analyzeDirectory ) {
	if ( is_dir( $workingDirectory . '/' . $analyzeDirectory ) ) {
		$config['parameters']['paths'][] = $workingDirectory . '/' . $analyzeDirectory;
	}
}

return $config;
