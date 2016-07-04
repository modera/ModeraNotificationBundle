<?php

namespace Modera\NotificationBundle\Dispatching;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
interface ChannelRegistryInterface
{
    /**
     * @return ChannelInterface[]
     */
    public function all();

    /**
     * @return ChannelInterface|null
     */
    public function getById($id);
}
