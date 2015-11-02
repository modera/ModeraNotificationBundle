<?php

namespace Modera\NotificationBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Model\NotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Service provides basic routines for manipulating notifications - dispatching(creating), querying,
 * batch changing of notification statuses.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class NotificationService
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Dispatches a notification.
     *
     * @param string $group
     * @param string $message
     * @param array $recipients
     * @param array $meta
     *
     * @return NotificationInterface[]
     */
    public function dispatch($group, $message, array $recipients, array $meta = array())
    {
        $def = new NotificationDefinition($group, $message);
        $def->setMeta($meta);

        $instances = [];
        foreach ($recipients as $user) {
            $instances[] = $def->createInstance($user);
        }

        /* @var EntityManager $em */
        $em = $this->registry->getManager();

        $em->persist($def);
        $em->flush();

        return $instances;
    }

    /**
     * Finds all notifications by given $group/$user and changes their status to $newStatus.
     *
     * @param string $group
     * @param UserInterface $user
     * @param int $newStatus
     */
    public function changeStatusByGroupAndUser($group, UserInterface $user, $newStatus)
    {
        /* @var EntityManager $em */
        $em = $this->registry->getManager();

        $query = sprintf(
            'SELECT inc FROM %s inc LEFT JOIN inc.definition def WHERE def.groupName = ?0 AND inc.recipient = ?1',
            UserNotificationInstance::clazz()
        );
        $query = $em->createQuery($query);
        $query->setParameters([$group, $user]);

        foreach ($query->getResult() as $instance) {
            /* @var UserNotificationInstance $instance*/
            $instance->setStatus($newStatus);
        }

        $em->flush();
    }

    /**
     * Allows to fetch notifications from storage. A sample query may look akin to this:
     *
     * array(
     *     'group' => 'foo_group',
     *     'recipients' => [$user1, $user2] // instances of UserInterface
     * );
     *
     * If none of parameters is provided then all available notifications will be fetched.
     *
     * @param array $arrayQuery
     *
     * @return NotificationInterface[]
     */
    public function fetch(array $arrayQuery)
    {
        /* @var EntityManager $em */
        $em = $this->registry->getManager();

        $querySegments = [
            sprintf('SELECT inc FROM %s inc LEFT JOIN inc.definition def', UserNotificationInstance::clazz())
        ];
        $queryParams = [];

        $hasGroup = isset($arrayQuery['group']);
        $hasRecipients = isset($arrayQuery['recipients']) && is_array($arrayQuery['recipients']) && count($arrayQuery['recipients']) > 0;

        if ($hasGroup || $hasRecipients) {
            $querySegments[] = 'WHERE';

            if ($hasGroup) {
                $querySegments[] = 'def.groupName = ?'.count($queryParams);
                $queryParams[] = $arrayQuery['group'];
            }
            if ($hasRecipients) {
                if ($hasGroup) {
                    $querySegments[] = 'AND';
                }

                $querySegments[] = sprintf('inc.recipient IN (?%d)', count($queryParams));
                $queryParams[] = $arrayQuery['recipients'];
            }
        }

        $query = $em->createQuery(implode(' ', $querySegments));
        $query->setParameters($queryParams);

        return $query->getResult();
    }
}