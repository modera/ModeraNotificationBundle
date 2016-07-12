<?php

namespace Modera\NotificationBundle\Contributions;

use Modera\NotificationBundle\Dispatching\ChannelInterface;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ChannelsProvider implements ContributorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ChannelInterface[]
     */
    private $channels;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->channels) {
            $this->channels = [];

            $env = $this->container->getParameter('kernel.environment');

            if (in_array(strtolower($env), ['dev', 'test'])) {
                $this->channels[] = $this->container->get('modera_notification.channels.monolog_channel');
            }
        }

        return $this->channels;
    }
}
