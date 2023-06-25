<?php

namespace Modera\NotificationBundle\Dispatching;

/**
 * Instances of this class are to be used to configure a notification that should be dispatched
 * through channels registered in a notification center.
 *
 * Use NotificationCenter::createNotificationBuilder() to create instances of this class.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class NotificationBuilder
{
    /**
     * Names of channels which should be used to deliver given notification.
     *
     * @var array
     */
    private $channels = [];

    /**
     * Contents of the message itself.
     *
     * @var string
     */
    private $message;

    /**
     * Used to group similar messages together. Think of a chat, it is very likely that you will want
     * to group messages from a single user together, this property can used to tell the system
     * how the notifications can be grouped and for the chat example it could look something
     * like - chat:1, where a number is ID of a user from the messages are received from.
     *
     * @var string
     */
    private $group;

    /**
     * @var \DateTime
     */
    private $lifetime;

    /**
     * An optional meta-information that you want to be stored in a database, when a notification is dispatched
     * through channels they will be able to access value of this property.
     *
     * @var array
     */
    private $meta = [];

    /**
     * Instances of UserInterface who should receive the notification.
     *
     * @var array
     */
    private $recipients = [];

    /**
     * It this property is set to TRUE and one of the channels you have specified that you want to use to deliver
     * a notification is not found then an exception will be thrown.
     *
     * @var bool
     */
    private $isExceptionThrownWhenChannelNotFound = false;

    /**
     * This property will store volatile runtime information that you may want to pass for channels to introspect
     * when dispatching a notifications. Information stored in this property, for instance, might be used by channels
     * to adjust their behaviour, it might contain some config that might be needed during dispatching but should
     * not outlive the request-response cycle.
     *
     * @var array
     */
    private $context = array();

    /**
     * @var NotificationCenter
     */
    private $notificationCenter;

    /**
     * @internal
     *
     * @param NotificationCenter $notificationCenter
     * @param string             $message
     * @param string             $group
     */
    public function __construct(NotificationCenter $notificationCenter, $message, $group)
    {
        $this->notificationCenter = $notificationCenter;

        $this->message = $message;
        $this->group = $group;
        $this->lifetime = new \DateTime('now + 14 days');
    }

    /**
     * @see throwExceptionWhenChannelNotFound()
     *
     * @return NotificationBuilder
     */
    public function suppressChannelNotFoundException()
    {
        $this->isExceptionThrownWhenChannelNotFound = false;

        return $this;
    }

    /**
     * By default if an unknown channel is specified through which a notification must be delivered then the notification
     * center will will suppress the error, by invoking this method you will signal the notification center
     * that an exception must be thrown instead in such cases.
     *
     * @return NotificationBuilder
     */
    public function throwExceptionWhenChannelNotFound()
    {
        $this->isExceptionThrownWhenChannelNotFound = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExceptionThrownWhenChannelNotFound()
    {
        return $this->isExceptionThrownWhenChannelNotFound;
    }

    /**
     * @param array $channels
     *
     * @return DeliveryReport
     */
    public function dispatch($channels = [])
    {
        $builder = $this;
        if (count($channels) != 0) {
            $builder = clone $this;
            $builder->setChannels($channels);
        }

        return $this->notificationCenter->dispatchUsingBuilder($builder);
    }

    /**
     * @param array $recipients
     *
     * @return NotificationBuilder
     */
    public function setRecipients(array $recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @param mixed $recipient
     *
     * @return NotificationBuilder
     */
    public function addRecipient($recipient)
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * @param string $propertyName
     * @param mixed $value
     *
     * @return NotificationBuilder
     */
    public function setMetaProperty($propertyName, $value)
    {
        $this->meta[$propertyName] = $value;

        return $this;
    }

    /**
     * @param array $meta
     *
     * @return NotificationBuilder
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param string $group
     *
     * @return NotificationBuilder
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @param \DateTime|null $lifetime
     *
     * @return NotificationBuilder
     */
    public function setLifetime(\DateTime $lifetime = null)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * @param string $message
     *
     * @return NotificationBuilder
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param array $channels
     *
     * @return NotificationBuilder
     */
    public function setChannels($channels)
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * @param string $propertyName
     * @param mixed  $value
     *
     * @return NotificationBuilder
     */
    public function setContextProperty($propertyName, $value)
    {
        $this->context[$propertyName] = $value;

        return $this;
    }

    /**
     * @param array $context
     *
     * @return NotificationBuilder
     */
    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    // boilerplate:

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @return NotificationCenter
     */
    public function getNotificationCenter()
    {
        return $this->notificationCenter;
    }
}
