<?php
/**
 * ownCloud - activitydefaults
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Matt Leonhardt <mleonhardt@mpr.com>
 * @copyright Matt Leonhardt 2016
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\ActivityDefaults\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
namespace OCA\ActivityDefaults\AppInfo;

//\OCP\Util::logException('activitydefaults', new \Exception(\OCP\Util::getRequestUri()));

$application = new Application();
$application->registerRoutes($this, ['routes' => [
	['name' => 'Settings#admin', 'url' => '/settings-admin', 'verb' => 'POST'],
]]);
