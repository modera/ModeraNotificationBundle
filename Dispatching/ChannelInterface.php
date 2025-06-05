<?php

namespace Modera\NotificationBundle\Dispatching;

/**
 * Represents a medium through which a notification can be delivered, for example - email, push-notification, sms etc.
 *
 * Implementations of this interface are mostly used by NotificationCenter.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
interface ChannelInterface
{
    /**
     * A unique ID that can be used to identify a channel.
     *
     * @return string
     */
    public function getId();

    /**
     * Other IDs how this channel can be identified.
     *
     * @return string[]
     */
    public function getAliases();

    /**
     * Must dispatch a notification through a medium that this channel is responsible for.
     *
     * @return void
     */
    public function dispatch(NotificationBuilder $builder, DeliveryReport $report);
}
