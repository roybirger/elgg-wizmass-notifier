<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 4/3/2016
 * Time: 12:16 PM
 */

namespace WizmassNotifier\Notifications;

use WizmassNotifier\Interfaces\WizmassNotification;
use Elgg;

class RateCommentNotification extends WizmassNotification
{

    /**
     * SubCommentNotification constructor.
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

        $this->attributes['subtype'] = "rate_comment_notification";
    }

    public function BuildNotificationData()
    {
        return array(
            'original_comment_guid' => $this->original_comment_guid,
            'notification_guid'     => $this->guid,
            'aggregation_key'       => $this->BuildAggregationKey(),
            'pusher_guid'            => $this->rater_guid,
            'pusher_name'            => $this->rater_name,
            'pusher_image'           => $this->rater_image,
            'pusher_data'                  => $this->rate,
            'timestamp'             => $this->getTimeCreated(),
            'href'                  => $this->href);
    }

    public function BuildAggregationKey()
    {
        return 'comment_rate';
    }

    public function GetDeleteInterval()
    {
        return 60 * 2; //2 minutes for test
    }

}
