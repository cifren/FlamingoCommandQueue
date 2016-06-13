You can define several options on the object flgCommandOption on the creation of the command's instance

We will review all those options here.

Max Pending Instance
=======

It defines how many command's instance are authorized to be waiting in the line up

**Default:** 30 command's instances

```php
    $flgCommandOption = new flgCommandOption();
    $flgCommandOption->setMaxPendingInstance(60);
```


Pending Laps Time
=======

It will define how long the command the instance command will wait before controlling its status
and run or wait again.

**Default:** 60seconds

```php
    $flgCommandOption = new flgCommandOption();
    $flgCommandOption->setPendingLapsTime(60);
```

Archvive Enable
========

Archive or delete the command logs, entity FlgScriptInstanceLog

**Default:** true

```php
    $flgCommandOption = new flgCommandOption();
    $flgCommandOption->setArchiveEnable(false);
```