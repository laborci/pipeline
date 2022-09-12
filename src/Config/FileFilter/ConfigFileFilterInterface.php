<?php namespace Atomino2\Config\FileFilter;

interface ConfigFileFilterInterface {
	/**
	 * @param string[] $files
	 * @return string[]
	 */
	public function filter(array $files): array;
}