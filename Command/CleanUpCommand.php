<?php

namespace Modera\NotificationBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Modera\NotificationBundle\Entity\NotificationDefinition;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Model\NotificationInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class CleanUpCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('modera:notification:clean-up')
            ->setDescription('Allows to clean up a database from notifications which have READ status.')
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'How many notifications to fetch from a database at a time.',
                100
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // By fetching and hydrating entities we guarantee that ORM
        // listeners will be invoked as well (if any)

        /* @var Registry $registry*/
        $registry = $this->getContainer()->get('doctrine');
        /* @var EntityManager $em */
        $em = $registry->getManager();

        $batchSize = $input->getOption('batch-size');

        $definitionsIds = [];
        $removedCount = 0;

        $instances = [];
        do {
            $query = $em->createQuery(sprintf(
                'SELECT def, ni FROM %s ni LEFT JOIN ni.definition def WHERE ni.status = ?0 ORDER BY ni.definition ASC',
                UserNotificationInstance::clazz()
            ));
            $query->setParameter(0, NotificationInterface::STATUS_READ);
            $query->setMaxResults($batchSize);

            /* @var UserNotificationInstance[] $instances */
            $instances = $query->getResult();

            foreach ($instances as $instance) {
                $em->remove($instance);

                $definitionsIds[] = $instance->getDefinition()->getId();
            }
            $em->flush();
            $em->clear();

            $removedCount += count($instances);
        } while (count($instances) == $batchSize);

        $definitions = [];
        do {
            $definitionIdsToRemove = []; // those which by now should have UserNotificationInstance associated
            foreach ($definitionsIds as $id) {
                $query = $em->createQuery(sprintf(
                    'SELECT COUNT(ni.id) FROM %s ni WHERE ni.definition = ?0', UserNotificationInstance::clazz()
                ));
                $query->setParameter(0, $id);
                $query->setMaxResults($batchSize);

                $noAssociatedNotificationsAvailable = $query->getSingleScalarResult() == 0;
                if ($noAssociatedNotificationsAvailable) {
                    $definitionIdsToRemove[] = $id;
                }
            }

            $definitions = $em->getRepository(NotificationDefinition::clazz())->findBy(array(
                'id' => $definitionIdsToRemove,
            ));

            foreach ($definitions as $definition) {
                $em->remove($definition);
            }
            $em->flush();
            $em->clear();
        } while (count($definitions) == $batchSize);

        if ($removedCount > 0) {
            $output->writeln(sprintf(
                ' <info>Success!</info> In total %d notifications with status READ were removed.', $removedCount
            ));
        } else {
            $output->writeln(' <comment>No notifications with status READ were found, nothing to clean up, aborting.</comment>');
        }
    }
}
