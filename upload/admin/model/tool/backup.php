<?php
class ModelToolBackup extends Model {
	public function restore($sql) {
		foreach (explode(";\n", $sql) as $sql) {
			$sql = trim($sql);

			if ($sql) {
				$this->db->query($sql);
			}
		}

		$this->cache->delete('*');
	}

	public function getTables() {
		$table_data = array();
		global $config;
		if (($this->config->get('db_type')) == "CSQLite3") {

			    $query = $this->db->query("SELECT name FROM sqlite_master WHERE type='table';");
				foreach ($query->rows as $result) {
					$strRes=$result['name'];
					$strRes2=utf8_substr($result['name'], 0, strlen(DB_PREFIX));
					if (utf8_substr($result['name'], 0, strlen(DB_PREFIX)) == DB_PREFIX) {
						if (isset($result['name'])) {
							$table_data[] = $result['name'];
						}
					}
				}
		}
		else {		
			$query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

			foreach ($query->rows as $result) {
				if (utf8_substr($result['Tables_in_' . DB_DATABASE], 0, strlen(DB_PREFIX)) == DB_PREFIX) {
					if (isset($result['Tables_in_' . DB_DATABASE])) {
						$table_data[] = $result['Tables_in_' . DB_DATABASE];
					}
				}
			}
		}
		return $table_data;
	}

	public function backup($tables) {
		$output = '';

		foreach ($tables as $table) {
			if (DB_PREFIX) {
				if (strpos($table, DB_PREFIX) === false) {
					$status = false;
				} else {
					$status = true;
				}
			} else {
				$status = true;
			}

			if ($status) {
				if (($this->config->get('db_type')) == "CSQLite3") {
					$output .= 'DELETE FROM `' . $table . '`;' . "\n\n";
				}else {
					$output .= 'TRUNCATE TABLE `' . $table . '`;' . "\n\n";
				}
				$query = $this->db->query("SELECT * FROM `" . $table . "`");

				foreach ($query->rows as $result) {
					$fields = '';

					foreach (array_keys($result) as $value) {
						$fields .= '`' . $value . '`, ';
					}

					$values = '';

					foreach (array_values($result) as $value) {
						$value = str_replace(array("\x00", "\x0a", "\x0d", "\x1a"), array('\0', '\n', '\r', '\Z'), $value);
						$value = str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $value);
						$value = str_replace('\\', '\\\\',	$value);
						$value = str_replace('\'', '\\\'',	$value);
						$value = str_replace('\\\n', '\n',	$value);
						$value = str_replace('\\\r', '\r',	$value);
						$value = str_replace('\\\t', '\t',	$value);

						$values .= '\'' . $value . '\', ';
					}

					$output .= 'INSERT INTO `' . $table . '` (' . preg_replace('/, $/', '', $fields) . ') VALUES (' . preg_replace('/, $/', '', $values) . ');' . "\n";
				}

				$output .= "\n\n";
			}
		}

		return $output;
	}
}