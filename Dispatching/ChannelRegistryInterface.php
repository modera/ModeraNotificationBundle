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

    /**
     * @since 0.3.0
     *
     * Returned channels must be unique, that is - if there's a channel with several aliases,
     * and those several aliases are given as $ids, then only one channel needs to be returned.
     *
     * @param mixed[] $ids
     *
     * @return ChannelInterface[]
     */
    public function getByIds(array $ids);
}
