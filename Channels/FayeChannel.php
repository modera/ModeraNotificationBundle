<?php

namespace Modera\NotificationBundle\Channels;

use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;
use Modera\NotificationBundle\Dispatching\ChannelInterface;
use Modera\NotificationBundle\Dispatching\NotificationBuilder;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class FayeChannel implements ChannelInterface
{
    private $entryPoint;

    /**
     * @param EntryPointInterface $entryPoint
     */
    public function __construct(EntryPointInterface $entryPoint)
    {
        $this->entryPoint = $entryPoint;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'faye';
    }

    /**
     * @param NotificationBuilder $builder
     * @param mixed $dispatchResult
     *
     * @return string
     */
    public function dispatch(NotificationBuilder $builder, $dispatchResult)
    {
        $this->entryPoint->publish('/notifications', array(
            'type' => 'notification',
            'action' => 'new',
            'data' => array(
//                'uid' => $uid,
            ),
        ));
    }

}
