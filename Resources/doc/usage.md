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
        $cmdManager = $this->getContainer()->get('flamingo.manager.command');
        
        $scriptName = 'myOxpeckerRunCommand';
        $cmdManager->start($scriptName);

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
Symfony2/Monolog, you could use into your command from 
[logger](http://symfony.com/fr/doc/current/cookbook/logging/monolog.html).

###TIPS

This tool use monolog in order to catch the logs into the database
In dev the system the debug are active, but not in prod so $this->getContainer()->get('logger')->getLogs() will be an empty array
In your monolog config (config_prod.yml) you can add 
```yml
    monolog:
    handlers:
        array:
            type: debug     #will call the handler
            level: notice   #will pass only notice level message, light weight logs


Queue
=====
You can line up commands in a table, everytime a command's instance is created, the system will line 
up the commands instance and execute them one by one.

The commands will be stored into the entity/table `FlgScriptRunningInstance`, the thing you have to do is to declare a group's name.

```php 
<?php
class RunCommand extends ContainerAwareCommand
{

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cmdManager = $this->getContainer()->get('flamingo.manager.command');
        
        $scriptName = 'myOxpeckerRunCommand';
        $groupName = 'ninja';
            
        $this->getflgCommandOption($groupName);
        $cmdManager->start($scriptName, $groupName);

        //******
        //your scripts, business logic
        //******

        $cmdManager->stop($this->getLogs());
    }

    protected function getflgCommandOption($groupName)
    {
        $flgCommandOption = new flgCommandOption();
        $flgCommandOption->setGroupName($groupName);
    }

}
```

In this case, all commands running with the group 'ninja' will be following each other, means all commands with same group's name will line up.

Unique Id
=========

You can as well specify a uniquId, this unique Id will make the command pending unique. 
In case of another command from the same group has the same id in the queue, this command will canceled itself. In this case the other command stay unique.


```php 
<?php
class RunCommand extends ContainerAwareCommand
{

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cmdManager = $this->getContainer()->get('flamingo.manager.command');
        
        $scriptName = 'myOxpeckerRunCommand';
        $groupName = 'ninja';
        $uniqueId = 'storeId=15';
        $this->getflgCommandOption($groupName, $uniqueId);
        $cmdManager->start($scriptName, $groupName, $uniqueId);

        //******
        //your scripts, business logic
        //******

        $cmdManager->stop($this->getLogs());
    }

    protected function getflgCommandOption($groupName, $uniqueId)
    {
        $flgCommandOption = new flgCommandOption();
        $flgCommandOption->setGroupName($groupName);
        $flgCommandOption->setGroupName($uniqueId);
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
        
See [Command Options](commandOptions.md) for more options

Entities
========

3 Entities exist :

- `FlgScript`, give you the name of the command you ran.
- `FlgScriptInstanceLog`, from FlgScript, the entity is the list of instance ran for FlgScript, you will find logs/duration/status/createdAt, it is the archive of your instance commands.
- `FlgScriptRunningInstance`, from FlgScript, this entity give you the current status of the command.

The list of status available in [FlgScriptStatus](../../Model/FlgScriptStatus.php) :

- `PENDING` only available in FlgScriptRunningInstance
- `RUNNING` only available in FlgScriptRunningInstance
- `CANCELED` only available in FlgScriptInstanceLog
- `FINISHED` only available in FlgScriptInstanceLog
- `FAILED` only available in FlgScriptInstanceLog
- `TERMINATED` only available in FlgScriptInstanceLog