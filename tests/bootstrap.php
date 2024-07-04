<?php

use Symfony\Component\Dotenv\Dotenv;
use Psr\Log\LoggerInterface;

require_once dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require_once dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
// create up-to-date TEST database before all tests start
passthru('php bin/console doctrine:database:drop --env=test --force --if-exists');
passthru('php bin/console doctrine:database:create --env=test');
passthru('php bin/console doctrine:schema:create --env=test');

// remove TEST database after all tests end
register_shutdown_function(function() {
   // passthru('php bin/console doctrine:database:drop --env=test --force --if-exists');
});
