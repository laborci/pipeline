<?php namespace Atomino2\Application\Config\FileFilter;

interface ConfigFileFilterInterface {
	/**
	 * @param string[] $files
	 * @return string[]
	 */
	public function filter(array $files): array;
}