<?php

/**
 * ownCloud - ActivityDefauls App
 *
 * @author Matt Leonhardt
 * @copyright 2016 Matt Leonhardt mleonhardt@mpr.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace OCA\ActivityDefaults\AppInfo;

use OCA\ActivityDefaults\AppHooks;
use OCA\ActivityDefaults\AppSettings;
use OCA\ActivityDefaults\Controller\SettingsController;
use OCP\AppFramework\App;
use OCP\IContainer;

class Application extends App
{

    /**
     *
     * @param array $urlParams            
     */
    public function __construct(array $urlParams = array())
    {
        parent::__construct('activitydefaults', $urlParams);
        $container = $this->getContainer();
        
        /**
         * TODO: Is there a way to inject the Activity Application? *
         */
        $activity = new \OCA\Activity\AppInfo\Application();
        $aContainer = $activity->getContainer();
        
        $container->registerParameter('ActivityData', $aContainer->query('ActivityData'));

        /**
         * Services
         */
        $container->registerService('AppSettings', 
            function (IContainer $c) {
                return new AppSettings(\OC::$server->getActivityManager(), \OC::$server->getConfig(), 
                    $c->query('ActivityData'));
            });
        
        /**
         * Controller
         */
        $container->registerService('SettingsController', 
            function (IContainer $c) {
                return new SettingsController($c->query('AppName'), $c->query('Request'), $c->query('ActivityData'), 
                    \OC::$server->getL10N($c->query('AppName')), $c->query('AppSettings'), \OC::$server->getConfig());
            });
        
        $container->registerService('AppHooks', 
            function () {
                return new AppHooks(\OC::$server->getConfig());
            });
    }
}