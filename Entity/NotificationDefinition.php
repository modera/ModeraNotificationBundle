<?php

namespace Modera\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notification entity describes essence and contents of a notification. Notification delivery, read statuses
 * are stored in UserNotificationInstance entity.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 *
 * @ORM\Entity
 * @ORM\Table(name="modera_notification_notificationdefinition")
 */
class NotificationDefinition
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * A human readable text of notification. If you need to store some technical data please use $meta property
     * instead.
     *
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * Every user will have its own notification, which is represented by UserNotificationInstance entity. When
     * user has read notification then his own instance (UserNotificationInstance) is going to be
     * modified, NotificationDefinition stays untouched.
     *
     * @var UserNotificationInstance[]
     *
     * @ORM\OneToMany(targetEntity="UserNotificationInstance", mappedBy="definition", cascade={"PERSIST", "REMOVE"})
     */
    private $instances;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $groupName;

    /**
     * Metadata that you optionally may want to associate with a notification.
     *
     * @ORM\Column(type="json_array")
     */
    private $meta = array();

    /**
     * @param string $message
     * @param string $groupName
     */
    public function __construct($message = null, $groupName = null)
    {
        $this->message = $message;
        $this->groupName = $groupName;

        $this->instances = new ArrayCollection();
    }

    /**
     * @return string
     */
    public static function clazz()
    {
        return get_called_class();
    }

    /**
     * @param UserInterface $recipient
     *
     * @return UserNotificationInstance
     */
    public function createInstance(UserInterface $recipient)
    {
        $instance = new UserNotificationInstance($this, $recipient);

        $this->instances->add($instance);

        return $instance;
    }

    // boilerplate:

    /**
     * @return UserNotificationInstance[]
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * @param UserNotificationInstance[] $instances
     */
    public function setInstances($instances)
    {
        $this->instances = $instances;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param null $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param mixed $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }
}