<?php

namespace Modera\NotificationBundle\Tests\Functional\Dispatching;

use Modera\NotificationBundle\Dispatching\ChannelNotFoundException;
use Modera\NotificationBundle\Dispatching\NotificationCenter;
use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Tests\Fixtures\Contributions\ChannelProvider;
use Modera\NotificationBundle\Tests\Fixtures\Contributions\DummyChannel;
use Modera\NotificationBundle\Tests\Fixtures\Entity\User;
use Modera\NotificationBundle\Tests\Functional\AbstractDatabaseTest;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class NotificationCenterTest extends AbstractDatabaseTest
{
    public function testCreateNotificationBuilder()
    {
        /* @var ChannelProvider $provider */
        $provider = self::$container->get('dummy_channel_provider');

        $prefix = uniqid();

        $channel = new DummyChannel();

        // using index because we are modifying a global instance which can be used by other tests as well
        $channelIdKey = $prefix.'._1';

        $provider->items[$channelIdKey] = $channel;

        /* @var NotificationCenter $center */
        $center = self::$container->get('modera_notification.dispatching.notification_center');

        $user1 = new User('bob');
        $user2 = new User('jane');
        $user3 = new User('john');

        self::$em->persist($user1);
        self::$em->persist($user2);
        self::$em->persist($user3);
        self::$em->flush();

        $builder = $center->createNotificationBuilder('hello world', 'test-group');
        $builder
            ->setRecipients([$user1, $user2])
            ->addRecipient($user3)
            ->setMeta(array('foo_key' => 'foo_val'))
            ->setMetaProperty('bar_key', 'bar_val')
            ->dispatch()
        ;

        $this->assertEquals(1, count($channel->dispatchInvocations));
        $this->assertSame($builder, $channel->dispatchInvocations[0][0]);
        $this->assertNotNull($channel->dispatchInvocations[0][1]);

        $defId = $channel->dispatchInvocations[0][1];
        /* @var NotificationDefinition $def */
        $def = $this->em()->find(NotificationDefinition::class, $defId);

        $this->assertInstanceOf(NotificationDefinition::class, $def);
        $this->assertEquals('hello world', $def->getMessage());
        $this->assertEquals('test-group', $def->getGroupName());
        $this->assertTrue(is_array($def->getMeta()));
        $meta = $def->getMeta();

        $this->assertArrayHasKey('foo_key', $meta);
        $this->assertEquals('foo_val', $meta['foo_key']);
        $this->assertArrayHasKey('bar_key', $meta);
        $this->assertEquals('bar_val', $meta['bar_key']);

        $provider->items = [];
    }

    public function dispatchWithChannelsSpecified()
    {
        /* @var ChannelProvider $provider */
        $provider = self::$container->get('dummy_channel_provider');

        $channel1 = new DummyChannel('channel_1');
        $channel2 = new DummyChannel('channel_2');
        $channel3 = new DummyChannel('channel_3');

        $provider->items = [$channel1, $channel2, $channel3];

        /* @var NotificationCenter $center */
        $center = self::$container->get('modera_notification.dispatching.notification_center');

        $user1 = new User('bob');
        $user2 = new User('jane');
        $user3 = new User('john');

        self::$em->persist($user1);
        self::$em->persist($user2);
        self::$em->persist($user3);
        self::$em->flush();

        $builder = $center->createNotificationBuilder('hello world', 'test-group');
        $builder
            ->setRecipients([$user1, $user2])
            ->dispatch(['channel_1', 'channel_3'])
        ;

        $this->assertEquals(1, count($channel1->dispatchInvocations));
        $this->assertEquals(0, count($channel2->dispatchInvocations));
        $this->assertEquals(1, count($channel3->dispatchInvocations));
    }

    public function dispatchWithMissingChannel()
    {
        /* @var NotificationCenter $center */
        $center = self::$container->get('modera_notification.dispatching.notification_center');

        $user1 = new User('bob');

        self::$em->persist($user1);
        self::$em->flush();

        $thrownException = null;
        try {
            $builder = $center->createNotificationBuilder('hello world', 'test-group');
            $builder
                ->setRecipients([$user1])
                ->dispatch(['channel_1'])
            ;
        } catch (ChannelNotFoundException $e) {
            $thrownException = $e;
        }

        $this->assertInstanceOf(ChannelNotFoundException::class, $thrownException);
        $this->assertEquals('channel_1', $thrownException->getChannelId());
    }
}
