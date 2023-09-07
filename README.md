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

> To be described


## For local development

Create folder named **packages** in the same level where resides microservice folders.

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
