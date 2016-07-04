<?php

namespace Modera\NotificationBundle\Tests\Fixtures\Contributions;

use Modera\NotificationBundle\Dispatching\ChannelInterface;
use Modera\NotificationBundle\Dispatching\NotificationBuilder;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class DummyChannel implements ChannelInterface
{
    public $id;

    public $dispatchInvocations = [];

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(NotificationBuilder $builder, $dispatchResult)
    {
        $this->dispatchInvocations[] = [$builder, $dispatchResult];
    }
}
