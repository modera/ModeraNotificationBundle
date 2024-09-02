# ModeraNotificationBundle

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
        $notificationCenter = $this->container->get('modera_notification.dispatching.notification_center');
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
that must be used nor specified them when invoking *dispatch* method. Here's another example how you can send
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

Next method that we have in our invocation chain is *setContextProperty*. While meta-information is stored in persistence
storage with the notification's contents itself, context-properties will only live during this specific request-response
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

Besides *isSuccessful* and *getFailedDeliveries* methods there's another few you can use:

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

Whenever a new notification is dispatched a few records in database are created and when you have a massive deployment
with a lots of users your notifications database eventually might grow and performance might start to degrade. In order
to address this issue the bundle ships *modera:notification:clean-up* console command which you can use to to clean those
notifications which were already marked as read:

    app/console modera:notification:clean-up

### Dispatching a notification

Sometimes during development you might want to emulate a notification being dispatched without writing
required source manually, if this is the case then we got your covered, the bundle provides
*modera:notification:send-notification* command which you can use to dispatch notifications from a console
environment, here is an example:

    app/console modera:notification:send-notification "Hello world" modera_backend_chat_notification_bridge "*"

This is the bare minimum of configuration parameters that you need to pass in order to dispatch a notification, let's
take a look at them more closely:

 * "Hello world" - as you probably already guessed it contains contents of this notification
 * "modera_backend_chat_notification_bridge" - a group name, this value is used to group a similar notifications together,
 please refer to NotificationBuilder::$group for more details
 * "\*" - third parameter accepts a list of user IDs who should receive a given notification, "\*" in this case means
 that notifications will be dispatched to all users found in database. List of user IDs must separated by a coma, for
 example - 1,2,6

Additionally there are two other parameters which we haven't yet shown, it is possible to specify channels which
should be used to dispatch a notification as well as you have a chance to specify meta-keys that will be stored with given
notification. This is an example how you would specify that a notification should only be dispatched using
"email", "sms" channels and contain "sender" meta-key:

    app/console modera:notification:send-notification "Hello world" modera_backend_chat_notification_bridge "*" --channels="email,sms" --meta="sender=john.doe@example.org"

You can specify as many --meta configuration options as you want, the contents of this option is a string separated
by an equal sign (=) where on left side is parameter name and on the right side its value.

## Architecture

The bundle has quite simple and straightforward architecture consisting of three components - a notifications center,
channels and notification objects. The first one you have already seen in previous sections, as reminder, a notification
class is represented by a *Modera\NotificationBundle\Dispatching\NotificationCenter*, this is the class whose API you will
use in your application logic when adding support for delivering notifications. The second that we have slightly mentioned is far
is "channel", which is represented by classes which implement *Modera\NotificationBundle\Dispatching\ChannelInterface*
interface. In a nutshell a notification center when it is coming to delivering notification is responsible only
for mitigating collaboration between channels that a developer has showed a desire to dispatch a notification through and storing
notifications data in database but  all heavy lifting for delivering a notification through different mediums is taken
care of in channels. The third component is notification object itself, which is represented by
*Modera\NotificationBundle\Model\NotificationInterface*, this interface provides very generic and persistence technology
agnostic API for reading notification details, methods *fetchBy* and *fetchOneBy* described in a next section return
instances of this interface.

### Notification center

So far we have used only one method from NotificationCenter - *createNotificationCenter* but in fact there's another few
ones that you can use to query and modify notifications:

 * fetchBy, fetchOneBy - these two methods are used to query a notifications from a database, both methods accepts
 so called "array query" which is in fact a very simplified syntax for building a query by using a simple
 associative array. Please refer to methods API docs to see for a full list configuration properties. These
 methods return instances of NotificationInterface. Example:

    $notifications = $notificationCenter->fetchBy(array('group' => 'test', 'status' => NotificationInterface::STATUS_NOT_READ));

 * changeStatus - method can be used to change status of notifications (mark some of the as READ, for instance). Method
 accepts two parameters - a new status that notification must have and an "array query" describing which notifications
 should be updated. Example:

    $notificationCenter->changeStatus(NotificationInterface::STATUS_READ, array('group' => 'test'));

### Channels & creating a channel

As it was already mentioned in the previous section, channels are the ones who actually are responsible for doing
the heavy lifting and eventually delivering a notification through a specific medium. The best way to understand
how channels work is to create one and in this section we will create a very simple channel which will write
dispatched notifications to Monolog logger.

If you have already read previous sections then probably you remember that a channel is represented by ChannelInterface,
this interface contains only a handful method and usually you still won't want to implement this interface directly but
instead extend *Modera\NotificationBundle\Dispatching\AbstractChannel* which will have some methods implemented for you,
but let's still take a moment and describe what the interface's methods do:

 * getId - must return a unique ID which can be used to identify your channel, value returned by this method you will
 be using when specifying which channels a notification should be delivered through using, for instance,
 *NotificationBuilder*'s method *dispatch*.
 * getAliases - an optional method that you may want to used to give other names to your channel. For example,
 while you may have in *getId* returning "backend", then *getAliases* method could also return something like
 "backend.title" or "backend.desktop*. This feature proves useful when your channels might have some "sub-channels".
 * dispatch(NotificationBuilder $builder, DeliveryReport $report) - this method should contain logic which will
 actually be responsible for delivering the notification, in other words this is the place where heavy lifting
 actually happens. The method accepts two parameters - a notification builder which was used by a developer
 to specify how the notification should be delivered, you can extract required details from the builder to configure
 how a notification must be transported before it gets send through the channel, and second argument - $report
 this is an object which is created internally by *NotificationBuilder::dispatch()* method, you can use this
 object to report if your channel was able to successfully deliver a message or there was a problem, later a developer
 is able to use this object to get feedback about delivery progress.

Now that you know what channel's methods are let's write our custom channel, this is our Monolog channel's implementation
(all comments are intentionally skipped here):

    namespace Modera\NotificationBundle\Channels;

    use Modera\NotificationBundle\Dispatching\AbstractChannel;
    use Modera\NotificationBundle\Dispatching\DeliveryReport;
    use Modera\NotificationBundle\Dispatching\NotificationBuilder;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    class MonologChannel extends AbstractChannel
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function getId()
        {
            return 'monolog';
        }

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

The channel's implementation is pretty much self-explanatory, the only thing that I want you to pay attention is how
we invoke *$report*'s *markDelivered* and *markFailed* method depending if channel has succeeded or not. Once channel
is created there is one more step we need to complete to make our notification center see that there's a new channel
is available. For this to happen we need to create a contribution to **modera_notification.channels** extension-point:

    namespace Modera\NotificationBundle\Contributions;

    use Modera\NotificationBundle\Dispatching\ChannelInterface;
    use Sli\ExpanderBundle\Ext\ContributorInterface;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Modera\NotificationBundle\Channels\MonologChannel;

    class ChannelsProvider implements ContributorInterface
    {
        private $container;

        private $channels;

        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }

        public function getItems(): array
        {
            if (!$this->channels) {
                $this->channels = [
                    new MonologChannel($this->container->get('logger'))
                ];
            }

            return $this->channels;
        }
    }

And register our contributor class in dependency injection container:

    <service id="modera_notification.contributions.channels_provider"
             class="Modera\NotificationBundle\Contributions\ChannelsProvider">

        <argument type="service" id="service_container" />

        <tag name="modera_notification.channels_provider" />
    </service>

As of now if you try to dispatch a new notification then the notification channel should be able to discover
our monolog channel and to use it as well to deliver notifications, you can try it yourself by running in console
something like:

    echo "" > app/logs/dev.log && app/console modera:notification:send-notification "hello test" test_group "*"

And then checking contents of *app/logs/dev.log* file.

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
