<?php

namespace Modera\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notification entity describes essence and contents of a notification. Notification delivery, read statuses
 * are stored in UserNotificationInstance entity.
 *
 * @private
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="modera_notification_notificationdefinition",
 *     indexes={
 *         @ORM\Index(name="group_name_idx", columns={"groupName"})
 *     }
 * )
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
     * Allows to logically group notifications. To understand it more easily, think of this field as a way
     * to group your notifications in "groups", where only last notification of given "group" is shown at a time.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $groupName;

    /**
     * Metadata that you optionally may want to associate with a notification.
     *
     * @ORM\Column(type="json")
     */
    private $meta = array();

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lifetime;

    /**
     * @param string $message
     * @param string $groupName
     * @param \DateTime|null $lifetime
     */
    public function __construct($message = null, $groupName = null, \DateTime $lifetime = null)
    {
        $this->message = $message;
        $this->groupName = $groupName;

        $this->lifetime = $lifetime;
        $this->createdAt = new \DateTime('now');
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
     * @return string
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
     * @param array $meta
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }
}
