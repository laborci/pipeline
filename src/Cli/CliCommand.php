<?php namespace Atomino2\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CliCommand extends Command {

	protected Style $style;
	protected InputInterface $input;
	protected OutputInterface $output;
	protected \Closure $executable;

	public function __invoke(callable $exec) { $this->executable = $exec; }
	public function define(callable $exec) { $this->executable = $exec; }
	protected final function execute(InputInterface $input, OutputInterface $output): int {
		$output->getFormatter()->setDecorated(true);
		$this->style = new Style($input, $output);
		$this->input = $input;
		$this->output = $output;
		try {
			$message = ($this->executable)($input, $output, $this->style);
			if ($message) {
				$this->style->newLine();
				$this->style->writeln('<fg=green;options=bold>' . $message . '</>');
			}
		} catch (Error $e) {
			$this->style->newLine();
			$this->style->writeln('<fg=red;options=bold>' . ($e->getMessage() ?: 'terminated') . '</>');
			$this->style->newLine();
			return 1;
		}
		return 0;
	}
}
