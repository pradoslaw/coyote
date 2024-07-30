<?php

if (!\file_exists('docker-snapshot')) {
    \mkDir('docker-snapshot');
}
\file_put_contents(
    'docker-snapshot/production.yaml',
    `docker stack config -c docker-swarm.yaml -c docker-swarm.production.yaml`,
);
\file_put_contents(
    'docker-snapshot/staging.yaml',
    `docker stack config -c docker-swarm.yaml -c docker-swarm.staging.yaml`,
);
\file_put_contents(
    'docker-snapshot/local.yaml',
    `docker stack config -c docker-swarm.yaml -c docker-swarm.local.yaml`,
);
