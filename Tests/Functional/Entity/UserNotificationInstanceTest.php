<?php

namespace Modera\NotificationBundle\Tests\Functional\Entity;

use Modera\NotificationBundle\Tests\Fixtures\Entity\User;
use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Tests\Functional\AbstractDatabaseTest;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class UserNotificationInstanceTest extends AbstractDatabaseTest
{
    public function testDates()
    {
        $user = new User('john');
        $def = new NotificationDefinition('foo message', 'foo group');

        self::$em->persist($user);
        self::$em->persist($def);
        self::$em->flush();

        $instance = $def->createInstance($user);
        self::$em->persist($instance);
        self::$em->flush();

        $this->assertInstanceOf('DateTime', $instance->getCreatedAt());
        $this->assertNull($instance->getUpdatedAt());
        $this->assertNull($instance->getReadAt());
        $createdAt = $instance->getCreatedAt();

        $instance->setStatus(UserNotificationInstance::STATUS_READ);
        self::$em->flush();

        $this->assertInstanceOf('DateTime', $instance->getCreatedAt());
        $this->assertEquals($createdAt, $instance->getCreatedAt());
        $this->assertInstanceOf('DateTime', $instance->getUpdatedAt());
        $this->assertInstanceOf('DateTime', $instance->getReadAt());
    }
}
