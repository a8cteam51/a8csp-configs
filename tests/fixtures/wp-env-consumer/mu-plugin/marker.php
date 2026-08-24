<?php
/**
 * Mapped into the container by .wp-env.json so smoke.sh can prove the environment serves this
 * project's tree, not merely that a container answers. Never loaded: WordPress reads mu-plugins one
 * level up, and a mapped directory's files are not autoloaded.
 */

die( 'This fixture file is a mapping marker and is not meant to run.' );
