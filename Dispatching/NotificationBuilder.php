<?php

namespace Modera\NotificationBundle\Dispatching;

/**
 * Use NotificationCenter::createNotificationBuilder() to create instances of this class.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class NotificationBuilder
{
    /**
     * @var array
     */
    private $channels = [];

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $group;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var array
     */
    private $recipients = [];

    private $notificationCenter;

    /**
     * @internal
     *
     * @param NotificationCenter $notificationCenter
     * @param string $message
     * @param string $group
     */
    public function __construct(NotificationCenter $notificationCenter, $message, $group)
    {
        $this->notificationCenter = $notificationCenter;

        $this->message = $message;
        $this->group = $group;
    }

    /**
     * @param array $channels
     */
    public function dispatch($channels = [])
    {
        $builder = $this;
        if (count($channels) != 0) {
            $builder = clone $this;
            $builder->setChannels($channels);
        }

        $this->notificationCenter->dispatch($builder);
    }

    /**
     * @param array $recipients
     */
    public function setRecipients(array $recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @param mixed $recipient
     */
    public function addRecipient($recipient)
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * @param string $propertyName
     * @param string $value
     *
     * @return static
     */
    public function setMetaProperty($propertyName, $value)
    {
        $this->meta[$propertyName] = $value;

        return $this;
    }

    /**
     * @param array $meta
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param array $channels
     */
    public function setChannels($channels)
    {
        $this->channels = $channels;

        return $this;
    }

    // boilerplate:

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
