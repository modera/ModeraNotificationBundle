<?php

namespace Modera\NotificationBundle\DependencyInjection\Compiler;

use Modera\NotificationBundle\Channels\MonologChannel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Monolog channel is going to be available only in "dev", "test" environments.
 *
 * @internal
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class MonologChannelCompiler implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $env = $container->getParameter('kernel.environment');

        if (in_array(strtolower($env), ['dev', 'test'])) {
            if ($container->has('logger')) {
                $channel = new Definition(MonologChannel::class);
                $channel->addArgument(new Reference('logger'));

                $container->setDefinition('modera_notification.channels.monolog_channel', $channel);
            }
        }
    }
}
