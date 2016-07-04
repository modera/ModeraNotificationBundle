<?php

namespace Modera\NotificationBundle;

use Modera\NotificationBundle\Dispatching\NotificationBuilder;
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
    }

    public function test()
    {
        $notificationCenter = $this->get('notification_center');

        $channels = [];

        $notificationCenter->createBuilder('hello world', 'groupx')->dispatch();

        /* @var NotificationBuilder $builder */
        $builder = null;

        $builder
            ->setRecipients([])
            ->addRecipient()
            ->setMessage()
            ->setGroup()
            ->setChannels()
            ->setMetaProperty('key', 'value')
            ->setMeta()
            ->dispatch($channels)
        ;
    }
}
