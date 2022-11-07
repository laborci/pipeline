<?php namespace Atomino2\Auth\Authentication;

interface AuthenticatorInterface { public function authenticate(): string|bool; }
