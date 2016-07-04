<?php

namespace Modera\NotificationBundle\Dispatching;

/**
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
     * @param NotificationBuilder $builder
     * @param mixed $dispatchResult
     *
     * @return string
     */
    public function dispatch(NotificationBuilder $builder, $dispatchResult);
}
