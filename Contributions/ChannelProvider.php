<?php

namespace Modera\NotificationBundle\Contributions;

use Modera\NotificationBundle\Dispatching\ChannelInterface;
use Modera\NotificationBundle\Dispatching\ChannelRegistryInterface;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ChannelProvider implements ContributorInterface
{
    /**
     * @return mixed[]
     */
    public function getItems()
    {
        return [];
    }
}
