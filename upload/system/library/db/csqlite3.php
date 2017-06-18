<?php
namespace DB;
final class CSQLite3 {
	private $connection= null;
	public function __construct($hostname, $username, $password, $database, $port = '1433') {
		if (!$this->connection = new \SQLite3($database))  {
		//if (!$this->connection = \sqlite3::open($database)) {
			throw new \Exception('Error: Could not connect to database ' . $database );
		}
		//mssql_query("SET NAMES 'utf8'", $this->connection);
		//mssql_query("SET CHARACTER SET utf8", $this->connection);
	}
//http://php.net/manual/en/sqlite3stmt.execute.php
/*-http://php.net/manual/en/book.sqlite3.php#113144 - user 'bohwaz' mentions that there's also a SQLite3Stmt::readOnly() function since PHP 5.3.11 which will tell you if you just wrote to the DB. This is currently undocumented but might be a more appropriate alternative to numColumns() (I'm not sure what it does, it might be the same).*/
	public function query($sql) {
		if ($this->connection) {
			$myfile = fopen("d:\db\logs.txt", "a") or die("Unable to open file!");
			fwrite($myfile, "\n". $sql."\r\n ");
			fclose($myfile);
			//var_dump($sql);
			//echo("\r\n");
			$resource = $this->connection->query( $sql);
			
			//$statement = $this->connection->prepare( $sql);
			//$resource=$statement->execute(); 
			if(  $resource) {
				//if (is_resource($resource)) {
					$i = 0;
//SELECT a.key, a.data, a.date_added FROM (SELECT ('customer_'|| ca.key) AS `key`, ca.data, ca.date_added FROM `oc_customer_activity`  as ca UNION  SELECT ('affiliate_'|| aa.key) AS `key`, aa.data, aa.date_added FROM `oc_affiliate_activity` as aa) as a ORDER BY a.date_added DESC LIMIT 0,5

					$data = array();
					if ($resource->numColumns())
					{
						while ($result =$resource->fetchArray(SQLITE3_ASSOC)) {
							$data[$i] = $result;
							$i++;
						}
						//$resource->close();
						$query = new \stdClass();
						$query->row = isset($data[0]) ? $data[0] : array();
						$query->rows = $data;
						$query->num_rows = $i;
						unset($data);
						
						return $query;
					}
				 else { return true; }
		} else {
		    echo "{ \"good\": false, \"what\": \"".$this->connection->lastErrorMsg ()."\" }";
		    return false;
		    
		}
			}
			else 
			{
				echo "{ \"good\": false, \"what\": \"".$this->connection->lastErrorMsg ()."\" }";
				return;
			}	
		
	}

	public function escape($value) {
		if ($this->connection) {
			return $this->connection->escapeString($value);
		}
	}

	public function countAffected() {
		return $this->connection->changes();
	}

	// If a separate thread performs a new INSERT on the same database connection while the sqlite3_last_insert_rowid() function is running 
	// and thus changes the last insert rowid, then the value returned by sqlite3_last_insert_rowid() is unpredictable 
	// and might not equal either the old or the new last insert rowid. 
	
	public function getLastId() {
			if ($this->connection) {
			return $this->connection->lastInsertRowID ();
		}
	}
	public function isConnected() {
		if ($this->connection) {
			return true;
		} else {
			return false;
		}
	}
	public function __destruct() {
		if ($this->connection) {
			$this->connection->close();
		}
	}
}