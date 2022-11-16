<?php namespace Atomino2\Carbonite\Cli;

use Atomino2\Carbonite\Carbonizer\Carbonizer;
use Atomino2\Cli\CliCommand;
use Atomino2\Cli\CliModule;
use Atomino2\Cli\Command;
use Atomino2\Cli\Style;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

class CarboniteCli extends CliModule {

	public function __construct(private Carbonizer $carbonizer) { }

	#[Command("carbonite:update", "carbonize", "Updte Carbonite entities")]
	public function update(CliCommand $command) {
		$command->define(function (Input $input, Output $output, Style $style) {
			$this->carbonizer->update($style);
		});
	}
	#[Command("carbonite:create", null, "Create new Carbonite entity")]
	public function creates(CliCommand $command) {
		$command->addArgument('entity');
		$command->define(function (Input $input, Output $output, Style $style) {
			$this->carbonizer->create($input->getArgument('entity'), $style);
		});
	}
}