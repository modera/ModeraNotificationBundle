<?php

namespace Modera\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Modera\NotificationBundle\Model\NotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserNotifications links a recipient and a notification essence represented by Notification entity.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 *
 * @ORM\Entity
 * @ORM\Table(name="modera_notification_usernotificationinstance")
 */
class UserNotificationInstance implements NotificationInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var NotificationDefinition
     *
     * @ORM\ManyToOne(targetEntity="NotificationDefinition", inversedBy="instances")
     */
    private $definition;

    /**
     * @ORM\ManyToOne(targetEntity="Symfony\Component\Security\Core\User\UserInterface")
     */
    private $recipient;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isRead = false;

    public function __construct(NotificationDefinition $definition, UserInterface $recipient)
    {
        $this->definition = $definition;
        $this->recipient = $recipient;
    }

    /**
     * @inheritdoc
     */
    public function getContents()
    {
        return $this->definition->getContents();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return NotificationDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param NotificationDefinition $definition
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return boolean
     */
    public function isRead()
    {
        return $this->isRead;
    }

    /**
     * @param boolean $isRead
     */
    public function setRead($isRead)
    {
        $this->isRead = $isRead;
    }

    /**
     * @return UserInterface
     */
    public function getRecipient()
    {
        return $this->recipient;
    }
}