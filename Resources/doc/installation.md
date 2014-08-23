Installation
============

Add the bunde to your `composer.json` file:
```javascript
require: {
    // ...
    "earls/flamingo-command-queue-bundle": "dev-master@dev"
    // ...
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/Earls/FlamingoCommandQueue.git"
    }
]
```

Then run a `composer update`:
```shell
composer.phar update
# OR
composer.phar update earls/flamingo-command-queue-bundle # to only update the bundle
```

Register the bundle with your `kernel`:
```php
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Earls\FlamingoCommandQueueBundle\EarlsFlamingoCommandQueueBundle(),
    // ...
);
```