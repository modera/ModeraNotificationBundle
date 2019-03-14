<?php

namespace Modera\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Modera\NotificationBundle\Model\NotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserNotifications links a recipient and a notification essence represented by Notification entity.
 *
 * @private
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="modera_notification_usernotificationinstance",
 *     indexes={
 *         @ORM\Index(name="status_idx", columns={"status"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks
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
     * @ORM\JoinColumn(name="definition_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $definition;

    /**
     * @ORM\ManyToOne(targetEntity="Symfony\Component\Security\Core\User\UserInterface")
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $recipient;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_NOT_READ;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $readAt;

    /**
     * @param NotificationDefinition $definition
     * @param UserInterface          $recipient
     */
    public function __construct(NotificationDefinition $definition, UserInterface $recipient)
    {
        $this->definition = $definition;
        $this->recipient = $recipient;
        $this->createdAt = new \DateTime('now');
    }

    public static function clazz()
    {
        return get_called_class();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->definition->getMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        return $this->definition->getMeta();
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->definition->getGroupName();
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
        if (!$this->readAt && $status == self::STATUS_READ) {
            $this->readAt = new \DateTime('now');
        }

        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return null|\DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return null|\DateTime
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * @private
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }
}
