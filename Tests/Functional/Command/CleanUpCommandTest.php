<?php

namespace Modera\NotificationBundle\Tests\Functional\Command;

use Modera\NotificationBundle\Command\CleanUpCommand;
use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Model\NotificationInterface;
use Modera\NotificationBundle\Service\NotificationService;
use Modera\NotificationBundle\Tests\Functional\AbstractDatabaseTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Modera\NotificationBundle\Tests\Fixtures\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class CleanUpCommandTest extends AbstractDatabaseTest
{
    private function getEntitiesCount($entityClass)
    {
        return $this->em()->createQuery(sprintf('SELECT COUNT(e.id) FROM %s e', $entityClass))->getSingleScalarResult();
    }

    /**
     * @param User $user
     *
     * @return UserNotificationInstance
     */
    private function findLastNotificationByUser(User $user)
    {
        $query = $this->em()->createQuery(sprintf(
            'SELECT e FROM %s e WHERE e.recipient = ?0 ORDER BY e.id DESC', UserNotificationInstance::clazz()
        ));
        $query->setParameter(0, $user);
        $query->setMaxResults(1);

        return $query->getSingleResult();
    }

    private function changeNotificationStatus($id, $newStatus)
    {
        $query = $this->em()->createQuery(sprintf(
            'UPDATE %s e SET e.status = ?0 WHERE e.id = ?1', UserNotificationInstance::clazz()
        ));
        $query->execute([$newStatus, $id]);
    }

    public function testExecute()
    {
        $app = new Application(self::$kernel);
        $app->add(new CleanUpCommand());

        $command = $app->find('modera:notification:clean-up');
        $this->assertNotNull($command);

        $tester = new CommandTester($command);
        $tester->execute(array());

        $this->assertContains('nothing to clean up', $tester->getDisplay());

        // and now with some data:

        $bob = new User('bob');
        $mike = new User('mike');
        $this->em()->persist($bob);
        $this->em()->persist($mike);
        $this->em()->flush();

        /* @var NotificationService $ns */
        $ns = self::$container->get('modera_notification.service.notification_service');

        $lastId = null;
        for ($i = 0; $i < 35; ++$i) {
            $time = time();

            $ns->dispatch('foogroup'.$time, 'barmsg'.$time, [$bob, $mike]);
        }

        $this->assertEquals(35, $this->getEntitiesCount(NotificationDefinition::clazz()));
        $this->assertEquals(70, $this->getEntitiesCount(UserNotificationInstance::clazz()));

        $query = $this->em()->createQuery(sprintf('UPDATE %s e SET e.status = ?0', UserNotificationInstance::clazz()));
        $query->execute([NotificationInterface::STATUS_READ]);

        // this notification should not be deleted because it has NOT_READ status
        $mikesLastNotification = $this->findLastNotificationByUser($mike);
        $this->changeNotificationStatus($mikesLastNotification->getId(), NotificationInterface::STATUS_NOT_READ);

        $tester->execute(array());

        $this->assertContains('Success', $tester->getDisplay());
        $this->assertContains('69', $tester->getDisplay());
        $this->assertEquals(1, $this->getEntitiesCount(NotificationDefinition::clazz()));
        $this->assertEquals(1, $this->getEntitiesCount(UserNotificationInstance::clazz()));

        // and now marking the last single notification as read as well:

        $this->changeNotificationStatus($mikesLastNotification->getId(), NotificationInterface::STATUS_READ);

        $tester->execute(array());

        $this->assertContains('Success', $tester->getDisplay());
        $this->assertContains('1', $tester->getDisplay());
        $this->assertEquals(0, $this->getEntitiesCount(NotificationDefinition::clazz()));
        $this->assertEquals(0, $this->getEntitiesCount(UserNotificationInstance::clazz()));
    }
}
