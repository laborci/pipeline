<?php namespace Atomino2\Database;

use Atomino2\Database\SmartQuery\Finder;
use Atomino2\Database\SmartQuery\SmartQuery;
use Atomino2\Database\SmartSQL\Select\Select;
use Atomino2\Database\SmartSQL\SqlHelper;
use Atomino2\Database\SmartStructure\SmartStructure;
use PDO;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class Connection {
	private PDO              $pdo;
	private string           $dsn;
	private ?LoggerInterface $logger;
	private SqlHelper        $sqlHelper;
	private ?SmartQuery      $smartQuery     = null;
	private ?SmartStructure  $smartStructure = null;
	private ?CacheInterface  $cache;

	public function getDsn(): string { return $this->dsn; }
	public function getPdo(): PDO { return $this->pdo; }
	public function getSqlHelper(): SqlHelper { return $this->sqlHelper; }
	public function getSmartQuery(): SmartQuery { return is_null($this->smartQuery) ? $this->smartQuery = new SmartQuery($this) : $this->smartQuery; }
	public function getSmartStructure(): SmartStructure { return is_null($this->smartStructure) ? $this->smartStructure = new SmartStructure($this) : $this->smartStructure; }
	public function getFinder(?string $table = null): Finder { return new Finder($this, new Select($table), $this->cache); }

	public function __construct(string $dsn, ?LoggerInterface $logger = null, ?CacheInterface $cache = null) {
		$this->logger = $logger;
		$this->dsn = $dsn;
		$this->pdo = new PDO($this->dsn);
		// set the language of the error messages to english for further usage
		$this->pdo->query("SET lc_messages = 'en_US'");
		$this->sqlHelper = new SqlHelper($this);
		$this->cache = $cache;
	}

	/**
	 * @param string $sql
	 * @return bool|\PDOStatement
	 * @throws \Exception
	 */
	public function query(string $sql): bool|\PDOStatement {
		try {
			$this->logger?->info($sql);
			debug($sql);
			return $this->pdo->query($sql);
		} catch (\Exception $exception) {
			$this->logger?->error($exception->getMessage(), [$sql]);
			throw $exception;
		}
	}

	public function beginTransaction(): bool { return $this->pdo->beginTransaction(); }
	public function commit(): bool { return $this->pdo->commit(); }
	public function rollBack(): bool { return $this->pdo->rollBack(); }
	public function inTransaction(): bool { return $this->pdo->inTransaction(); }

}

