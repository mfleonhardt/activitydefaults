<?php

/**
 * ownCloud - ActivityDefaults App
 *
 * @author Matt Leonhardt
 * @copyright 2016 Matt Leonhardt mleonhardt@mpr.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\ActivityDefaults\Tests\AppInfo;

use OCA\ActivityDefaults\AppInfo\Application;
use OCA\ActivityDefaults\Tests\TestCase;
use OCP\AppFramework\IAppContainer;

/**
 * @group DB
 */
class ApplicationTest extends TestCase
{

    /** @var \OCA\ActivityDefaults\AppInfo\Application **/
    protected $app;

	/** @var  IAppContainer */
    protected $container;

    protected function setUp()
    {
        parent::setUp();
        $this->app = new Application();
        $this->container = $this->app->getContainer();
    }

    public function testContainerAppName()
    {
        $this->assertEquals('activitydefaults', $this->container->getAppName());
    }

    public function queryData()
    {
        return array(
            array(
                'AppSettings',
                'OCA\ActivityDefaults\AppSettings'
            ),
            array(
                'SettingsController',
                'OCA\ActivityDefaults\Controller\SettingsController'
            ),
            array(
                'AppHooks',
                'OCA\ActivityDefaults\AppHooks'
            )
        );
    }

    /**
     * @dataProvider queryData
     * 
     * @param string $service            
     * @param string $expected            
     */
    public function testContainerQuery($service, $expected)
    {
        $this->assertTrue($this->container->query($service) instanceof $expected);
    }
}
