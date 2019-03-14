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
    /**
     * @var string
     */
    private $dateFormat = 'YmdHis';

    /**
     * @param \DateTime $value1
     * @param \DateTime $value2
     */
    private function assertDateTimeEquals(\DateTime $value1, \DateTime $value2)
    {
        $this->assertEquals(
            $value1->format($this->dateFormat),
            $value2->format($this->dateFormat)
        );
    }

    public function testDates()
    {
        $user = new User('john');
        $def = new NotificationDefinition('foo message', 'foo group');
        $instance = $def->createInstance($user);

        self::$em->persist($user);
        self::$em->persist($def);
        self::$em->persist($instance);
        self::$em->flush();
        self::$em->clear();

        /* @var UserNotificationInstance $instanceFromDb*/
        $instanceFromDb = self::$em->find(UserNotificationInstance::clazz(), $instance->getId());

        $this->assertInstanceOf('DateTime', $instanceFromDb->getCreatedAt());
        $this->assertNull($instanceFromDb->getUpdatedAt());
        $this->assertNull($instanceFromDb->getReadAt());

        $createdAt = $instanceFromDb->getCreatedAt();
        $instanceFromDb->setStatus(UserNotificationInstance::STATUS_READ);
        self::$em->flush();
        self::$em->clear();

        $updatedAt = $instanceFromDb->getUpdatedAt();
        $readAt = $instanceFromDb->getReadAt();
        /* @var UserNotificationInstance $instanceFromDb*/
        $instanceFromDb = self::$em->find(UserNotificationInstance::clazz(), $instance->getId());

        $this->assertInstanceOf('DateTime', $instanceFromDb->getCreatedAt());
        $this->assertDateTimeEquals($createdAt, $instanceFromDb->getCreatedAt());
        $this->assertInstanceOf('DateTime', $instanceFromDb->getUpdatedAt());
        $this->assertDateTimeEquals($updatedAt, $instanceFromDb->getUpdatedAt());
        $this->assertInstanceOf('DateTime', $instanceFromDb->getReadAt());
        $this->assertDateTimeEquals($readAt, $instanceFromDb->getReadAt());
    }
}
