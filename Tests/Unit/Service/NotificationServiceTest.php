<?php

namespace Modera\NotificationBundle\Tests\Unit\Service;
use Doctrine\ORM\EntityRepository;
use Modera\NotificationBundle\Entity\UserNotificationInstance;
use Modera\NotificationBundle\Service\NotificationService;
use Modera\NotificationBundle\Transport\UID;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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

    private $repositoryMock;

    private $userMock;

    public function setUp()
    {
        $this->repositoryMock = \Phake::mock(EntityRepository::class);
        $this->registryMock = \Phake::mock(RegistryInterface::class);
        $this->userMock = \Phake::mock(UserInterface::class);

        \Phake::when($this->registryMock)
            ->getRepository(UserNotificationInstance::class)
            ->thenReturn($this->repositoryMock)
        ;

        $this->ns = new NotificationService($this->registryMock);
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
        \Phake::when($this->repositoryMock)
            ->findOneBy($this->anything())
            ->thenReturn('foo-notification')
        ;

        $uid = UID::parse('foo:1234');

        $this->assertEquals(
            'foo-notification',
            $this->ns->fetchOneByUIDAndRecipient($uid, $this->userMock)
        );

        \Phake::verify($this->repositoryMock)
            ->findOneBy(array('recipient' => $this->userMock, 'definition' => '1234'))
        ;
    }

    public function testFetchOneByUIDAndRecipient()
    {
        \Phake::when($this->repositoryMock)
            ->find($this->anything())
            ->thenReturn('foo-notification')
        ;

        $uid = UID::parse('foo:1234:true');

        $this->assertEquals(
            'foo-notification',
            $this->ns->fetchOneByUIDAndRecipient($uid, $this->userMock)
        );

        \Phake::verify($this->repositoryMock)
            ->find('1234')
        ;
    }
}