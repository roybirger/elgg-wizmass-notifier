<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 06/12/2015
 * Time: 00:13
 */

namespace WizmassNotifier\Notifications;

use WizmassNotifier\Interfaces\WizmassNotification;
use Elgg;

class MyCommentNotification extends WizmassNotification
{

    /**
     * MyCommentNotification constructor.
     * @param null|\stdClass $base
     */
    public function __construct($base = null)
    {
        if ($base != null) {
            parent::__construct($base);
        }
        else {
            $this->initializeAttributes();
            parent::create();
        }
    }

    /** Override */
    protected function initializeAttributes() {
        parent::initializeAttributes();

        $this->attributes['subtype'] = "my_comment_notification";
    }

    public function BuildNotificationData()
    {
        return array(
            'comment_guid' => $this->comment_guid,
            'notification_guid' => $this->guid,
            'aggregation_key' => $this->BuildAggregationKey());
    }

    public function BuildAggregationKey()
    {
        return 'comment_add_sub';
    }

    public function GetDeleteInterval()
    {
        return 60 * 2; //2 minutes for test
    }

}
