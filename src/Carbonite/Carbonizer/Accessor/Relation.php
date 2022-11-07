<?php namespace Atomino2\Carbonite\Carbonizer\Accessor;

use Atomino2\Carbonite\Carbonizer\Access;
use Atomino2\Carbonite\Carbonizer\Accessor;
use Atomino2\Carbonite\EntityStore;
use Atomino2\Database\SmartSQL\SQL;
use DI\Container;

class Relation extends Accessor {

	const RELATION = true;
	const REVERSE  = false;

	private bool   $multi;
	private bool   $mode;
	private string $key;
	private string $target;
	private string $store;

	public function isReverse(): bool { return $this->mode === self::REVERSE; }
	public function isRelation(): bool { return $this->mode === self::RELATION; }
	public function isMulti(): bool { return $this->multi; }
	public function getKey(): string { return $this->key; }
	public function getAccess(): int { return Access::READ; }
	public function getTarget(): string { return $this->target; }
	public function getStore(): string { return $this->store; }
	public function getIdKey(): string { return $this->isReverse() ? 'id' : $this->key; }

	public function __construct(
		string $key,
		bool   $multi,
		bool   $mode,
		string $target,
		string $store,
		string $type
	) {
		$this->store = $store;
		$this->target = $target;
		$this->key = $key;
		$this->mode = $mode;
		$this->multi = $multi;
		parent::__construct($type);
	}

	public function get(int|array $id, Container $di) {
		/** @var EntityStore $store */
		$store = $di->get($this->store);
		if ($this->isRelation() && !$this->isMulti()) return $store->pick($id);
		if ($this->isRelation() && $this->isMulti()) return $store->collect($id);
		if ($this->isReverse() && !$this->isMulti()) return $store->search(SQL::filter(SQL::cmp($this->key, $id)));
		if ($this->isReverse() && $this->isMulti()) return $store->search(SQL::filter(SQL::cmp($this->key)->inJson($id)));
	}
}