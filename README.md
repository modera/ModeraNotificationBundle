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

The bundle provides extensible architecture with a simple high-level API which can be used by developers to integrate
user notifications support in their own applications. Extensibility is achieved by using so called "channels" which are
used to consolidate logic necessary for delivering a notification through a specific medium - for example a channel could
be responsible for delivering a piece of text by sending SMS message or even using Push notification for mobile device,
the bundle provides a set of very intuitive APIs which developers can use to create their own channels.

The first thing you deal with when you want to add a support of delivering notifications to your application is to
use a notification center which is represented by an instance of *Modera\NotificationBundle\Dispatching\NotificationCenter*
class, most of the time though you won't want to create its instance manually but instead use
**modera_notification.dispatching.notification_center** Dependency Injection service. For example, in order to get a
notification service from a controller you can do something like that:

    public function indexAction()
    {
        $notificationCenter = $this->get('modera_notification.dispatching.notification_center');
    }

In a nutshell, notification center provides a high-level API which you will work with when configuring and sending
notifications from your application code, some of the operations that you can achieve by using notification center are:

 * sending out notifications for specific channels, users and monitoring its delivery state
 * querying notifications
 * changing notification state (marking them as read)

### Sending notifications

As it was already mentioned in order to send a notification you need to use a notification center and rely on its
very simply and high-level API. When sending a notification you can configure several things:

 * which channels a notification should be delivered through
 * who should receive a notification
 * what optional metadata you want to associate with a notification

The first thing you need to do when you want to send a notification is to create so called "notification builder",
it is an object which provides a set of methods which you can use to tweak things that were mentioned in the list above.
This is what you need to do in order to send your first notification:

    $user = $em->getRepository(User::class)->findOneBy(array('username' => 'foo_user'));

    $notificationCenter->createNotificationBuilder('hello world', 'test-group');
        ->addRecipient($user)
        ->dispatch()
    ;

When you invoke a *createNotificationBuilder* under the hood the notification center will create an instance
of *Modera\NotificationBundle\Dispatching\NotificationBuilder* linked with the given notification center which provides
fluent-interface methods that you can chain to configure your notification. First argument of the method contains a piece
of text that this notification is about and second one - a group name (please refer to NotificationBuilder class
properties in order to see full description of what "group" parameter is used for). In this case once an instance
of notification builder is created the last thing we need to do in order to send-out our notification is to specify
who should receive it, for this you can use either *addRecipient* or *setRecipients* methods, when invoking them
you need to provide implementations of UserInterface which we talked about in "Installation" section of this guide. Once
you have configured your notification you can invoke *dispatch* method and notification will be attempted to be delivered
through all registered channels because we neither used *setChannels* method which accepts list of channel IDs
that must be used nor specified then when invoking *dispatch* method. Here's another example how you can send
a notification while specifying which channels should be used:

    $notificationCenter->createNotificationBuilder('hello world', 'test-group');
        ->setRecipients([$bob, $jane])
        ->dispatch(['push', 'email'])
    ;

### Getting more control over notifications sending

So far we have taken a look at very simply set of methods that you can use when sending notifications, at times though
you may want to get som extra more control over how notification is being sent, what optional meta-information
it contains and after it has been sent you may want to see if there were any problems while delivering it. Here's
more advanced example:

    $notificationCenter->createNotificationBuilder('hello world', 'test-group');
        ->setMetaProperty('sent_by', 'vasya@example.org')
        ->setContextProperty('sms_api_key', 12345)
        ->throwExceptionWhenChannelNotFound()
        ->setRecipients([$bob, $jane])
        ->dispatch(['push', 'sms', 'email'])
    ;

Sometimes after a notification is delivered to a user and user wants to perform some action on it there is a need
to store some additional normalized information with the notification that handling logic can used to get better
understanding what actions should be taken when user has acknowledged a notification - for this purpose you have
two methods at your disposal - *setMeta* and *setMetaProperty*. First method allows you to override all available
meta information stored in a specific notification-builder while second one - *setMetaProperty* can be used
to specify one meta-property at time.

Next method that we in our invocation chain is *setContextProperty*. While meta-information is stored in persistence
storage with the notification's contents itself, context-properties will only during this specific request-response
server cycle. Usually you may want to use context properties to tweak a specific notification channel configuration
properties that affect how a notification is delivered, but once a notification is delivered these details can be
discarded.

Last method *throwExceptionWhenChannelNotFound* can be used to instruct the notification center that if some of the
specified channels are not found then exception must be thrown - default behaviour is the opposite, if some channels
are not found they simply will be ignored. Once *throwExceptionWhenChannelNotFound* has been invoked you can use
*suppressChannelNotFoundException* to revert it back to default behaviour.

### Controlling notification dispatch status

What we haven't yet talked about is that it is possible to figure out if a notification has been successfully
delivered or what channels could have possibly failed and with what error. When you dispatch a notification
using *dispatch* method on *NotificationBuilder* then an instance of *Modera\NotificationBundle\Dispatching\DeliveryReport*
is returned which you can use to get dispatching status. This is an example showing some of the methods that you
can use:

    $report = $notificationCenter->createNotificationBuilder('hello world', 'test-group');
        // ...
        ->dispatch()
    ;

    if ($report->isSuccessful()) {
        echo "Everything's good, it seems all channel managed to deliver a notification.";
    } else {
        foreach ($report->getFailedDeliveries() as $info) {
            echo sprintf("%s: failed to deliver a notification, error: ", $info['channel']->getId(), $info['error']);
            echo $info['meta'] ? print_r($info['meta'], true) : 'No meta-information provided';
            echo "\n";
        }
    }

Besides *isSuccessful* and *getFailedDeliveries* method there's another few you can use

 * isFailed() - opposite of *isSuccessful*, will return TRUE is any of the channels deemed that it was unable
  to deliver a notification
 * getSuccessfulDeliveries() - use this method to get a list of channels which were able to successfully deliver
 a notification. Each element of returned array from this method is an array containing three keys: *channel* (instance of
 ChannelInterface), message (an optional message specified by channel), meta (denormalized meta that can be used
 by application logic to gain better understanding of dispatching process)

For a full list of available methods please refer to DeliveryReport class.

## Command line

Bundle provides several command line commands that might prove useful as in development as well as in production
environment.

### Cleaning up old notifications

Whenever a new notification is dispatched a few records in database is created and when you have a big
deployment ...


## Architecture

### Creating a custom channel

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
