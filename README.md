# Transactional outbox

The package is implementation of [transactional outbox pattern](https://microservices.io/patterns/data/transactional-outbox.html) for the project ThirstyX.

Read [this nice explanation](https://phpnews.io/feeditem/reliable-event-dispatching-using-a-transactional-outbox) of the pattern.

## Installation

The package intended only for the project ThirstyX, it is not in Packagist.

Add path to Github repository to your composer.json file.

```json
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:showers-and-bs/transactional-outbox.git"
        }
    ],
```
Now run composer require to pull in the package.

```sh
composer require showers-and-bs/transactional-outbox
```
## Usage

Before use, read about the problem [here](https://softwaremill.com/microservices-101/#transactional-outbox).

How to use the package in your Laravel application?

Run migrations to create table **outgoing_messages**.

```sh
php artisan migration
```

When something happens that you want to share with the world i.e interested microservices, you just need to dispatch the "publishable event". It is an [event class](https://laravel.com/docs/10.x/events#defining-events) that implements **ShouldBePublished** interface exposed by the package [**showers-and-bs/thirsty-events**](https://github.com/showers-and-bs/thirsty-events). The package listens for publishable events and handles them in [**ShouldBePublishedListener@handle**](https://github.com/showers-and-bs/transactional-outbox/blob/master/src/Listeners/ShouldBePublishedListener.php) method. As pattern describes, in the first step we should store messages that are intended for delivery to our message outbox (in our case the table name is **outgoing_messages**) and that exactly is what the package do. So dispatching publishable event can be and should be part of a database transaction together with database operations that precede to event dispatching.

In the second step, when the message is finally stored in the database, the package relays them to the message broker. To run [the message relay deamon](https://github.com/showers-and-bs/transactional-outbox/blob/master/src/Console/Commands/MessageRelay.php), execute the following command.

```sh
php artisan amqp:relay
```

## Console commands

There is a bunch of console commands that should help development and debugging.

Here are examples of use.

```sh
php artisan amqp:outbox --help # to list all available options
php artisan amqp:outbox # list the last 10 messages
php artisan amqp:outbox --limit=30 # list the last 30 messages
php artisan amqp:outbox --no-limit # list all the messages
php artisan amqp:outbox --no-limit --status=3 # list all the messages with the status 3 (PUBLISHED)
php artisan amqp:outbox --no-limit --event=UserLogin # list all the messages for the event UserLogin
php artisan amqp:outbox --id=7 # display details for the message with id=7
php artisan amqp:outbox --event-id=a12f907c-115b-4eb4-ad20-140e547b98c6 # display details for the message with event id =a12f907c-115b-4eb4-ad20-140e547b98c6
php artisan amqp:outbox --id=7 --resend # change status of the message with id=7 to PENDING and that way give signal to the message relay to send the message
```

## Guide for package development

Create folder named **packages** in the same level where reside microservice applications.

Get into it and run `git clone git@github.com:showers-and-bs/transactional-outbox.git`.

The folder structure should look like this:

<pre>
<code>...
&#9500;&#9472;&#9472; packages
&#9474;   &#9492;&#9472;&#9472; transactional-outbox
&#9474;       &#9492;&#9472;&#9472; composer.json
&#9500;&#9472;&#9472; content-service
&#9474;   &#9492;&#9472;&#9472; composer.json
&#9500;&#9472;&#9472; member-service
&#9474;   &#9492;&#9472;&#9472; composer.json
...</code>
</pre>

Now get into the folder `vendor/showers-and-bs`, delete folder `transactional-outbox` and crate symlink to the folder `packages/transactional-outbox`.

```sh
ln -s ../../../packages/transactional-outbox/ ./transactional-outbox
```

Happy coding!
