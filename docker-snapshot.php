<?php

if (!\file_exists('docker-snapshot')) {
    \mkDir('docker-snapshot');
}
\file_put_contents(
    'docker-snapshot/production.yaml',
    `docker stack config -c swarm.yaml`,
);
