<?php

namespace Modera\NotificationBundle\Dispatching;

use Modera\NotificationBundle\Service\NotificationService;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Service provides basic routines for manipulating notifications - dispatching(creating), querying, batch changing
 * of notification statuses.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class NotificationCenter extends NotificationService
{
    private $channelRegistry;

    public function __construct(ChannelRegistryInterface $channelRegistry, RegistryInterface $doctrineRegistry)
    {
        $this->channelRegistry = $channelRegistry;

        parent::__construct($doctrineRegistry);
    }

    /**
     * Create a builder that you can use to configure a notification, once ready invoke its dispatch() method
     * to send out the notification.
     *
     * @param string $message
     * @param string $group
     *
     * @return NotificationBuilder
     */
    public function createNotificationBuilder($message, $group)
    {
        return new NotificationBuilder($this, $message, $group);
    }

    /**
     * Do not use this method directly, use createNotificationBuilder() to create a builder and its
     * "dispatch" method instead.
     *
     * @internal
     *
     * @throws ChannelNotFoundException
     *
     * @param NotificationBuilder $builder
     */
    public function dispatch(NotificationBuilder $builder)
    {
        $dispatchResult = parent::dispatch(
            $builder->getGroup(), $builder->getMessage(),$builder->getRecipients(), $builder->getMeta()
        );

        $channels = [];
        if (count($builder->getChannels()) == 0) {
            $channels = $this->channelRegistry->all();
        } else {
            foreach ($builder->getChannels() as $id) {
                $channel = $this->channelRegistry->getById($id);
                if (!$channel) {
                    throw ChannelNotFoundException::create($id);
                }

                $channels[] = $channel;
            }
        }

        foreach ($channels as $channel) {
            $channel->dispatch($builder, $dispatchResult);
        }

        return $dispatchResult;
    }
}
