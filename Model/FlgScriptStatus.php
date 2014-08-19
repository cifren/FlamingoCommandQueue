<?php

namespace Earls\FlamingoCommandQueueBundle\Model;

class FlgScriptStatus
{

    /**
     * State if job is inserted, and might be started.
     *
     * It is important to note that this does not automatically mean that all
     * jobs of this state can actually be started, but you have to check
     * isStartable() to be absolutely sure.
     *
     * In contrast to NEW, jobs of this state at least might be started,
     * while jobs of state NEW never are allowed to be started.
     */
    const STATE_PENDING = 1;

    /** State if job was never started, and will never be started. */
    const STATE_CANCELED = 2;

    /** State if job was started and has not exited, yet. */
    const STATE_RUNNING = 3;

    /** State if job exists with a successful exit code. */
    const STATE_FINISHED = 4;

    /** State if job exits with a non-successful exit code. */
    const STATE_FAILED = 5;

    /** State if job exceeds its configured maximum runtime. */
    const STATE_TERMINATED = 6;

}
