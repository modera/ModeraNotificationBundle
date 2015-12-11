# ModeraNotificationBundle
[![Build Status](https://travis-ci.org/modera/ModeraNotificationBundle.svg?branch=master)](https://travis-ci.org/modera/ModeraNotificationBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/modera/ModeraNotificationBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/modera/ModeraNotificationBundle/?branch=master)
[![StyleCI](https://styleci.io/repos/45386852/shield)](https://styleci.io/repos/45386852)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2e7b46b0-ffe5-46fc-807f-63b8f4b9ad2d/mini.png)](https://insight.sensiolabs.com/projects/2e7b46b0-ffe5-46fc-807f-63b8f4b9ad2d)

Provides functionality to store/retrieve notifications.

## Installation

Add this dependency to your composer.json:

    "modera/notification-bundle": "dev-master"

If bundle is used in conjunction with Modera Foundation then no additional configuration is needed. If you
are using plain Symfony, then you need to configure Doctrine ORM and specify what implementation of
`Symfony\Component\Security\Core\User\UserInterface` you are using. For example, if you have a entity
Acme/AppBundle/Entity/User which happen to implement UserInterface, then you could add following to `config.yml` file
to make ModeraNotificationBundle bundle work:

    doctrine:
        orm:
            resolve_target_entities:
                Symfony\Component\Security\Core\User\UserInterface: Acme/AppBundle/Entity/User

Also, don't forget to update your AppKernel class and add ModeraNotificationBundle there:

    new \Modera\NotificationBundle\ModeraNotificationBundle(),

And finally update database:

    app/console doctrine:schema:update --force

## Documentation

When bundle is installed you can use `modera_notification.service.notification_service` service to
manipulate notifications.

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
