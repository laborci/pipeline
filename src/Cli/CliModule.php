<?php namespace Atomino2\Cli;

class CliModule {
	public function getCommands() {
		$commands = [];
		$methods = (new \ReflectionClass(get_called_class()))->getMethods();
		foreach ($methods as $method) {
			if (!is_null($_command = Command::get($method))) {
				$command = new CliCommand();
				$this->{$method->getName()}($command);
				$command->setName($_command->getName());
				if (!is_null($_command->getAlias())) $command->setAliases([$_command->getAlias()]);
				if (!is_null($_command->getDescription())) $command->setDescription($_command->getDescription());
				$commands[] = $command;
			}
		}
		return $commands;
	}

}