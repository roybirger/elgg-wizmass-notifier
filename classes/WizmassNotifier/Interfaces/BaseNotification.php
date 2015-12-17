<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/8/2015
 * Time: 3:36 PM
 */

namespace WizmassNotifier\Interfaces;

interface BaseNotification {

    public function BuildNotificationData();

    public function BuildAggregationKey();

    public function ShouldDelete();

    public function GetDeleteInterval();

}