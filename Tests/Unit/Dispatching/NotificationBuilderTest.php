<?php

namespace Modera\NotificationBundle\Tests\Unit\Dispatching;

use Modera\NotificationBundle\Dispatching\NotificationBuilder;
use Modera\NotificationBundle\Dispatching\NotificationCenter;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class NotificationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testFluentInterface()
    {
        $ns = \Phake::mock(NotificationCenter::class);

        $builder = new NotificationBuilder($ns, 'foo-message', 'bar-group');

        $this->assertEquals('foo-message', $builder->getMessage());
        $this->assertEquals('bar-group', $builder->getGroup());

        $builder
            ->setRecipients(['r1'])
            ->addRecipient('r2')
            ->setMessage('new-message')
            ->setGroup('new-group')
            ->setChannels(['ch1', 'ch2'])
        ;

        $this->assertEquals(['r1', 'r2'], $builder->getRecipients());
        $this->assertEquals('new-message', $builder->getMessage());
        $this->assertEquals('new-group', $builder->getGroup());

        $builder->dispatch(['foo-channel']);

        $this->assertEquals(
            ['ch1', 'ch2'],
            $builder->getChannels(),
            'When a notification is dispatched with channels specified, the original channels should not be touched.'
        );
    }
}
