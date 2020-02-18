<?php


class SageCommands
{
    protected $bar;
    public function __construct($bar) {
        $this->bar = $bar;
    }
    public function __invoke( $args ) {
        WP_CLI::success($this->bar . ':' . $args[0]);
    }
}

$instance = new SageCommands( 'Some text' );
WP_CLI::add_command( 'foo', $instance );