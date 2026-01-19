<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune')->daily();
Schedule::command('mailbox:clean')->daily();
Schedule::command('webhook:retry')->everyFifteenMinutes();
