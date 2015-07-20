You can define several options on the object FlgCommand on the creation of the command's instance

We will review all those options here.

Max Pending Instance
=======

It defines how many command's instance are authorized to be waiting in the line up

**Default:** 30 command's instances

```php
    $flgCommand = new FlgCommand();
    $flgCommand->setMaxPendingInstance(60);
```


Pending Laps Time
=======

It will define how long the command the instance command will wait before controlling its status
and run or wait again.

**Default:** 60seconds

```php
    $flgCommand = new FlgCommand();
    $flgCommand->setPendingLapsTime(60);
```

Archvive Enable
========

Archive or delete the command logs, entity FlgScriptInstanceLog

**Default:** true

```php
    $flgCommand = new FlgCommand();
    $flgCommand->setArchiveEnable(false);
```