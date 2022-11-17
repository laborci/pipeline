<?php

namespace Atomino2\Mercury\FileServer;

interface FileResolverInterface {
	public function resolve($uri):?string;
}