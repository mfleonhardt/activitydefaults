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

use OCA\Activity\UserSettings;
use OCA\ActivityDefaults\AppSettings;
use OCP\Activity\IExtension;
use OCP\Activity\IManager;
use OCP\Activity\ISetting;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;

class SettingsController extends Controller
{

    /** @var IConfig */
    protected $config;

    /** @var ISecureRandom */
    protected $random;

    /** @var IURLGenerator */
    protected $urlGenerator;

    /** @var IManager */
    protected $manager;

    /** @var AppSettings */
    protected $appSettings;

    /** @var \OCP\IL10N */
    protected $l10n;


    public function __construct(
        $appName,
        IRequest $request,
        IConfig $config,
        ISecureRandom $random,
        IURLGenerator $urlGenerator,
        IManager $manager,
        AppSettings $appSettings,
        IL10N $l10n
    ) {
        parent::__construct($appName, $request);
        $this->config = $config;
        $this->random = $random;
        $this->urlGenerator = $urlGenerator;
        $this->manager = $manager;
        $this->appSettings = $appSettings;
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
    public function admin(
        $notify_setting_batchtime = UserSettings::EMAIL_SEND_HOURLY,
        $notify_setting_self = false,
        $notify_setting_selfemail = false
    ) {
        foreach (array_keys($this->manager->getSettings()) as $type) {
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
        $settings = $this->manager->getSettings();
        usort($settings, function (ISetting $a, ISetting $b) {
            if ($a->getPriority() === $b->getPriority()) {
                return $a->getIdentifier() > $b->getIdentifier();
            }

            return $a->getPriority() > $b->getPriority();
        });

        $activities = array();
        foreach ($settings as $setting) {
            if (!$setting->canChangeStream() && !$setting->canChangeMail()) {
                continue;
            }

            $methods = [];
            if ($setting->canChangeStream()) {
                $methods[] = IExtension::METHOD_STREAM;
            }
            if ($setting->canChangeMail()) {
                $methods[] = IExtension::METHOD_MAIL;
            }

            $activities[$setting->getIdentifier()] = [
                'desc' => $setting->getName(),
                IExtension::METHOD_MAIL => $this->appSettings->getAppSetting('email', $setting->getIdentifier()),
                IExtension::METHOD_STREAM => $this->appSettings->getAppSetting('stream', $setting->getIdentifier()),
                'methods' => $methods
            ];
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