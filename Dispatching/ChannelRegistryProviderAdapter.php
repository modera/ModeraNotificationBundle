<?php

namespace Modera\NotificationBundle\Dispatching;

use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * Uses extension point architecture to discover available channels.
 *
 * @see \Modera\NotificationBundle\ModeraNotificationBundle
 *
 * @internal
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ChannelRegistryProviderAdapter implements ChannelRegistryInterface
{
    /**
     * @var ContributorInterface
     */
    private $contributor;

    /**
     * @param ContributorInterface $contributor
     */
    public function __construct(ContributorInterface $contributor)
    {
        $this->contributor = $contributor;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->contributor->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        foreach ($this->all() as $channel) {
            if ($channel->getId() == $id || in_array($id, $channel->getAliases())) {
                return $channel;
            }
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIds(array $ids)
    {
        return DuplicateChannelsFilterer::filter($this->all(), $ids);
    }
}
