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
namespace OCA\ActivityDefaults\Tests;

use OCA\ActivityDefaults\AppSettings;
use OCP\Activity\IExtension;

/**
 * @group DB
 */
class AppSettingsTest extends TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $manager;

    protected $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $data;

    /** @var \OCA\ActivityDefaults\AppSettings */
    protected $settings;

    protected function setUp()
    {
        parent::setUp();
        
        $this->manager = $this->createMock('OCP\Activity\IManager');
        $this->config = \OC::$server->getConfig();
        $this->data = $this->getMockBuilder('OCA\Activity\Data')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->settings = new AppSettings(
        	\OC::$server->getActivityManager(),
			\OC::$server->getConfig()
		);
    }
    
    public function defaultData()
    {
        return array(
            array('setting', 'batchtime', self::HOURLY),
            array('setting', 'self', true),
            array('setting', 'selfemail', false),
            array(IExtension::METHOD_MAIL, 'mail_default', false),
            array(IExtension::METHOD_MAIL, 'stream_default', false)
        );
    }
    
    /**
     * @dataProvider defaultData
     * 
     * @param string $method
     * @param string $type
     * @param mixed $expectation
     */
    public function testGetDefaultSetting($method, $type, $expectation)
    {
        $this->manager->expects($this->any())->method('getDefaultTypes')->willReturnMap(array(
            array(IExtension::METHOD_MAIL, array('mail_default'))
        ));
        
        $this->assertEquals($expectation, $this->settings->getDefaultSetting($method, $type));
    }

	/**
	 * @dataProvider defaultData
	 * @param $method
	 * @param $type
	 */
    public function testGetDefaultAppSetting($method, $type) {
    	$this->assertEquals(
    		$this->settings->getDefaultSetting($method, $type),
			$this->settings->getAppSetting($method, $type)
		);
	}

	public function testGetCustomAppSetting() {
		$this->c->setAppValue('activitydefaults', 'notify_setting_selfemail', true);
		$this->assertTrue($this->settings->getAppSetting('setting', 'selfemail'));
	}

}