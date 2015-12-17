<?php

/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/8/2015
 * Time: 3:38 PM
 */

namespace WizmassNotifier;


class TestNotification extends \WizmassNotifier\ElggNotification {

    public function BuildNotificationData()
    {
        return "test notification";
    }

}