<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/13/2015
 * Time: 4:15 PM
 */

namespace WizmassNotifier\Notifications;

class NotificationFactory {


    private static $instance;

    private static $types;


    protected function __construct()
    {
        static::$types = [];
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

//    public function RegisterType($name, $builder) {
//
//        static::$types[$name] = $builder;
//    }

    /**
     * @param \ElggEntity $entity
     */
    public function Build($entity) {

        echo $entity->guid . PHP_EOL;
        echo $entity->getSubtype() . PHP_EOL;

        switch ($entity->getSubtype()) {
            case 'sub_comment_notification':
                return new \WizmassNotifier\Notifications\SubCommentNotification($entity);
            case 'rate_comment_notification':
                return new \WizmassNotifier\Notifications\RateCommentNotification($entity);
            default:
                return false;
        }

    }

    public function Count()
    {
        return count(static::$types);
    }


}