<?php

namespace Modera\NotificationBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Model\NotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Service provides basic routines for manipulating notifications - dispatching(creating), querying, batch changing
 * of notification statuses.
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
     * @param UserInterface[] $recipients
     * @param array $meta
     *
     * @return mixed A notification ID
     */
    public function dispatch($group, $message, array $recipients, array $meta = array())
    {
        $def = new NotificationDefinition($message, $group);
        $def->setMeta($meta);

        foreach ($recipients as $user) {
            $def->createInstance($user);
        }

        /* @var EntityManager $em */
        $em = $this->registry->getManager();

        $em->persist($def);
        $em->flush();

        return $def->getId();
    }

    /**
     * Finds all notifications by given $group/$user and changes their status to $newStatus.
     *
     * Possible array query keys are: recipient (instance of UserInterface), group (string), id(int).
     * By combining different keys you are able to change scope of notifications whose statuses are going
     * to be updated.
     *
     * @param int $newStatus
     * @param array $arrayQuery
     */
    public function changeStatus($newStatus, array $arrayQuery)
    {
        /* @var EntityManager $em */
        $em = $this->registry->getManager();

        $querySegments = [
            sprintf('SELECT inc FROM %s inc LEFT JOIN inc.definition def', UserNotificationInstance::clazz())
        ];
        $queryParams = [];

        $hasId = isset($arrayQuery['id']);
        $hasRecipient = isset($arrayQuery['recipient']);
        $hasGroup = isset($arrayQuery['group']);
        $hasQuery = $hasId || $hasRecipient || $hasGroup;

        if ($hasQuery) {
            $querySegments[] = 'WHERE ';
        }

        $filters = [];
        if ($hasId) {
            $filters[] = 'def.id = ?'.count($queryParams);
            $queryParams[] = $arrayQuery['id'];
        }
        if ($hasRecipient) {
            $filters[] = 'inc.recipient = ?'.count($queryParams);
            $queryParams[] = $arrayQuery['recipient'];
        }
        if ($hasGroup) {
            $filters[] = 'def.groupName = ?'.count($queryParams);
            $queryParams[] = $arrayQuery['group'];
        }

        $query = implode(' ', $querySegments).implode(' AND ', $filters);
        $query = $em->createQuery($query);
        $query->setParameters($queryParams);

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
    public function fetchBy(array $arrayQuery)
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

    /**
     * Available query keys: ID (int), recipient (UserInterface).
     *
     * @throws \RuntimeException If more than one result is returned from persistence storage.
     *
     * @param array $arrayQuery
     *
     * @return NotificationInterface|null NULL is returned when no notification is found
     */
    public function fetchOneBy(array $arrayQuery)
    {
        /* @var EntityManager $em */
        $em = $this->registry->getManager();

        $hasId = isset($arrayQuery['id']);
        $hasRecipient = isset($arrayQuery['recipient']);

        $querySegments = [
            sprintf('SELECT inc FROM %s inc LEFT JOIN inc.definition def', UserNotificationInstance::clazz())
        ];
        $queryParams = [];

        if ($hasId || $hasRecipient) {
            $querySegments[] = 'WHERE';
        }

        if ($hasId) {
            $querySegments[] = 'def.id = ?'.count($queryParams);
            $queryParams[] = $arrayQuery['id'];
        }
        if ($hasRecipient) {
            if ($hasId) {
                $querySegments[] = 'AND';
            }

            $querySegments[] = sprintf('inc.recipient IN (?%d)', count($queryParams));
            $queryParams[] = [$arrayQuery['recipient']];
        }

        $query = $em->createQuery(implode(' ', $querySegments));
        $query->setParameters($queryParams);

        $result = $query->getResult();
        if (count($result) > 1) {
            throw new \RuntimeException('More than one notification returned for query: '.json_encode($arrayQuery));
        } else if (count($result) == 0) {
            return;
        }

        return $result[0];
    }

    /**
     * Saves/updates notification.
     *
     * @param object object
     */
    public function save($notification)
    {
        /* @var EntityManager $em */
        $em = $this->registry->getManager();

        $em->persist($notification);
        $em->flush();
    }
}
