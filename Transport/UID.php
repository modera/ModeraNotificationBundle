<?php

namespace Modera\NotificationBundle\Transport;

use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Model\NotificationInterface;

/**
 * Encapsulates notification identifying logic, allows to identify ID in cross-request (scope) manner without
 * exposing underlying database structure.
 *
 * UID can be given in two forms:
 *  - foo
 *  - foo:12
 * "foo" UID in context on backend notification framework is called "generalized" and targets a notification group
 * as a whole - an action must be taken all notifications that belong to this group. Second UID - "foo:12" represents
 * a notification from group "foo" with ID 12, so action must be taken on this single notification only.
 *
 * Actually, there's a third form also available - foo:123:true, but it is low level technical one,
 * you won't need to use it manually.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class UID
{
    /**
     * @var string
     */
    private $group;

    /**
     * @var string
     */
    private $notification;

    /**
     * @var boolean
     */
    private $isUserSpecific;

    /**
     * @internal
     *
     * @see parse()
     * @see create()
     *
     * @param string $group
     * @param string $notification
     */
    public function __construct($group, $notification, $isUserSpecific)
    {
        $this->group = $group;
        $this->notification = $notification;
        $this->isUserSpecific = $isUserSpecific;
    }

    /**
     * @param string $uid
     *
     * @return UID
     */
    public static function parse($uid)
    {
        $exp = explode(':', $uid);

        $group = null;
        $notification = null;
        $isUserSpecific = count($exp) == 3 && true == $exp[2]; // foo:12:true

        $group = $exp[0]; // a generalized UID is given like "foo"

        if (count($exp) >= 2) { // a specific like "foo:12" is provided
            $notification = $exp[1];
        }

        if (count($exp) > 3) {
            throw InvalidUIDFormatException::create(
                $uid,
                'Invalid UID given, UIDs must conform to a standard - "group:id" or simply "group" for generalized ones.'
            );
        }

        return new static($group, $notification, $isUserSpecific);
    }

    /**
     * @param string $expectedGroup
     *
     * @return bool
     */
    public function assertGroup($expectedGroup)
    {
        return $this->getGroup() == $expectedGroup;
    }

    /**
     * Creates a UID from an existing notification.
     *
     * @param NotificationInterface|NotificationDefinition $notification
     *
     * @return UID
     */
    public static function create($notification)
    {
        if ($notification instanceof NotificationInterface) {
            return new self(
                $notification->getGroup(),
                $notification->getId(),
                true
            );
        } elseif ($notification instanceof NotificationDefinition) {
            return new self(
                $notification->getGroupName(),
                $notification->getId(),
                false
            );
        } else {
            throw new \InvalidArgumentException(
                'Only instances of NotificationInterface or NotificationDefinition are expected.'
            );
        }
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Returns TRUE if a specific single notification is targeted by this UID.
     *
     * @return bool
     */
    public function isSpecific()
    {
        return (bool) $this->notification;
    }

    /**
     * Returns TRUE if a notification group (multiple notifications, that is) is targeted by this UID.
     *
     * The opposite of isSpecific() method.
     *
     * @return bool
     */
    public function isGeneralized()
    {
        return !$this->notification;
    }

    /**
     * @internal
     *
     * @return boolean
     */
    public function isUserSpecific()
    {
        return $this->isUserSpecific;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $suffix = $this->isUserSpecific() ? ':true' : '';

        return $this->isGeneralized()
            ? $this->group
            : $this->group.':'.$this->notification.$suffix;
    }
}
