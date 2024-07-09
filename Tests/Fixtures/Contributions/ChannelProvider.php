<?php

namespace Modera\NotificationBundle\Tests\Fixtures\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ChannelProvider implements ContributorInterface
{
    public $items = [];

    /**
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function getItems(): array
    {
        return array_values($this->items);
    }
}
