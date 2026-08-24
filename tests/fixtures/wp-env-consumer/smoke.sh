#!/bin/sh
# The composer script reusable-phpunit.yml runs against this fixture. It asserts the two things the
# workflow's wp-env steps are responsible for, and nothing else: that the environment came up on the
# WordPress the caller asked for, and that it serves this project's tree.
#
# Both assertions run inside the container and are read through the exit status. Nothing greps
# wp-env's own output, which narrates the command it is about to run and therefore matches any
# pattern taken from that command.

set -eu
cd -- "$(dirname -- "$0")"

# Kept level with the `wp-version` input quality.yml passes; the pair is the point of the assertion.
EXPECTED_WP=6.9.4

# The project's own binary, which is the whole subject of the coverage: the workflow starts this one.
WP_ENV=./node_modules/.bin/wp-env

# Compared whole rather than matched as a prefix: `6.9.41` and `6.9.4-RC1` are not this version.
if ! $WP_ENV run cli sh -c "[ \"\$(wp core version)\" = '${EXPECTED_WP}' ]"; then
	echo "smoke: the environment is not running WordPress ${EXPECTED_WP} — the wp-version the workflow passed did not reach the container. It reports:" >&2
	$WP_ENV run cli wp core version >&2 || true
	exit 1
fi

if ! $WP_ENV run cli test -f /var/www/html/wp-content/mu-plugins/wp-env-consumer/marker.php; then
	echo "smoke: the container does not serve this project's mapping — it answered, but not for this tree." >&2
	exit 1
fi

echo "smoke: WordPress ${EXPECTED_WP} is running and the project's mapping is served."
