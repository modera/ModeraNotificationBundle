<?php

namespace Modera\NotificationBundle\Channels;

use Modera\NotificationBundle\Dispatching\AbstractChannel;
use Modera\NotificationBundle\Dispatching\DeliveryReport;
use Modera\NotificationBundle\Dispatching\NotificationBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A sample channel which is used in documentation in a section describing how to create a custom channel.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class MonologChannel extends AbstractChannel
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'monolog';
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(NotificationBuilder $builder, DeliveryReport $report)
    {
        $usernames = [];
        foreach ($builder->getRecipients() as $user) {
            /* @var UserInterface $user */
            $usernames[] = $user->getUsername();
        }

        try {
            $message = sprintf(
                'Notification with contents "%s" dispatched for %d users: %s.',
                $builder->getMessage(),
                count($usernames),
                implode(', ', $usernames)
            );

            $this->logger->info($message, $builder->getMeta());

            $report->markDelivered($this);
        } catch (\Exception $e) {
            $report->markFailed($this, $e->getMessage());
        }
    }
}
