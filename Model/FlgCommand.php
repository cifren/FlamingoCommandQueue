<?php

namespace Earls\FlamingoCommandQueueBundle\Model;

class FlgCommand
{

    /**
     * You can line up commands in a table, everytime a command's instance
     * is created, the system will line up the commands and execute them one by one
     * 
     * @var string 
     */
    protected $groupName = null;

    /**
     * Command waiting for their execution(pending) will be unique, all newly created commands with the same
     * uniqueId won't be added to the line up, simply deleted, usually a clone of the previous command 
     * 
     * @var string
     */
    protected $uniqueId = null;

    /**
     * How many command's instance are authorized to be waiting in the line up
     * 
     * @var int 
     */
    protected $maxPendingInstance = 30;

    /**
     * How long the command the instance command will wait before controlling its status
     * and run or wait again
     * 
     * @var int 
     */
    protected $pendingLapsTime = 60;

    /**
     * Archive or delete the command logs, entity FlgScriptInstanceLog
     *
     * @var boolean 
     */
    protected $archiveEnable = true;

    /**
     * Give an ID in order to retrieve the information based on a external information
     * 
     * For Example : 
     *  Can be used if you want to refer to a page and an object id
     *  Give an array of page_id and object_id and the system will concat and save the id
     *  The information can be retrieve later
     * 
     * @var string 
     */
    protected $watchScriptReferences = array();

    public function getGroupName()
    {
        return $this->groupName;
    }

    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    public function getMaxPendingInstance()
    {
        return $this->maxPendingInstance;
    }

    public function getPendingLapsTime()
    {
        return $this->pendingLapsTime;
    }

    public function setMaxPendingInstance($maxPendingInstance)
    {
        $this->maxPendingInstance = $maxPendingInstance;
        return $this;
    }

    public function setPendingLapsTime($pendingLapsTime)
    {
        $this->pendingLapsTime = $pendingLapsTime;
        return $this;
    }

    public function getArchiveEnable()
    {
        return $this->archiveEnable;
    }

    public function setArchiveEnable($archiveEnable)
    {
        $this->archiveEnable = $archiveEnable;
        return $this;
    }

    public function getWatchScriptReferences()
    {
        return $this->watchScriptReferences;
    }

    public function setWatchScriptReferences($watchScriptReferences)
    {
        $this->watchScriptReferences = $watchScriptReferences;
        return $this;
    }

}
