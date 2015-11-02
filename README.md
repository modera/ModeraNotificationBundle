# ModeraNotificationBundle

Provides functionality to store/retrieve notifications.

## Installation

Add this dependency to your composer.json:

    "modera/notification-bundle": "dev-master"

Update your AppKernel class and add this:

    new \Modera\NotificationBundle\ModeraNotificationBundle(),

If bundle is used in conjunction with Modera Foundation then no additional configuration is needed. If you
are using plain Symfony, then you need to configure Doctrine ORM and specify what implementation of
`Symfony\Component\Security\Core\User\UserInterface` you are using. For example, if you have a entity
Acme/AppBundle/Entity/User which happen to implement UserInterface, then you could add following to `config.yml` file
to make ModeraNotificationBundle bundle work:

    doctrine:
        orm:
            resolve_target_entities:
                Symfony\Component\Security\Core\User\UserInterface: Acme/AppBundle/Entity/User

## Documentation

When bundle is installed you can use `modera_notification.service.notification_service` service to
manipulate notifications.

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
