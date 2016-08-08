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

use OC\AllConfig;
use OCA\Activity\UserSettings;

abstract class TestCase extends \Test\TestCase {

	const HOURLY = 3600;
	const DAILY = 3600 * 24;
	const WEEKLY = 3600 * 24 * 7;
	/** @var array */
	protected $batchtimes;
	/** @var AllConfig */
	protected $c;

	public function tearDown() {
		parent::tearDown();
		$this->c->deleteAllUserValues('testuser');
		$this->c->deleteAppValues('activitydefaults');
	}

	protected function setUp() {
		$this->batchtimes = array(
			self::HOURLY => UserSettings::EMAIL_SEND_HOURLY,
			self::DAILY => UserSettings::EMAIL_SEND_DAILY,
			self::WEEKLY => UserSettings::EMAIL_SEND_WEEKLY
		);
		$this->c = \OC::$server->getConfig();
	}

}