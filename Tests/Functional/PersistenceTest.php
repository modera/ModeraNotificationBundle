<?php

namespace Modera\NotificationBundle\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Tests\Fixtures\Entity\User;
use Sli\AuxBundle\Util\Toolkit;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class PersistenceTest extends FunctionalTestCase
{
    private static $entities = [
        NotificationDefinition::class,
        UserNotificationInstance::class,
        User::class
    ];
    private static $metaClasses = [];

    /**
     * @inheritdoc
     */
    public static function doSetUpBeforeClass()
    {
        foreach (self::$entities as $className) {
            self::$metaClasses[] = self::$em->getClassMetadata($className);
        }

        $schemaTool = new SchemaTool(self::$em);
        $schemaTool->dropSchema(self::$metaClasses);
        $schemaTool->createSchema(self::$metaClasses);
    }

    /**
     * @inheritdoc
     */
    public static function doTearDownAfterClass()
    {
        $schemaTool = new SchemaTool(self::$em);
        $schemaTool->dropSchema(self::$metaClasses);
    }

    public function testHowWellPersistenceWorks()
    {
        $user = new User();
        $user->username = 'john.doe';

        $definition = new NotificationDefinition('foo');
        $instance1 = $definition->createInstance($user);
        $instance2 = $definition->createInstance($user);

        $this->assertEquals('foo', $definition->getContents());
        $this->assertEquals(2, count($definition->getInstances()));

        self::$em->persist($user);
        self::$em->persist($definition);
        self::$em->flush();
        self::$em->clear();

        $this->assertNotNull($definition->getId());
        $this->assertNotNull($instance1->getId());
        $this->assertNotNull($instance2->getId());

        /* @var NotificationDefinition $definitionFromDb */
        $definitionFromDb = self::$em->find(NotificationDefinition::class, $definition->getId());

        $instancesFromDb = $definitionFromDb->getInstances();

        $this->assertEquals(2, count($instancesFromDb));
        $this->assertEquals($instance1->getId(), $instancesFromDb[0]->getId());
        $this->assertEquals($user->id, $instancesFromDb[0]->getRecipient()->id);
        $this->assertEquals($instance2->getId(), $instancesFromDb[1]->getId());
        $this->assertEquals($user->id, $instancesFromDb[1]->getRecipient()->id);

        self::$em->remove($definitionFromDb);
        self::$em->flush();

        $this->assertNull(self::$em->find(UserNotificationInstance::class, $instance1->getId()));
        $this->assertNull(self::$em->find(UserNotificationInstance::class, $instance2->getId()));
    }
}