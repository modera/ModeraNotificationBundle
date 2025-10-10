<?php

namespace Modera\NotificationBundle\Tests\Unit\Dispatching;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\NotificationBundle\Dispatching\ChannelInterface;
use Modera\NotificationBundle\Dispatching\ChannelRegistryProviderAdapter;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class ChannelRegistryProviderAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testGetByIds()
    {
        $items = [
            $this->createChannelMock('backend', ['backend.title', 'backend.sound', 'backend.info-box', 'backend.desktop']),
            $this->createChannelMock('frontend', ['popup'])
        ];

        $contributor = \Phake::mock(ContributorInterface::class);
        \Phake::when($contributor)
            ->getItems()
            ->thenReturn($items)
        ;

        $provider = new ChannelRegistryProviderAdapter($contributor);

        $channels = $provider->getByIds(['backend.title', 'backend.sound']);
        $this->assertEquals(1, count($channels));
        $this->assertSame($items[0], $channels[0]);

        $channels = $provider->getByIds(['backend']);
        $this->assertEquals(1, count($channels));
        $this->assertSame($items[0], $channels[0]);

        $channels = $provider->getByIds(['frontend']);
        $this->assertEquals(1, count($channels));
        $this->assertSame($items[1], $channels[0]);

        $channels = $provider->getByIds(['frontend', 'backend.title', 'backend.sound']);
        $this->assertEquals(2, count($channels));
        $this->assertSame($items[1], $channels[0]);
        $this->assertSame($items[0], $channels[1]);
    }

    private function createChannelMock($id, $aliases = [])
    {
        $channel = \Phake::mock(ChannelInterface::class);
        \Phake::when($channel)
            ->getId()
            ->thenReturn($id)
        ;
        \Phake::when($channel)
            ->getAliases()
            ->thenReturn($aliases)
        ;

        return $channel;
    }
}