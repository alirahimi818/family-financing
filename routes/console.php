<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('transactions:register-periodic')->daily()->at('00:00');
