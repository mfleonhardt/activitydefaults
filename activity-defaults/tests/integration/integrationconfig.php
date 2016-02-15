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

use OCP\IConfig;

class IntegrationConfig implements IConfig
{

    /** @var array **/
    protected $config;

    public function __construct()
    {
        $this->config = [
            'system' => [],
            'apps' => [],
            'users' => []
        ];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \OCP\IConfig::setAppValue()
     */
    public function setAppValue($appName, $key, $value)
    {
        $this->config['apps'][$appName][$key] = $value;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \OCP\IConfig::getAppValue()
     */
    public function getAppValue($appName, $key, $default = '')
    {
        return (isset($this->config['apps'][$appName]) && isset($this->config['apps'][$appName][$key])) ? $this->config['apps'][$appName][$key] : $default;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \OCP\IConfig::getAppKeys()
     */
    public function getAppKeys($appName)
    {
        return isset($this->config['apps'][$appName]) ? array_keys($this->config['apps'][$appName]) : [];
    }

    /**
     * NOTE: Does not implement Preconditions.
     *
     * {@inheritDoc}
     *
     * @see \OCP\IConfig::setUserValue()
     */
    public function setUserValue($userId, $appName, $key, $value)
    {
        $this->config['users'][$appName][$key] = $value;
    }

    /* Unused methods */
    public function setSystemValues($configs)
    {}

    public function setSystemValue($key, $value)
    {}

    public function getSystemValue($key)
    {}

    public function getFilteredSystemValue($key)
    {}

    public function deleteSystemValue($key)
    {}

    public function deleteAppValue($appName, $key)
    {}

    public function deleteAppValues($appName)
    {}

    public function getUserValue($userId, $appName, $key)
    {}

    public function getUserValueForUsers($appName, $key, $userIds)
    {}

    public function getUserKeys($userId, $appName)
    {}

    public function deleteUserValue($userId, $appName, $key)
    {}

    public function deleteAllUserValues($userId)
    {}

    public function deleteAppFromAllUsers($appName)
    {}

    public function getUsersForUserValue($appName, $key, $value)
    {}
}