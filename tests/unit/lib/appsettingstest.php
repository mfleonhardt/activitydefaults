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
use OCP\DB\QueryBuilder\IExpressionBuilder;

/**
 * @group DB
 */
class AppSettingsTest extends TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $data;

    /** @var \OCA\ActivityDefaults\AppSettings */
    protected $settings;

    protected function setUp()
    {
        parent::setUp();
        
        $this->manager = $this->getMock('OCP\Activity\IManager');
        $this->config = $this->getMock('OCP\IConfig');
        $this->data = $this->getMockBuilder('OCA\Activity\Data')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->settings = new AppSettings($this->manager, $this->config, $this->data);
    }
    
    public function defaultData()
    {
        return array(
            array('setting', 'batchtime', self::HOURLY),
            array('setting', 'self', true),
            array('setting', 'selfemail', false),
            array(IExtension::METHOD_MAIL, 'mail_default', true),
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
}