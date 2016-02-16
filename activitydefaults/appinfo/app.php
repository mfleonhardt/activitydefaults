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
namespace OCA\ActivityDefaults\AppInfo;

$app = new Application();
$c = $app->getContainer();

// Register new user hook
\OCP\Util::connectHook('OC_User', 'post_createUser', 'OCA\ActivityDefaults\AppHooks', 'createUser');

// Register admin page template
\OCP\App::registerAdmin($c->getAppName(), 'settings-admin');