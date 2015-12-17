<?php
/**
 * Class for notification functionalities
 *
 */

namespace WizmassNotifier\Interfaces;

require_once(dirname(__FILE__) . '/BaseNotification.php');


abstract class WizmassNotification extends \ElggObject implements BaseNotification {

    const HAS_ACTOR = "hasActor";
    const HAS_OBJECT = "hasObject";
    const WAS_READ = "wasRead";

    /**
     * Set the user triggering the notification
     *
     * @param ElggUser $user User whose action triggered the notification
     * @return bool
     */
    public function addSubject ($user) {
        return $this->addRelationship($user->guid, self::HAS_ACTOR);
    }

    /**
     * Set the object involved in the notification
     *
     * @param ElggEntity $entity Entity that the notification is about
     * @return bool
     */
    public function setTarget ($entity) {
        return $this->addRelationship($entity->guid, self::HAS_OBJECT);
    }

    /**
     * Get the object of the notification
     *
     * @return ElggObject $object
     */
    public function getTarget () {
        $object = $this->getEntitiesFromRelationship(array('relationship' => self::HAS_OBJECT));
        if ($object) {
            $object = $object[0];
        }

        return $object;
    }

    /**
     * Get display name of the notification target
     *
     * @return string $name Display name
     */
    public function getTargetName() {
        $name = elgg_echo('unknown');

        $target = $this->getTarget();

        if (!$target) {
            // This may happen if the target owner changes the ACL
            // after the notification has already been created
            return $name;
        }

        $name = $target->getDisplayName();

        if (empty($name) && $target->description) {
            $name = elgg_get_excerpt($target->description, 20);
        }

        return $name;
    }

    /**
     * Get the user who triggered the notification
     *
     * @return ElggUser $subject
     */
    public function getSubject () {
        $subject = $this->getSubjects();
        if ($subject) {
            $subject = $subject[0];
        }

        return $subject;
    }

    /**
     * Get all users who participate in the notification
     *
     * @return ElggUser[]|false
     */
    public function getSubjects() {
        return $this->getEntitiesFromRelationship(array('relationship' => self::HAS_ACTOR));
    }

    /**
     * Mark this notification as read
     *
     * @param $user
     */
    public function markRead($user) {

        return $this->addRelationship($user->guid, self::WAS_READ);

    }

    /**
     * Mark this notification as unread
     *
     * @param $user
     */
    public function markUnread($user) {
        return $this->removeRelationship($user->guid,self::WAS_READ);
    }

    /** Override */
//    public function save () {
//        // We are writing to someone else's container so ignore access
//        $ia = elgg_set_ignore_access(true);
//        $this->access_id = ACCESS_PRIVATE;
//        $this->status = 'unread';
//
//        $success = parent::save();
//
//        elgg_set_ignore_access($ia);
//
//        return $success;
//    }

    public function ShouldDelete()
    {
        $difference = time() - $this->getTimeCreated();

        return ($difference > $this->GetDeleteInterval()) ? true : false;

    }

    public function GetDeleteInterval()
    {
        $timeToDelete = 60 * 60 * 24 * 7; //7 days
        return $timeToDelete;
    }


}