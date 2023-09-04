# Transactional outbox

The package is implementation of [transactional outbox pattern](https://microservices.io/patterns/data/transactional-outbox.html) for the project ThirstyX.

Read [this nice explanation](https://phpnews.io/feeditem/reliable-event-dispatching-using-a-transactional-outbox) of the pattern.

## Installation

The package is not yet in Packagist, so you’ll have to make a slight adjustment to your composer.json file.

Open the file and insert the following array somewhere in the object:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/fhsinchy/inspire"
    }
]
```

Now you can use composer to pull in the package.
```sh
composer showers-and-bs/transactional-outbox
```

## Usage

> To be described

