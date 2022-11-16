<?php namespace Atomino2\Carbonite\Carbonizer\Accessor;

use Atomino2\Carbonite\Carbonizer\Access;
use Atomino2\Carbonite\Carbonizer\Accessor;
use Atomino2\Carbonite\EntityStore;
use Atomino2\Database\SmartSQL\SQL;

class Relation extends Accessor {

	const RELATION = true;
	const REVERSE  = false;

	private bool   $multi;
	private bool   $mode;
	private string $key;
	private string $store;

	public function getKey(): string { return $this->key; }
	public function getAccess(): int { return Access::READ; }
	public function getStore(): string { return $this->store; }
	public function getIdKey(): string { return $this->mode === self::REVERSE ? 'id' : $this->key; }

	public function __construct(
		string $key,
		bool   $multi,
		bool   $mode,
		string $store,
		string $type
	) {
		$this->store = $store;
		$this->key = $key;
		$this->mode = $mode;
		$this->multi = $multi;
		parent::__construct($type);
	}

	public function get(int|array $id, EntityStore $store) {
		if ($this->mode === self::RELATION && !$this->multi) return $store->pick($id);
		if ($this->mode === self::RELATION && $this->multi) return $store->collect($id);
		if ($this->mode === self::REVERSE && !$this->multi) return $store->search(SQL::filter(SQL::cmp($this->key, $id)));
		if ($this->mode === self::REVERSE && $this->multi) return $store->search(SQL::filter(SQL::cmp($this->key)->inJson($id)));
	}
}