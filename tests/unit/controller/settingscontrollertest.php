<?php

/**
 * ownCloud - ActivityDefauls App
 *
 * @author Matt Leonhardt
 * @copyright 2016 Matt Leonhardt mleonhardt@mpr.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\ActivityDefaults\Tests\Controller;

use OC\AllConfig;
use OC\AppFramework\Http\Request;
use OC\L10N\L10N;
use OCA\Activity\Data;
use OCA\ActivityDefaults\AppSettings;
use OCA\ActivityDefaults\Controller\SettingsController;
use OCA\ActivityDefaults\Tests\TestCase;
use OCP\Activity\IExtension;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Util;

/**
 * @group DB
 */
class SettingsControllerTest extends TestCase {

	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $request;

	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $data;

	/** @var L10N */
	protected $l10n;

	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $appSettings;

	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $config;

	/** @var SettingsController */
	protected $controller;

	public function adminData() {
		return array(
			array(
				self::HOURLY,
				false,
				false
			),
			array(
				self::DAILY,
				true,
				false
			),
			array(
				self::WEEKLY,
				false,
				true
			)
		);
	}

	/**
	 * @dataProvider adminData
	 *
	 * @param int $batchtime
	 * @param bool $self
	 * @param bool $selfemail
	 */
	public function testAdmin($batchtime, $self, $selfemail) {
		$testType = 'simple_desc';

		$this->data->expects($this->any())
			->method('getNotificationTypes')
			->willReturn(array(
				$testType => 'A simple notification type'
			));

		$this->request->expects($this->any())
			->method('getParam')
			->willReturnMap(
				array(
					array(
						"{$testType}_email",
						false,
						true
					),
					array(
						"{$testType}_stream",
						false,
						false
					)
				));

		$this->config->expects($this->exactly(5))
			->method('setAppValue');
		$this->config->expects($this->at(0))
			->method('setAppValue')
			->with('activitydefaults', "notify_email_{$testType}", true);
		$this->config->expects($this->at(1))
			->method('setAppValue')
			->with('activitydefaults', "notify_stream_{$testType}", false);
		$this->config->expects($this->at(2))
			->method('setAppValue')
			->with('activitydefaults', 'notify_setting_batchtime', $batchtime);
		$this->config->expects($this->at(3))
			->method('setAppValue')
			->with('activitydefaults', 'notify_setting_self', $self);
		$this->config->expects($this->at(4))
			->method('setAppValue')
			->with('activitydefaults', 'notify_setting_selfemail', $selfemail);

		$this->assertDataResponse($this->controller->admin($this->batchtimes[$batchtime], $self, $selfemail));
	}

	/**
	 * @param DataResponse $response
	 */
	protected function assertDataResponse(DataResponse $response) {
		$this->assertInstanceOf('OCP\AppFramework\Http\DataResponse', $response);
		$responseData = $response->getData();
		$this->assertEquals(1, count($responseData));
		$this->assertArrayHasKey('data', $responseData);
		$data = $responseData['data'];
		$this->assertEquals(1, sizeof($data));
		$this->assertArrayHasKey('message', $data);
		$this->assertEquals('Application settings have been updated.', $data['message']);
	}

	public function indexData() {
		return array(
			array(
				3600
			),
			array(
				3600 * 24
			),
			array(
				3600 * 24 * 7
			)
		);
	}

	/**
	 * @dataProvider indexData
	 *
	 * @param int $batchtime
	 */
	public function testIndex($batchtime) {
		$this->data->expects($this->any())
			->method('getNotificationTypes')
			->willReturn(
				array(
					'simple_desc' => 'A simple notification type',
					'stream_only' => array(
						'desc' => 'A stream-only notification type',
						'methods' => array(
							IExtension::METHOD_STREAM
						)
					),
					'nested_desc' => array(
						'desc' => 'A nested notification type'
					),
					'missing_desc' => array(
						'methods' => array(
							IExtension::METHOD_MAIL,
							IExtension::METHOD_STREAM
						)
					)
				));

		$testParams = array(
			'email' => array(
				'simple_desc' => false,
				'stream_only' => false,
				'nested_desc' => true,
				'missing_desc' => false
			),
			'stream' => array(
				'simple_desc' => true,
				'stream_only' => true,
				'nested_desc' => true,
				'missing_desc' => false
			),
			'setting' => array(
				'batchtime' => $batchtime,
				'self' => false,
				'selfemail' => false
			)
		);

		// Hourly
		$this->appSettings->expects($this->any())
			->method('getAppSetting')
			->willReturnCallback(
				function ($type, $name) use ($testParams) {
					return $testParams[$type][$name];
				});
		$this->assertTemplateResponse($this->controller->index());
	}

	protected function assertTemplateResponse(TemplateResponse $response) {
		// Validate template construction
		$this->assertInstanceOf('OCP\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('blank', $response->getRenderAs());
		$this->assertEquals('settings-admin', $response->getTemplateName());

		/** @var Data $data */
		$data = $this->data;
		$types = $data->getNotificationTypes($this->l10n);

		// Validate parameter construction
		$params = $response->getParams();
		$this->assertEquals(5, count($params));
		$this->assertArrayHasKey('activities', $params);
		$activities = $params['activities'];
		$this->assertEquals(count($types), count($activities));
		foreach ($activities as $activity) {
			$this->assertArrayHasKey('desc', $activity);
			$this->assertArrayHasKey(IExtension::METHOD_MAIL, $activity);
			$this->assertArrayHasKey(IExtension::METHOD_STREAM, $activity);
			$this->assertArrayHasKey('methods', $activity);
		}

		$this->assertArrayHasKey('notify_self', $params);
		$this->assertArrayHasKey('notify_selfemail', $params);
		$this->assertArrayHasKey('setting_batchtime', $params);
		$this->assertArrayHasKey('methods', $params);
		$this->assertArrayHasKey(IExtension::METHOD_MAIL, $params['methods']);
		$this->assertArrayHasKey(IExtension::METHOD_STREAM, $params['methods']);

		// Validate data
		/** @var AppSettings $appSettings */
		$appSettings = $this->appSettings;
		foreach ($types as $type => $typeData) {
			$this->assertArrayHasKey($type, $activities);
			$activity = $activities[$type];
			$desc = is_array($typeData) ? (isset($typeData['desc']) ? $typeData['desc'] : '') : $typeData;
			$methods = is_array($typeData) && isset($typeData['methods']) ? $typeData['methods'] : [
				IExtension::METHOD_STREAM,
				IExtension::METHOD_MAIL
			];
			$this->assertEquals($desc, $activity['desc']);
			$this->assertEquals($appSettings->getAppSetting('email', $type), $activity[IExtension::METHOD_MAIL]);
			$this->assertEquals($appSettings->getAppSetting('stream', $type),
				$activity[IExtension::METHOD_STREAM]);
			$this->assertEquals($methods, $activity['methods']);
		}

		$this->assertEquals($appSettings->getAppSetting('setting', 'self'), $params['notify_self']);
		$this->assertEquals($appSettings->getAppSetting('setting', 'selfemail'), $params['notify_selfemail']);
		$this->assertEquals($this->batchtimes[$appSettings->getAppSetting('setting', 'batchtime')],
			$params['setting_batchtime']);
	}

	protected function setUp() {
		parent::setUp();

		$this->request = $this->getMockBuilder(Request::class)
			->disableOriginalConstructor()
			->getMock();

		$this->data = $this->getMockBuilder(Data::class)
			->disableOriginalConstructor()
			->getMock();

		$this->l10n = Util::getL10N('activity', 'en');

		$this->appSettings = $this->getMockBuilder(AppSettings::class)
			->disableOriginalConstructor()
			->getMock();

		$this->config = $this->getMockBuilder(AllConfig::class)
			->disableOriginalConstructor()
			->getMock();

		$this->controller = new SettingsController('activitydefaults', $this->request, $this->data, $this->l10n,
			$this->appSettings, $this->config);

	}
}
