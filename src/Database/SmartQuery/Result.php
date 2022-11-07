<?php namespace Atomino2\Database\SmartQuery;
class Result {
	/** @var array[] */
	private array $records = [];
	private ?int $count = null;
	public function __construct(array $records, ?int $count = null) {
		$this->count = $count;
		$this->records = $records;
	}
	public function getRecords(): array { return $this->records; }
	public function getCount(): ?int { return $this->count; }
	public function getFirstRecord(): array|null { return count($this->records) > 0 ? $this->records[0] : null; }
}