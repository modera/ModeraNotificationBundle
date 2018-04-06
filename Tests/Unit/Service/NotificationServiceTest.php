<?php

namespace Modera\NotificationBundle\Tests\Unit\Service;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Service\NotificationService;
use Modera\NotificationBundle\Transport\UID;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class NotificationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NotificationService
     */
    private $ns;

    private $registryMock;

    private $managerMock;

    private $queryMock;

    private $userMock;

    public function setUp()
    {
        $this->registryMock = \Phake::mock(RegistryInterface::class);
        $this->managerMock = \Phake::mock(EntityManager::class);
        $this->queryMock = \Phake::mock(MockQuery::class);
        $this->userMock = \Phake::mock(UserInterface::class);

        \Phake::when($this->registryMock)
            ->getManager()
            ->thenReturn($this->managerMock)
        ;

        \Phake::when($this->managerMock)
            ->createQuery(\Phake::anyParameters())
            ->thenReturn($this->queryMock)
        ;

        $this->ns = \Phake::partialMock(NotificationService::class, $this->registryMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchOneByUIDAndRecipient_generalized()
    {
        $uid = UID::parse('foo');

        $this->ns->fetchOneByUIDAndRecipient($uid, $this->userMock);
    }

    public function testFetchOneByUIDAndRecipient_userSpecific()
    {
        \Phake::when($this->queryMock)
            ->getResult(\Phake::anyParameters())
            ->thenReturn(['foo-notification'])
        ;

        $uid = UID::parse('foo:1234');

        $this->assertEquals('foo-notification', $this->ns->fetchOneByUIDAndRecipient($uid, $this->userMock));

        \Phake::verify($this->ns)->fetchOneBy(
            array('recipient' => $this->userMock, 'definition' => '1234'),
            AbstractQuery::HYDRATE_OBJECT
        );
    }

    public function testFetchOneByUIDAndRecipient()
    {
        \Phake::when($this->queryMock)
            ->getResult(\Phake::anyParameters())
            ->thenReturn(['foo-notification'])
        ;

        $uid = UID::parse('foo:1234:true');

        $this->assertEquals('foo-notification', $this->ns->fetchOneByUIDAndRecipient($uid, $this->userMock));

        \Phake::verify($this->ns)->fetchOneBy(
            array('id' => '1234'),
            AbstractQuery::HYDRATE_OBJECT
        );
    }

    public function testChangeStatusByUIDAndRecipient_generalized()
    {
        \Phake::when($this->queryMock)
            ->getResult(\Phake::anyParameters())
            ->thenReturn([array('id' => 'foo-notification', 'readAt' => null)])
        ;

        $uid = UID::parse('foo');
        $this->ns->changeStatusByUIDAndRecipient(UserNotificationInstance::STATUS_READ, $uid, $this->userMock);

        \Phake::verify($this->ns)->changeStatus(
            UserNotificationInstance::STATUS_READ,
            array('recipient' => $this->userMock, 'group' => 'foo')
        );
    }

    public function testChangeStatusByUIDAndRecipient()
    {
        \Phake::when($this->queryMock)
            ->getResult(\Phake::anyParameters())
            ->thenReturn([array('id' => 'foo-notification', 'readAt' => null)])
        ;

        $uid = UID::parse('foo:1234:true');
        $this->ns->changeStatusByUIDAndRecipient(UserNotificationInstance::STATUS_READ, $uid, $this->userMock);

        \Phake::verify($this->ns)->changeStatus(
            UserNotificationInstance::STATUS_READ,
            array('recipient' => $this->userMock, 'id' => '1234')
        );
    }
}

class MockQuery extends AbstractQuery
{
    public function getSQL()
    {
        // mock
    }

    protected function _doExecute()
    {
        // mock
    }
}