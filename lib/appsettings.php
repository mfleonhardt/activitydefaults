<?php

/**
 * ownCloud - Activity Defaults App
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
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\ActivityDefaults;

use OCP\Activity\IManager;
use OCP\IConfig;

/**
 * Class UserSettings
 *
 * @package OCA\Activity
 */
class AppSettings
{

    /** @var IManager */
    protected $manager;

    /** @var IConfig */
    protected $config;

    const EMAIL_SEND_HOURLY = 0;

    const EMAIL_SEND_DAILY = 1;

    const EMAIL_SEND_WEEKLY = 2;

    /**
     *
     * @param IManager $manager
     * @param IConfig $config
     */
    public function __construct(IManager $manager, IConfig $config) {
        $this->manager = $manager;
        $this->config = $config;
    }

	/**
	 * Get a setting for a user
	 *
	 * Falls back to some good default values if the user does not have a preference
	 *
	 * @param string $method
	 *            Should be one of 'stream', 'email' or 'setting'
	 * @param string $type
	 *            One of the activity types, 'batchtime' or 'self'
	 * @return mixed
	 */
    public function getAppSetting($method, $type)
    {
        return $this->config->getAppValue('activitydefaults', 'notify_' . $method . '_' . $type,
            $this->getDefaultSetting($method, $type));
    }

    /**
     * Get a good default setting for a preference
     *
     * @param string $method
     *            Should be one of 'stream', 'email' or 'setting'
     * @param string $type
     *            One of the activity types, 'batchtime', 'self' or 'selfemail'
     * @return bool|int
     */
    public function getDefaultSetting($method, $type)
    {
        $default = false;
        if ($method === 'setting') {
            switch ($type) {
                case 'batchtime':
                    $default = 3600;
                    break;
                case 'self':
                    $default = true;
                    break;
            }
        } else {
            $settings = $this->manager->getDefaultTypes($method);
            $default = in_array($type, $settings);
        }
        return $default;
    }
}
