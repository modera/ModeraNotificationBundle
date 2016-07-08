<?php

namespace Modera\NotificationBundle\Dispatching;

/**
 * High-level abstraction for discovering channels.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
interface ChannelRegistryInterface
{
    /**
     * Returns all known channels.
     *
     * @return ChannelInterface[]
     */
    public function all();

    /**
     * Checks channel ID and Aliases and if any of them matches then it must be returned by this method.
     *
     * @param mixed $id
     *
     * @return ChannelInterface|null
     */
    public function getById($id);
}
