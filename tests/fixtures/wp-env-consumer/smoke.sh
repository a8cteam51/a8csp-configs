#!/bin/sh
# reusable-phpunit.yml runs this against the fixture. It asserts only what the workflow's wp-env
# handling owns: the caller's WordPress, also after a consumer script starts wp-env again, and this
# project's mapping. Assertions run inside the container and read exit statuses, because wp-env's
# own output echoes the command it runs and would match any pattern taken from it.

set -eu
cd -- "$(dirname -- "$0")"

# Kept level with the `wp-version` input quality.yml passes; the pair is the point of the assertion.
EXPECTED_WP=6.9.4

# The workflow starts the project's own locked binary, so the smoke uses it too.
WP_ENV=./node_modules/.bin/wp-env

# A consumer's test script may start wp-env itself. That start sees only the job's environment, so
# the version check below also proves the caller's WordPress reached this step, not just the start step.
$WP_ENV start

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
