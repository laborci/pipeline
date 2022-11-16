<?php namespace Atomino2\Carbonite\Carbonizer;

interface Persist {
	const NEVER  = 0;
	const INSERT = 1;
	const ALWAYS = 2;
}