<?php

return [
    'dsn' => env('SENTRY_DSN', docker_secret('SENTRY_DSN')),

    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,
];
