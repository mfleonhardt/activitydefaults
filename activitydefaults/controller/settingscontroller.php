<?php

/**
 * ownCloud - ActivityDetails App
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
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\ActivityDefaults\Controller;

use OCA\Activity\Data;
use OCP\Activity\IExtension;
use OCA\Activity\UserSettings;
use OCA\ActivityDefaults\AppSettings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends Controller
{

    protected $appName;

    protected $appSettings;

    protected $config;

    protected $data;

    protected $l10n;

    public function __construct($appName, IRequest $request, Data $data, IL10N $l10n, AppSettings $appSettings, IConfig $config)
    {
        parent::__construct($appName, $request);
        $this->appSettings = $appSettings;
        $this->config = $config;
        $this->data = $data;
        $this->l10n = $l10n;
    }

    /**
     * Handle post from admin settings
     *
     * @param int $notify_setting_batchtime            
     * @param bool $notify_setting_self            
     * @param bool $notify_setting_selfemail            
     * @return DataResponse
     */
    public function admin($notify_setting_batchtime = UserSettings::EMAIL_SEND_HOURLY, $notify_setting_self = false, $notify_setting_selfemail = false)
    {
        $types = $this->data->getNotificationTypes($this->l10n);
        
        foreach (array_keys($types) as $type) {
            $this->config->setAppValue($this->appName, 'notify_email_' . $type, $this->request->getParam($type . '_email', false));
            
            $this->config->setAppValue($this->appName, 'notify_stream_' . $type, $this->request->getParam($type . '_stream', false));
        }
        
        $email_batch_time = 3600;
        if ($notify_setting_batchtime === UserSettings::EMAIL_SEND_DAILY) {
            $email_batch_time = 3600 * 24;
        } elseif ($notify_setting_batchtime === UserSettings::EMAIL_SEND_WEEKLY) {
            $email_batch_time = 3600 * 24 * 7;
        }
        
        $this->config->setAppValue($this->appName, 'notify_setting_batchtime', $email_batch_time);
        $this->config->setAppValue($this->appName, 'notify_setting_self', $notify_setting_self);
        $this->config->setAppValue($this->appName, 'notify_setting_selfemail', $notify_setting_selfemail);
        
        return new DataResponse(array(
            'data' => array(
                'message' => (string) $this->l10n->t('Application settings have been updated.')
            )
        ));
    }

    /**
     * Build admin template response
     *
     * @return TemplateResponse
     */
    public function index()
    {
        $types = $this->data->getNotificationTypes($this->l10n);
        
        $activities = array();
        foreach ($types as $type => $desc) {
            if (is_array($desc)) {
                $methods = isset($desc['methods']) ? $desc['methods'] : [
                    IExtension::METHOD_STREAM,
                    IExtension::METHOD_MAIL
                ];
                $desc = isset($desc['desc']) ? $desc['desc'] : '';
            } else {
                $methods = [
                    IExtension::METHOD_STREAM,
                    IExtension::METHOD_MAIL
                ];
            }
            
            $activities[$type] = array(
                'desc' => $desc,
                IExtension::METHOD_MAIL => $this->appSettings->getAppSetting('email', $type),
                IExtension::METHOD_STREAM => $this->appSettings->getAppSetting('stream', $type),
                'methods' => $methods
            );
        }
        
        $settingBatchTime = UserSettings::EMAIL_SEND_HOURLY;
        $currentSetting = (int) $this->appSettings->getAppSetting('setting', 'batchtime');
        //var_dump($currentSetting);
        if ($currentSetting === 3600 * 24 * 7) {
            $settingBatchTime = UserSettings::EMAIL_SEND_WEEKLY;
        } elseif ($currentSetting === 3600 * 24) {
            $settingBatchTime = UserSettings::EMAIL_SEND_DAILY;
        }

        return new TemplateResponse($this->appName, 'settings-admin', array(
            'activities' => $activities,
            'notify_self' => $this->appSettings->getAppSetting('setting', 'self'),
            'notify_selfemail' => $this->appSettings->getAppSetting('setting', 'selfemail'),
            'setting_batchtime' => $settingBatchTime,
            'methods' => [
                IExtension::METHOD_MAIL => $this->l10n->t('Mail'),
                IExtension::METHOD_STREAM => $this->l10n->t('Stream')
            ]
        ), 'blank');
    }
}