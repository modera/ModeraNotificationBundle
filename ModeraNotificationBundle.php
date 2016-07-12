<?php

namespace Modera\NotificationBundle;

use Modera\NotificationBundle\DependencyInjection\Compiler\MonologChannelCompiler;
use Sli\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ModeraNotificationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $extensionPoint = new ExtensionPoint('modera_notification.channels');
        $extensionPoint->setDescription('Allows to contribute additional notification channels.');

        $container->addCompilerPass($extensionPoint->createCompilerPass());

        $container->addCompilerPass(new MonologChannelCompiler());
    }
}
