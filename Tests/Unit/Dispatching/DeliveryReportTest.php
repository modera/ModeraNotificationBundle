<?php

namespace Modera\NotificationBundle\Tests\Unit\Dispatching;

use Modera\NotificationBundle\Dispatching\ChannelInterface;
use Modera\NotificationBundle\Dispatching\ChannelRegistryInterface;
use Modera\NotificationBundle\Dispatching\DeliveryReport;
use Modera\NotificationBundle\Dispatching\NotificationBuilder;
use Modera\NotificationBundle\Dispatching\NotificationCenter;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class DeliveryReportTest extends \PHPUnit_Framework_TestCase
{
    private function createMocks()
    {
        $registry = \Phake::mock(ChannelRegistryInterface::class);

        $ns = \Phake::mock(NotificationCenter::class);
        \Phake::when($ns)
            ->getChannelRegistry()
            ->thenReturn($registry)
        ;

        $nb = \Phake::mock(NotificationBuilder::class);
        \Phake::when($nb)
            ->getNotificationCenter()
            ->thenReturn($ns)
        ;

        $channel = \Phake::mock(ChannelInterface::class);
        \Phake::when($channel)
            ->getId()
            ->thenReturn('foo_channel')
        ;

        \Phake::when($registry)
            ->getById('foo_channel')
            ->thenReturn($channel)
        ;

        return array(
            'registry' => $registry,
            'notification_center' => $ns,
            'notification_builder' => $nb,
            'channel' => $channel,
        );
    }

    public function testMarkingAsDeliveredWithChannelInstance()
    {
        $mocks = $this->createMocks();

        $report = new DeliveryReport(
            $mocks['notification_builder'],
            'foo',
            function () {
            }
        );

        $this->assertFalse($report->isAlreadyHandled($mocks['channel']));

        $report->markDelivered($mocks['channel']);

        $this->assertTrue($report->isAlreadyHandled($mocks['channel']));

        $this->assertTrue($report->isSuccessful());
    }

    public function testMarkingAsDeliveredAsString()
    {
        $mocks = $this->createMocks();

        $report = new DeliveryReport(
            $mocks['notification_builder'],
            'foo',
            function () {
            }
        );

        $this->assertFalse($report->isAlreadyHandled($mocks['channel']));

        $report->markDelivered('foo_channel', 'foo-message', 'foo-meta');

        $this->assertTrue($report->isAlreadyHandled('foo_channel'));

        $this->assertTrue($report->isSuccessful());

        $deliveries = $report->getSuccessfulDeliveries();

        $this->assertEquals(1, count($deliveries));
        $this->assertArrayHasKey('channel', $deliveries[0]);
        $this->assertArrayHasKey('message', $deliveries[0]);
        $this->assertArrayHasKey('meta', $deliveries[0]);

        $this->assertSame($mocks['channel'], $deliveries[0]['channel']);
        $this->assertEquals('foo-message', $deliveries[0]['message']);
        $this->assertEquals('foo-meta', $deliveries[0]['meta']);
    }

    public function testMarkingAsFailed()
    {
        $mocks = $this->createMocks();

        $report = new DeliveryReport(
            $mocks['notification_builder'],
            'foo',
            function () {
            }
        );

        $this->assertFalse($report->isAlreadyHandled($mocks['channel']));

        $report->markFailed($mocks['channel']);

        $this->assertTrue($report->isAlreadyHandled($mocks['channel']));

        $this->assertTrue($report->isFailed());
    }

    public function testMarkingAsFailedAsString()
    {
        $mocks = $this->createMocks();

        $report = new DeliveryReport(
            $mocks['notification_builder'],
            'foo',
            function () {
            }
        );

        $this->assertFalse($report->isAlreadyHandled($mocks['channel']));

        $report->markFailed('foo_channel', 'foo-error', 'foo-meta');

        $this->assertTrue($report->isAlreadyHandled('foo_channel'));

        $this->assertFalse($report->isSuccessful());

        // errors:

        $errors = $report->getErrors();

        $this->assertEquals(1, count($errors));
        $this->assertArrayHasKey('channel', $errors[0]);
        $this->assertArrayHasKey('error', $errors[0]);
        $this->assertArrayHasKey('meta', $errors[0]);
        $this->assertSame($mocks['channel'], $errors[0]['channel']);
        $this->assertEquals('foo-error', $errors[0]['error']);
        $this->assertEquals('foo-meta', $errors[0]['meta']);
    }

    public function testContributeMeta()
    {
        $mocks = $this->createMocks();

        $meta = array();

        $report = new DeliveryReport(
            $mocks['notification_builder'],
            'foo',
            function (array $contributedMeta) use (&$meta) {
                $meta = $contributedMeta;
            }
        );

        $metaToContribute = array('foo_key' => 'foo_val');
        $report->contributeMeta($metaToContribute);

        $this->assertEquals($metaToContribute, $meta);
    }
}
