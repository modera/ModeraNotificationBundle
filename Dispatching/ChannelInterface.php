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
     * @return string
     */
    public function getId();

    /**
     * Other IDs how this channel can be identified.
     *
     * @return string
     */
    public function getAliases();

    /**
     * Method decides if the given channel is able to handle a configured notification.
     *
     * @param NotificationBuilder $builder
     *
     * @return mixed
     */
    public function canHandle(NotificationBuilder $builder, DeliveryReport $report);

    /**
     * Must dispatch a notification through a medium that this channel is responsible for.
     *
     * @param NotificationBuilder $builder
     * @param DeliveryReport      $report
     *
     * @return string
     */
    public function dispatch(NotificationBuilder $builder, DeliveryReport $report);
}
