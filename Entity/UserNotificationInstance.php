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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_NOT_READ;

    public function __construct(NotificationDefinition $definition, UserInterface $recipient)
    {
        $this->definition = $definition;
        $this->recipient = $recipient;
    }

    public static function clazz()
    {
        return get_called_class();
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->definition->getMessage();
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return $this->definition->getMeta();
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
     * @return UserInterface
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
