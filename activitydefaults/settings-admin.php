<?php

namespace OCA\ActivityDefaults;

use \OCA\ActivityDefaults\AppInfo\Application;

$app = new Application();
$container = $app->getContainer();
return $container->query('\OCA\ActivityDefaults\Controller\SettingsController')->index()->render();
