<?php
/**
 * Created by PhpStorm.
 * User: mleonhardt
 * Date: 8/5/16
 * Time: 3:45 PM
 */

namespace OCA\ActivtyDefaults\Tests;

use OCA\ActivityDefaults\AppHooks;
use OCA\ActivityDefaults\Tests\TestCase;

class AppHooksTest extends TestCase {


	public function testCreateUser() {

		// Preset some default activity values
		$this->c->setAppValue('activitydefaults', 'key1', 'value1');
		$this->c->setAppValue('activitydefaults', 'key2', 'value2');

		// call the new user hook
		AppHooks::createUser(['uid' => 'testuser']);

		// The user's activity config should now have those keys and values
		$userActivityKeys = $this->c->getUserKeys('testuser', 'activity');
		$this->assertCount(2, $userActivityKeys);
		$this->assertContains('key1', $userActivityKeys);
		$this->assertContains('key2', $userActivityKeys);
		$this->assertEquals('value1', $this->c->getUserValue('testuser', 'activity', 'key1'));
		$this->assertEquals('value2', $this->c->getUserValue('testuser', 'activity', 'key2'));
	}
}
