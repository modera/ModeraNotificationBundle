<?php

namespace Modera\NotificationBundle\Tests\Unit\Transport;

use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Transport\InvalidUIDFormatException;
use Modera\NotificationBundle\Transport\UID;
use Modera\NotificationBundle\Model\NotificationInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class UIDTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneralized()
    {
        $uid = UID::parse('foo');

        $this->assertEquals('foo', $uid->getGroup());
        $this->assertNull($uid->getNotification());
        $this->assertTrue($uid->isGeneralized());
        $this->assertFalse($uid->isSpecific());
    }

    public function testSpecific()
    {
        $uid = UID::parse('foo:12');

        $this->assertEquals('foo', $uid->getGroup());
        $this->assertEquals('12', $uid->getNotification());
        $this->assertFalse($uid->isGeneralized());
        $this->assertTrue($uid->isSpecific());
    }

    public function testWithInvalidSyntax()
    {
        $thrownException = null;

        try {
            UID::parse('foo:bar:baz:yoyo');
        } catch (InvalidUIDFormatException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertEquals('foo:bar:baz:yoyo', $thrownException->getUID());
    }

    public function testCreateFromExistingNotification()
    {
        $notification = \Phake::mock(NotificationInterface::class);

        \Phake::when($notification)
            ->getId()
            ->thenReturn('123')
        ;
        \Phake::when($notification)
            ->getGroup()
            ->thenReturn('foo')
        ;

        $uid = UID::create($notification);

        $this->assertEquals('foo', $uid->getGroup());
        $this->assertEquals('123', $uid->getNotification());
        $this->assertTrue($uid->isSpecific());
        $this->assertTrue($uid->isUserSpecific());
    }

    public function testCreateFromDefinition()
    {
        $def = \Phake::mock(NotificationDefinition::class);

        \Phake::when($def)
            ->getId()
            ->thenReturn('123')
        ;
        \Phake::when($def)
            ->getGroupName()
            ->thenReturn('foo')
        ;

        $uid = UID::create($def);

        $this->assertEquals('foo', $uid->getGroup());
        $this->assertEquals('123', $uid->getNotification());
        $this->assertTrue($uid->isSpecific());
        $this->assertFalse($uid->isUserSpecific());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalidArgument()
    {
        UID::create(new \stdClass());
    }

    /**
     * @internal
     */
    public function testUserSpecific()
    {
        $uid = UID::parse('foo:12:true');

        $this->assertEquals('foo', $uid->getGroup());
        $this->assertEquals('12', $uid->getNotification());
        $this->assertFalse($uid->isGeneralized());
        $this->assertTrue($uid->isSpecific());
        $this->assertTrue($uid->isUserSpecific());
    }
}
