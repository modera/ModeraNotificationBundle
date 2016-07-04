<?php

namespace Modera\NotificationBundle\Dispatching;

use Sli\ExpanderBundle\Ext\ContributorInterface;


/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ChannelRegistryProviderAdapter implements ChannelRegistryInterface
{
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
            if ($channel->getId() == $id) {
                return $channel;
            }
        }

        return null;
    }
}
