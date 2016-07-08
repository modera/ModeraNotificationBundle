<?php

namespace Modera\NotificationBundle\Dispatching;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
abstract class AbstractChannel implements ChannelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Will return TRUE if $builder requested given channel to be used and it has not been used
     * yet before (this might happen when one physical class is responsible for handling several
     * virtual channels, for example - backend.desktop, backend.title ...)
     *
     * {@inheritdoc}
     */
    public function canHandle(NotificationBuilder $builder, DeliveryReport $report)
    {
        if ($report->isAlreadyHandled($this)) {
            return false;
        }

        foreach (array_merge([$this->getId()], $this->getAliases()) as $id) {
            if (in_array($id, $builder->getChannels())) {

                return true;
            }
        }

        return false;
    }
}
