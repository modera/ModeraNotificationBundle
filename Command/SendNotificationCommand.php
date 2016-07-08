<?php

namespace Modera\NotificationBundle\Command;

use Modera\NotificationBundle\Dispatching\NotificationCenter;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class SendNotificationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('modera:notification:send-notification')
            ->addArgument('message', InputArgument::REQUIRED)
            ->addArgument('group', InputArgument::REQUIRED)
            ->addArgument('recipients', InputArgument::REQUIRED, 'IDs of user who to dispatch notification to, if * is provided the notification will be delivered to all users')
            ->addOption('channels', null, InputOption::VALUE_OPTIONAL, 'Channels to use to dispatch the notification, comma separated')
            ->addOption('meta', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_OPTIONAL)
            ->setDescription('Allows to dispatch a notification right from console')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var NotificationCenter $center */
        $center = $this->getContainer()->get('modera_notification.dispatching.notification_center');
        /* @var RegistryInterface $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $userRepository = $doctrine->getManagerForClass(User::class)->getRepository(User::class);

        $channels = $input->getOption('channels');
        $channels = $channels ? explode(',', $channels) : [];

        $meta = [];
        foreach ($input->getOption('meta') as $metaProperty) {
            if (false === strpos($metaProperty, '=')) {
                continue;
            }

            list ($key, $value) = explode('=', $metaProperty);
            $meta[$key] = $value;
        }

        $recipients = [];
        $recipientsArg = $input->getArgument('recipients');
        if ('*' == $recipientsArg) {
            $recipients = $userRepository->findAll();
        } else {
            $recipients = $userRepository->findBy(array(
                'id' => explode(', ', $recipientsArg)
            ));
        }

        $report = $center->createNotificationBuilder($input->getArgument('message'), $input->getArgument('group'))
            ->setRecipients($recipients)
            ->setMeta($meta)
            ->dispatch($channels)
        ;

        if ($report->isSuccessful()) {
            $output->writeln('<info>Great success!</info> Delivered through channels:');

            foreach ($report->getSuccessfulDeliveries() as $report) {
                $message = $report['message'] ? $report['message'] : 'no details provided';

                $output->writeln(sprintf('- %s: %s', $report['channel']->getId(), $message));
            }
        } else {
            $output->writeln('<error>Something went wrong during notification delivery.</error>');

            foreach ($report->getErrors() as $errorData) {
                $message = $errorData['error'] ? $errorData['error'] : 'no details provided';

                $output->writeln(sprintf(' - %s: %s', $errorData['channel']->getId(), $message));
            }

            return 1;
        }
    }
}
