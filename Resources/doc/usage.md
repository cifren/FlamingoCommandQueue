Usage Documentation
===================

Next it is a very simple process, the only you have to do is to call the start function when you start your script and stop function when your script is done.

Log Command
===========

Your command :

```php
<?php

namespace Earls\OxPeckerDataBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class RunCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();
        $this
                ->setName('oxpecker:run')
                ->setDescription('Run your data tier config, generate easily your data for report or data center or import')
                ->addArgument('namedatatier', InputArgument::REQUIRED, 'Which data tier config do you want execute')
                ->addArgument('args', InputArgument::IS_ARRAY, 'Add all arguments this command needs');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $cmdManager = $this->getContainer()->get('flamingo.manager.command');

        $cmdManager->start($name);

        //******
        //your scripts, business logic
        //******

        $cmdManager->stop($this->getLogs());
    }

    protected function getLogs()
    {
        //this class come from symfony2 by default
        return $this->getContainer()->get('logger')->getLogs();
    }
}

```

Here the command manager will run and save all data in the Table. The logs will be store 
into an array in the entity `FlgScriptInstanceLog`.

Why an array ? Because like this, you can retrieve all level of notification from 
symfony2, you could use into your command from 
[logger](http://symfony.com/fr/doc/current/cookbook/logging/monolog.html).

Queue
=====

You can line up a command in the entity/table `FlgScriptRunningInstance`, the thing 
you have to do is to declare a name of group.

```php 
<?php
class RunCommand extends ContainerAwareCommand
{

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $cmdManager = $this->getContainer()->get('flamingo.manager.command');
        
        $groupName = 'ninja';
        $cmdManager->start($name, $groupName);

        //******
        //your scripts, business logic
        //******

        $cmdManager->stop($this->getLogs());
    }

}
```

In this case, all commands running with the group 'ninja' will be following each other.

Unique Id
=========

You can as well specify a uniquId, this unique Id will make the command pending unique. 
In case of another command from the same group has the same id, this command will stop 
running. In this case the other command stay unique.


```php 
<?php
class RunCommand extends ContainerAwareCommand
{

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $cmdManager = $this->getContainer()->get('flamingo.manager.command');
        
        $groupName = 'ninja';
        $uniqueId = 'storeId=15';
        $cmdManager->start($name, $groupName, $uniqueId);

        //******
        //your scripts, business logic
        //******

        $cmdManager->stop($this->getLogs());
    }

}
```

unique id for the command, this will be usually based on a name and arguments
For example you want to Queue all command with argument store_id is unique

```php
$uniqueId = $name.'storeId='.$arguments['storeId'];
$cmdManager->start($name, $groupName, $uniqueId);
```

In this situtation, the command will always change the unique depending on the storeId, but the group wont change.
So each store command will be unique, but all `ninja` command will stack up.

The command will queue only one command with this Id, avoid repetition, 
if you add the command on web page and the command is ran several times per minutes
or if the command is lasting slower than the time between 2 crons, your call, 
or if you need a permanent execution etc...