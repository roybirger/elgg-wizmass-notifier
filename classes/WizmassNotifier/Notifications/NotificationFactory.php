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
     * @param $entity ElggEntity
     */
    public function Build($entity) {

//        if (in_array($entity->getSubtype(),static::$types)) {
//
//            $func = static::$types[$entity->getSubtype()];
//
//            return $func($entity);
//        }
//        else {
//            return false;
//        }

        echo $entity->getSubtype() . PHP_EOL;

        switch ($entity->getSubtype()) {
            case 'my_comment_notification':
                return new \WizmassNotifier\Notifications\MyCommentNotification($entity);
            default:
                return false;
        }

    }

    public function Count()
    {
        return count(static::$types);
    }


}