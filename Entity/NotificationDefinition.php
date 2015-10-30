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
     * @ORM\Column(type="text")
     */
    private $contents;

    /**
     * Every user will have its own notification, which is represented by UserNotificationInstance entity. When
     * user has read notification then UserNotificationInstance then his own instance is going to be
     * modified, NotificationDefinition stays untouched.
     *
     * @var UserNotificationInstance[]
     *
     * @ORM\OneToMany(targetEntity="UserNotificationInstance", mappedBy="definition", cascade={"PERSIST", "REMOVE"})
     */
    private $instances;

    public function __construct($contents = null)
    {
        $this->contents = $contents;

        $this->instances = new ArrayCollection();
    }

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
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param mixed $contents
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}