<?php

namespace Modera\NotificationBundle\Dispatching;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ChannelNotFoundException extends \RuntimeException
{
    /**
     * @var mixed
     */
    private $channelId;

    /**
     * @param $channelId
     *
     * @return ChannelNotFoundException
     */
    public static function create($channelId)
    {
        $me = new static(sprintf('Channel with ID "%s" is not found.', $channelId));

        return $me;
    }

    /**
     * @return mixed
     */
    public function getChannelId()
    {
        return $this->channelId;
    }
}
