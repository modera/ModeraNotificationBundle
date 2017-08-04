<?php

namespace Modera\NotificationBundle\Tests\Unit\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class UserNotificationInstanceTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAt()
    {
        $recipient = \Phake::mock(UserInterface::class);
        $definition = \Phake::mock(NotificationDefinition::class);

        $instance = new UserNotificationInstance($definition, $recipient);

        $this->assertEquals(UserNotificationInstance::STATUS_NOT_READ, $instance->getStatus());
        $this->assertNull($instance->getReadAt());

        $instance->setStatus(UserNotificationInstance::STATUS_READ);

        $this->assertEquals(UserNotificationInstance::STATUS_READ, $instance->getStatus());
        $this->assertInstanceOf('DateTime', $instance->getReadAt());

        $readAt = $instance->getReadAt();
        sleep(1);
        $instance->setStatus(UserNotificationInstance::STATUS_NOT_READ);
        $instance->setStatus(UserNotificationInstance::STATUS_READ);

        $this->assertEquals($readAt, $instance->getReadAt());
    }
}
