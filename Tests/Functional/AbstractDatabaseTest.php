<?php

namespace Modera\NotificationBundle\Tests\Functional;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Tests\Fixtures\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
abstract class AbstractDatabaseTest extends FunctionalTestCase
{
    private static $entities = [];
    private static $metaClasses = [];

    /**
     * {@inheritdoc}
     */
    public static function doSetUpBeforeClass()
    {
        self::$entities = [
            NotificationDefinition::class,
            UserNotificationInstance::class,
            User::class,
        ];

        foreach (self::$entities as $className) {
            self::$metaClasses[] = self::$em->getClassMetadata($className);
        }

        $schemaTool = new SchemaTool(self::$em);
        $schemaTool->dropSchema(self::$metaClasses);
        $schemaTool->createSchema(self::$metaClasses);
    }

    /**
     * {@inheritdoc}
     */
    public static function doTearDownAfterClass()
    {
        $schemaTool = new SchemaTool(self::$em);
        $schemaTool->dropSchema(self::$metaClasses);
    }

    /**
     * @return EntityManager
     */
    protected function em()
    {
        return self::$container->get('doctrine.orm.entity_manager');
    }
}
