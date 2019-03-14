<?php

namespace Modera\NotificationBundle\Dispatching;

use Doctrine\ORM\EntityManager;
use Modera\NotificationBundle\Transport\UID;
use Modera\NotificationBundle\Entity\NotificationDefinition;
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
    /**
     * @var ChannelRegistryInterface
     */
    private $channelRegistry;

    /**
     * @param ChannelRegistryInterface $channelRegistry
     * @param RegistryInterface        $doctrineRegistry
     */
    public function __construct(ChannelRegistryInterface $channelRegistry, RegistryInterface $doctrineRegistry)
    {
        $this->channelRegistry = $channelRegistry;

        parent::__construct($doctrineRegistry);
    }

    /**
     * @return ChannelRegistryInterface
     */
    public function getChannelRegistry()
    {
        return $this->channelRegistry;
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
     *
     * @return DeliveryReport
     */
    public function dispatchUsingBuilder(NotificationBuilder $builder)
    {
        $channels = [];
        if (count($builder->getChannels()) == 0) {
            $channels = $this->channelRegistry->all();
        } else {
            foreach ($builder->getChannels() as $id) {
                $channel = $this->channelRegistry->getById($id);
                if ($channel) {
                    $channels[] = $channel;
                } else {
                    if ($builder->isExceptionThrownWhenChannelNotFound()) {
                        throw ChannelNotFoundException::create($id);
                    }
                }
            }

            // One channel could have been added several times because of aliases
            $channels = DuplicateChannelsFilterer::filter($channels, $builder->getChannels());
        }

        $def = new NotificationDefinition($builder->getMessage(), $builder->getGroup(), $builder->getLifetime());
        $def->setMeta($builder->getMeta());

        foreach ($builder->getRecipients() as $user) {
            $def->createInstance($user);
        }

        /* @var EntityManager $em */
        $em = $this->doctrineRegistry->getManager();

        $em->persist($def);
        $em->flush();

        $report = new DeliveryReport($builder, UID::create($def), function (array $metaToContribute) use ($def) {
            $def->setMeta(array_merge($def->getMeta(), $metaToContribute));
        });

        foreach ($channels as $channel) {
            $channel->dispatch($builder, $report);
        }

        // channels might want to update "meta" through given $report so we need to re-sync database
        $em->flush($def);

        return $report;
    }
}
