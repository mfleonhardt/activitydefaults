<?php
namespace OCA\ActivityDefaults;

class AppHooks
{

    public static function createUser($user)
    {
        $c = \OC::$server->getConfig();
        foreach ($c->getAppKeys('activitydefaults') as $key) {
            $c->setUserValue($user['uid'], 'activity', $key, $c->getAppValue('activitydefaults', $key));
        }
    }
}