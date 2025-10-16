<?php

namespace Modera\NotificationBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Entity\NotificationDefinition;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class CleanUpCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('modera:notification:clean-up')
            ->setDescription('Allows to clean up a database from notifications which not needed any more.')
            ->addOption('only-expired', null, InputOption::VALUE_NONE)
            ->addOption('no-lifetime-expiry-days', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime('now');

        /** @var bool $onlyExpired */
        $onlyExpired = $input->getOption('only-expired');

        /** @var ?string $noLifetimeExpiryDays */
        $noLifetimeExpiryDays = $input->getOption('no-lifetime-expiry-days') ?: null;

        $removed = (int) $this->em()->createQuery(sprintf(
            implode(' ', array(
                'SELECT COUNT(ni.id) FROM %s n',
                'LEFT JOIN %s ni WITH ni.definition = n.id',
                'WHERE n.lifetime IS NOT NULL AND n.lifetime <= :date',
            )),
            NotificationDefinition::class,
            UserNotificationInstance::class
        ))->setParameter('date', $now)->getSingleScalarResult();

        $removedDefinitions = $this->em()->createQuery(sprintf(
            'DELETE FROM %s n WHERE n.lifetime IS NOT NULL AND n.lifetime <= :date',
            NotificationDefinition::class
        ))->setParameter('date', $now)->execute();

        if (intval($noLifetimeExpiryDays) > 0) {
            $removed += (int) $this->em()->createQuery(sprintf(
                implode(' ', array(
                    'SELECT COUNT(ni.id) FROM %s n',
                    'LEFT JOIN %s ni WITH ni.definition = n.id',
                    'WHERE n.lifetime IS NULL AND DATE_ADD(n.createdAt, %s, \'DAY\') <= :date',
                )),
                NotificationDefinition::class,
                UserNotificationInstance::class,
                $noLifetimeExpiryDays
            ))->setParameter('date', $now)->getSingleScalarResult();

            $removedDefinitions += $this->em()->createQuery(sprintf(
                'DELETE FROM %s n WHERE n.lifetime IS NULL AND DATE_ADD(n.createdAt, %s, \'DAY\') <= :date',
                NotificationDefinition::class,
                $noLifetimeExpiryDays
            ))->setParameter('date', $now)->execute();
        }

        if (!$onlyExpired) {
            $removed += $this->em()->createQuery(sprintf(
                'DELETE FROM %s ni WHERE ni.status = :status',
                UserNotificationInstance::class
            ))->setParameter('status', UserNotificationInstance::STATUS_READ)->execute();
        }

        $arr = $this->em()->createQuery(sprintf(
            'SELECT n.id FROM %s n LEFT JOIN %s ni WITH ni.definition = n.id GROUP BY n.id HAVING COUNT(ni.id) = 0',
            NotificationDefinition::class,
            UserNotificationInstance::class
        ))->getScalarResult();

        foreach (array_chunk(array_column($arr, 'id'), 1000) as $chunk) {
            $removedDefinitions += $this->em()->createQuery(sprintf(
                'DELETE FROM %s n WHERE n.id IN (%s)',
                NotificationDefinition::class,
                implode(', ', $chunk)
            ))->execute();
        }

        if (!$removed && !$removedDefinitions) {
            $output->writeln('<comment>Nothing to clean up, aborting.</comment>');
        } else {
            $output->writeln(sprintf(
                implode(' ', array(
                    '<info>Success!</info>',
                    'Removed:',
                    '<comment>%d</comment> notification definition(s),',
                    '<comment>%d</comment> user notification instance(s).',
                )),
                $removedDefinitions,
                $removed
            ));
        }

        return 0;
    }

    /**
     * @return EntityManager
     */
    protected function em()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
