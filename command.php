<?php

namespace WP_CLI_Sage;

require_once 'SageCommands.php';

use SageCommand\SageCommands;

if (!class_exists('WP_CLI')) {
    return;
}

$instance = new SageCommands();
WP_CLI::add_command('sage', $instance);