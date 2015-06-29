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

Database
========

First you have to run `php app/console doctrine:schema:update --dump-sql` and create your tables `flg` from it

3 tables are needed : FlgScript, FlgScriptInstanceLog and FlgScriptRunningInstance.

###TIPS
  If you don't see anything after doctrine:schema:update command, check if 
  FlamingoCommandQueueBundle is a part of your dcctrine mappings in config.yml.

