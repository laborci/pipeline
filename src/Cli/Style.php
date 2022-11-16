<?php namespace Atomino2\Cli;

use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Style extends SymfonyStyle {

	protected OutputInterface $output;
	protected InputInterface  $input;
	private Cursor            $cursor;

	public function __construct(InputInterface $input, OutputInterface $output) {
		parent::__construct($input, $output);
		$this->input = $input;
		$this->output = $output;
		$this->cursor = new Cursor($this->output);

	}

	public function _task(string $message) {
		$this->write('[·] ' . trim($message));
		$this->cursor->savePosition();
		$this->newLine();
	}

	public function _task_ok(string $message = '', $inline = true) { $this->_task_message('✔', 'green', $message, $inline); }
	public function _task_error(string $message = '', $inline = true) { $this->_task_message('✖', 'red', $message, $inline); }
	public function _task_warn(string $message = '', $inline = true) { $this->_task_message('≡', 'yellow', $message, $inline); }
	public function _task_message(string $icon, string $color, string $message, $inline = true) {
		$this->cursor->moveUp();
		$this->cursor->moveToColumn(2);
		$this->writeln('<fg=' . $color . '>' . $icon . '</>');
		if ($message) {
			if ($inline) {
				$this->cursor->restorePosition();
				$this->cursor->moveUp();
				$this->writeln(' <fg=' . $color . '>' . $message . '</>');
			} else {
				$this->writeln('    <fg=' . $color . '>' . $message . '</>');
			}
		}
	}

	public function _icon(string $icon, string $color, string $message, $reverse = false) { $this->writeln('<fg=' . $color . ($reverse ? ';options=reverse' : '') . '>[' . $icon . '] ' . $message . '</>'); }
	public function _ok(string $message = '', $reverse = false) { $this->_icon('✔', 'green', $message, $reverse); }
	public function _error(string $message = '', $reverse = false) { $this->_icon('✖', 'red', $message, $reverse); }
	public function _warn(string $message, $reverse = false) { $this->_icon('≡', 'yellow', $message, $reverse); }

	public function _section(string $message, string $color = 'magenta') {
		$this->newLine();
		$this->writeln('<fg=' . $color . ';options=bold,underscore,>' . $message . '</>');
	}

	public function _note(string $message, string|null $title = null) {
		$lines = explode("\n", trim($message));
		$max = 0;
		array_walk($lines, function ($line) use (&$max) { if ($max < strlen($line)) $max = strlen($line); });
		if (!is_null($title)) $this->writeln('  <fg=yellow;options=bold>' . $title . '</>');

		array_unshift($lines, '');
		array_push($lines, '');
		foreach ($lines as $line) {
			$this->writeln('    <fg=yellow;bg=black>  ' . str_pad($line, $max) . '  </>');
		}
	}
}