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

use OC\L10N\L10N;
use OCA\ActivityDefaults\AppSettings;
use OCA\ActivityDefaults\Controller\SettingsController;
use OCA\ActivityDefaults\Tests\TestCase;
use OCP\Activity\IExtension;
use OCP\Activity\IManager;
use OCP\Activity\ISetting;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;
use OCP\Util;

/**
 * @group DB
 */
class SettingsControllerTest extends TestCase {

    /** @var \PHPUnit_Framework_MockObject_MockObject|IRequest */
	protected $request;

	/** @var L10N */
	protected $l10n;

    /** @var \PHPUnit_Framework_MockObject_MockObject|AppSettings */
	protected $appSettings;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|IManager */
    protected $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|IConfig */
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
        $settings = [$testType => 'A simple notification type'];

        $this->manager->expects($this->any())
            ->method('getNotificationTypes')
            ->willReturn($settings);

        $this->manager
            ->expects($this->once())
            ->method('getSettings')
            ->willReturn($settings);

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

	    $this->manager
            ->expects($this->any())
            ->method('getSettings')
            ->willReturn([
                $this->createSetting('simple_desc', 'A simple notification type'),
                $this->createSetting('stream_only', 'A stream-only notification type', false),
                $this->createSetting('email_only', 'An email-only notification type', true, false),
                $this->createSetting('missing_desc', '')
            ]);

        $testParams = array(
            'email' => array(
                'simple_desc' => false,
                'stream_only' => false,
                'email_only' => true,
                'missing_desc' => false
            ),
            'stream' => array(
                'simple_desc' => true,
                'stream_only' => true,
                'email_only' => false,
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

    /**
     * @param string $identifier
     * @param string $name
     * @param bool $canChangeStream
     * @param bool $canChangeMail
     * @return \PHPUnit_Framework_MockObject_MockObject|ISetting
     */
    protected function createSetting(
        $identifier,
        $name,
        $canChangeStream = true,
        $canChangeMail = true) {

        $setting = $this->createMock(ISetting::class);
        $setting
            ->expects($this->any())
            ->method('getIdentifier')
            ->willReturn($identifier);
        $setting
            ->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $setting
            ->expects($this->any())
            ->method('getPriority')
            ->willReturn(50);
        $setting
            ->expects($this->any())
            ->method('canChangeStream')
            ->willReturn((bool) $canChangeStream);
        $setting
            ->expects($this->any())
            ->method('canChangeMail')
            ->willReturn((bool) $canChangeMail);

        return $setting;
    }

	protected function assertTemplateResponse(TemplateResponse $response) {
		// Validate template construction
		$this->assertInstanceOf('OCP\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('blank', $response->getRenderAs());
		$this->assertEquals('settings-admin', $response->getTemplateName());

        $settings = $this->manager->getSettings();

		// Validate parameter construction
		$params = $response->getParams();
		$this->assertEquals(5, count($params));
		$this->assertArrayHasKey('activities', $params);
		$activities = $params['activities'];
        $this->assertEquals(count($settings), count($activities));
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
        foreach ($settings as $setting) {
            $this->assertArrayHasKey($setting->getIdentifier(), $activities);
            $activity = $activities[$setting->getIdentifier()];
            $desc = $setting->getName();
            $methods = [];
            if ($setting->canChangeStream()) {
                $methods[] = IExtension::METHOD_STREAM;
            }
            if ($setting->canChangeMail()) {
                $methods[] = IExtension::METHOD_MAIL;
            }
            $this->assertEquals($desc, $activity['desc']);
            $this->assertEquals($appSettings->getAppSetting('email', $setting->getIdentifier()), $activity[IExtension::METHOD_MAIL]);
            $this->assertEquals($appSettings->getAppSetting('stream', $setting->getIdentifier()),
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

        $this->request = $this->createMock(IRequest::class);
        $this->config = $this->createMock(IConfig::class);
        $this->manager = $this->createMock(IManager::class);
        $this->appSettings = $this->createMock(AppSettings::class);

        $this->l10n = Util::getL10N('activity', 'en');

        /** @var \PHPUnit_Framework_MockObject_MockObject|ISecureRandom $iSecureRandom */
        $iSecureRandom = $this->createMock(ISecureRandom::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject|IURLGenerator $iUrlGenerator */
        $iUrlGenerator = $this->createMock(IURLGenerator::class);

        $this->controller = new SettingsController(
            'activitydefaults',
            $this->request,
            $this->config,
            $iSecureRandom,
            $iUrlGenerator,
            $this->manager,
            $this->appSettings,
            $this->l10n
        );
    }
}
