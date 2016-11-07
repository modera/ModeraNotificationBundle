<?php

namespace Modera\NotificationBundle\Tests\Functional\Service;

use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Service\NotificationService;
use Modera\NotificationBundle\Tests\Fixtures\Entity\User;
use Modera\NotificationBundle\Tests\Functional\AbstractDatabaseTest;
use Symfony\Component\Security\Core\Exception\RuntimeException;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class NotificationServiceTest extends AbstractDatabaseTest
{
    private function loadFixtures()
    {
        $user1 = new User('bob');
        $user2 = new User('jane');
        $user3 = new User('john');

        $groupName = 'foo group';

        $def1 = new NotificationDefinition('foo message', $groupName);
        $instance1 = $def1->createInstance($user1);
        $instance2 = $def1->createInstance($user1);

        $instance3 = $def1->createInstance($user2);
        $instance3->setStatus(1234);

        $instance4 = $def1->createInstance($user3);

        self::$em->persist($user1);
        self::$em->persist($user2);
        self::$em->persist($user3);
        self::$em->persist($def1);
        self::$em->flush();

        return array(
            'group_name' => $groupName,
            'users' => [$user1, $user2, $user3],
            'instances' => [$instance1, $instance2, $instance3],
        );
    }

    public function testChangeStatusByGroupAndRecipient()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $fixtures = $this->loadFixtures();

        $newStatus = 777;
        $service->changeStatus($newStatus, array(
            'group' => $fixtures['group_name'],
            'recipient' => $fixtures['users'][0],
        ));

        self::$em->clear();

        /* @var UserNotificationInstance $instance1FromDb*/
        $instance1FromDb = self::$em->find(UserNotificationInstance::clazz(), $fixtures['instances'][0]->getId());
        $this->assertEquals($instance1FromDb->getStatus(), $newStatus);

        /* @var UserNotificationInstance $instance1FromDb*/
        $instance2FromDb = self::$em->find(UserNotificationInstance::clazz(), $fixtures['instances'][1]->getId());
        $this->assertEquals($instance2FromDb->getStatus(), $newStatus);

        /* @var UserNotificationInstance $instance1FromDb*/
        $instance3FromDb = self::$em->find(UserNotificationInstance::clazz(), $fixtures['instances'][2]->getId());
        $this->assertEquals($instance3FromDb->getStatus(), $fixtures['instances'][2]->getStatus()); // should not have been changed
    }

    public function testChangeStatusByRecipient()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $fixtures = $this->loadFixtures();

        $anotherUser = new User('another user');

        $def = new NotificationDefinition('message', 'foo_group');
        $instance1 = $def->createInstance($anotherUser);
        $instance2 = $def->createInstance($anotherUser);

        self::$em->persist($anotherUser);
        self::$em->persist($def);
        self::$em->flush();
        self::$em->clear();

        $newStatus = 777;
        $service->changeStatus($newStatus, array(
            'recipient' => $anotherUser,
        ));

        /* @var UserNotificationInstance $instance1FromDb*/
        $instance1FromDb = self::$em->getRepository(UserNotificationInstance::clazz())->find($instance1->getId());
        $this->assertEquals($newStatus, $instance1FromDb->getStatus());

        /* @var UserNotificationInstance $instance2FromDb*/
        $instance2FromDb = self::$em->getRepository(UserNotificationInstance::clazz())->find($instance2->getId());
        $this->assertEquals($newStatus, $instance2FromDb->getStatus());

        // should not have been changed:
        $instance1FromFixtures = self::$em->find(UserNotificationInstance::clazz(), $fixtures['instances'][0]->getId());
        $this->assertEquals($instance1FromFixtures->getStatus(), $fixtures['instances'][0]->getStatus());
    }

    public function testChangeStatusById()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $fixtures = $this->loadFixtures();

        $newStatus = 777;
        $service->changeStatus($newStatus, array(
            'id' => $fixtures['instances'][0]->getId(),
        ));

        self::$em->clear();

        /* @var UserNotificationInstance $instance1FromDb*/
        $instance1FromDb = self::$em->find(UserNotificationInstance::clazz(), $fixtures['instances'][0]->getId());
        $this->assertEquals($instance1FromDb->getStatus(), $newStatus);

        $instance2FromDb = self::$em->find(UserNotificationInstance::clazz(), $fixtures['instances'][1]->getId());
        $this->assertEquals($instance2FromDb->getStatus(), $fixtures['instances'][1]->getStatus()); // should not have been changed
    }

    public function testFetch()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $fixtures = $this->loadFixtures();

        $allNotifications = $service->fetchBy(array());
        $this->assertEquals(4, count($allNotifications));

        $byRecipientsNotifications = $service->fetchBy(array(
            'recipients' => [$fixtures['users'][0]],
        ));
        $this->assertEquals(2, count($byRecipientsNotifications));
        $this->assertEquals($fixtures['instances'][0]->getId(), $byRecipientsNotifications[0]->getId());
        $this->assertEquals($fixtures['instances'][1]->getId(), $byRecipientsNotifications[1]->getId());

        // ---

        $groupName = 'blah_group';

        $byGroupNameNotifications = $service->fetchBy(array(
            'group' => $groupName,
        ));
        $this->assertEquals(0, count($byGroupNameNotifications));

        // ---

        $def = new NotificationDefinition('blah', $groupName);
        $instance1 = $def->createInstance($fixtures['users'][0]);

        self::$em->persist($def);
        self::$em->flush();

        $byGroupNameNotifications = $service->fetchBy(array(
            'group' => $groupName,
        ));
        $this->assertEquals(1, count($byGroupNameNotifications));

        // ---

        $byGroupAndRecipientsNotification = $service->fetchBy(array(
            'group' => $fixtures['group_name'],
            'recipients' => [$fixtures['users'][0], $fixtures['users'][1]],
        ));

        $this->assertEquals(3, count($byGroupAndRecipientsNotification));

        foreach ($byGroupAndRecipientsNotification as $notification) {
            $this->assertInstanceOf('Modera\NotificationBundle\Model\NotificationInterface', $notification);
        }
    }

    public function testFetchByStatus()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $fixtures = $this->loadFixtures();

        $notifications = $service->fetchBy(array(
            'status' => 1234, // see loadFixtures() method
        ));

        $this->assertEquals(1, count($notifications));
    }

    public function testDispatch()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $user1 = new User('bob');
        $user2 = new User('jane');
        $user3 = new User('john');

        self::$em->persist($user1);
        self::$em->persist($user2);
        self::$em->flush();

        $meta = array(
            'some_key' => 'some_value',
        );
        $msg = 'hello world';
        $group = 'foogroup';

        $service->dispatch($group, $msg, [$user1, $user2], $meta);

        $instancesRepository = self::$em->getRepository(UserNotificationInstance::clazz());

        /* @var UserNotificationInstance[] $user1Instances*/
        $user1Instances = $instancesRepository->findBy(array('recipient' => $user1->id));
        $this->assertEquals(1, count($user1Instances));

        $definition = $user1Instances[0]->getDefinition();
        $this->assertEquals($meta, $definition->getMeta());
        $this->assertEquals($msg, $definition->getMessage());
        $this->assertEquals($group, $definition->getGroupName());

        $user2Instances = $instancesRepository->findBy(array('recipient' => $user2->id));
        $this->assertEquals(1, count($user2Instances));

        $user3Instances = $instancesRepository->findBy(array('recipient' => $user3->id));
        $this->assertEquals(0, count($user3Instances));
    }

    public function testFetchOneBy()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $fixtures = $this->loadFixtures();

        $notification = $service->fetchOneBy(array(
            'id' => $fixtures['instances'][2]->getId(),
            'recipient' => $fixtures['users'][1],
        ));

        $this->assertInstanceOf(UserNotificationInstance::clazz(), $notification);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFetchOneByWithMoreThanOneResult()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $fixtures = $this->loadFixtures();

        $notification = $service->fetchOneBy(array(
            'recipient' => $fixtures['users'][0],
        ));

        $this->assertInstanceOf(UserNotificationInstance::clazz(), $notification);
    }

    public function testFetchOneByWithNoResults()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $user = new User('bob');
        $def = new NotificationDefinition('dat msg', 'dat_group');

        self::$em->persist($user);
        self::$em->persist($def);
        self::$em->flush();
        self::$em->clear();

        $notification = $service->fetchOneBy(array(
            'id' => $def->getId(),
            'recipient' => $user->id,
        ));

        $this->assertNull($notification);
    }

    public function testSave()
    {
        /* @var NotificationService $service*/
        $service = self::$container->get('modera_notification.service.notification_service');

        $fixtures = $this->loadFixtures();

        /* @var UserNotificationInstance $notification */
        $notification = $fixtures['instances'][0];
        $notification->setStatus(12345);

        $service->save($notification);

        self::$em->clear();

        $instanceFromDb = self::$em->find(UserNotificationInstance::clazz(), $notification->getId());
        $this->assertEquals(12345, $instanceFromDb->getStatus());
    }
}
