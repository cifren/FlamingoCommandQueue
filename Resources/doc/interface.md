Interface
=========

No views are available yet...

But 3 Entities exist :
- FlgScript, give you the name of the command you ran.
- FlgScriptInstanceLog, from FlgScript, the entity is the list of instance ran for FlgScript, you will find logs/duration/status/createdAt, it is the archive of your instance commands.
- FlgScriptRunningInstance, from FlgScript, this entity give you the current status of the command.

The list of status available in [FlgScriptStatus](../../Model/FlgScriptStatus.php) :
- PENDING only available in FlgScriptRunningInstance
- RUNNING only available in FlgScriptRunningInstance
- CANCELED only available in FlgScriptInstanceLog
- FINISHED only available in FlgScriptInstanceLog
- FAILED only available in FlgScriptInstanceLog
- TERMINATED only available in FlgScriptInstanceLog