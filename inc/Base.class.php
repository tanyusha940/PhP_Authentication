<?php

/**
 * Автор Олег Исаев
 * ВКонтакте: vk.com/id50416641
 * Skype: pandcar97
 */

class Base
{
    public static $pdo = false,	// Объект PDO
				  $log = [];	// Массив запросов
				  
	private static $transaction = false;
	
	// Экранирования строки слэшами для использования в запросах (не самый надёжный вариант)
	public static function safely($string)
	{
		return addslashes($string);
	}
	
	// Блокировка таблиц(ы)
	public static function lock_tables($tables = [])
	{
		$string = '';
		
		foreach ($tables as $value)
		{
			$string .= (! empty($string) ? ', ' : null) . '`'.$value.'` WRITE';
		}
		
		return self::query('LOCK TABLES '.$string);
	}
	
	// Разблокировка таблиц(ы)
	public static function unlock_tables()
	{
		return self::query('UNLOCK TABLES');
	}
	
	// Начало транзакции
	public static function transaction()
	{
		if (self::$pdo && !self::$transaction)
		{
			try
			{
				$bool = self::$pdo->beginTransaction();
				
				self::$transaction = 0;
				
				return $bool;
			}
			catch (PDOException $e)
			{
				$place = debug_backtrace()[0];
				
				trigger_error('Base::transaction() in <b>' . $place['file'] . '</b> on line <b>' . $place['line'] . '</b>, description error: ' . $e->getMessage(), E_USER_WARNING);
				
				return false;
			}
		}
		
		return false;
	}
	
	// Конец транзакции
	public static function commit()
	{
		if (self::$pdo && is_int(self::$transaction))
		{
			$count = self::$transaction;
			
			self::$transaction = false;
			
			if ($count == 0)
			{
				self::$pdo->commit();
				
				return true;
			}
			else
			{
				self::$pdo->rollBack();
			}
		}
		
		return false;
	}
	
	public static function countRows($table, $mixed_var = [])
	{
		list($part_sql, $param) = self::gen_part_sql($mixed_var, ' AND ');
		
		return self::query('SELECT COUNT(`id`) FROM `'.self::safely($table).'` WHERE '.$part_sql.' LIMIT 1', $param);
	}
	
	public static function getRow($table, $mixed_var = [])
	{
		list($part_sql, $param) = self::gen_part_sql($mixed_var, ' AND ');
		
		return self::query('SELECT * FROM `'.self::safely($table).'` WHERE '.$part_sql.' LIMIT 1', $param);
	}
	
	public static function getRowAll($table, $mixed_var = [], $length = false, $offset = false)
	{
		list($part_sql, $param) = self::gen_part_sql($mixed_var, ' AND ');
		
		return self::query('SELECT * FROM `'.self::safely($table).'`'.(! empty($part_sql) ? ' WHERE '.$part_sql : null) . ($offset || $length ? ' LIMIT '. ($offset ? $offset.', ' : null) . $length : null), $param, true);
	}
	
	public static function removeRow($table, $mixed_var = [])
	{
		list($part_sql, $param) = self::gen_part_sql($mixed_var, ' AND ');
		
		return self::query('DELETE FROM `'.self::safely($table).'` WHERE '.$part_sql.' LIMIT 1', $param);
	}
	
	public static function updateRow($table, $mixed_var = [], $param = [])
	{
		list($sel_sql, $sel_params) = self::gen_part_sql($mixed_var, ' AND ');
		list($up_sql, $up_params) = self::gen_part_sql($param, ', ');
		
		return self::query('UPDATE `'.self::safely($table).'` SET '.$up_sql.' WHERE '.$sel_sql, array_merge($sel_params, $up_params));
	}
	
	public static function addRow($table, $param = [])
	{
		$str_column = '';
		$str_values = '';
		$array = [];
		
		foreach ($param as $column => $value)
		{
			$name_column = explode('/', $column)[0];
			
			$str_column .= (! empty($str_column) ? ', ' : null).'`'.$name_column.'`';
			$str_values .= (! empty($str_values) ? ', ' : null).':'.$name_column;
			
			$array[$column] = $value;
		}
		
		return self::query('INSERT INTO `'.self::safely($table).'` ('.$str_column.') VALUES ('.$str_values.')', $array);
	}
	
	private static function gen_part_sql($array, $glue = ', ')
	{
		if (! is_array($array))
		{
			$array = ['id' => $array];
		}
		
		$string = '';
		$param = [];
		
		foreach ($array as $column => $value)
		{
			$name_column = explode('/', $column)[0];
			
			$string .= (! empty($string) ? $glue : null).'`'.$name_column.'` = :'.$name_column;
			
			$param[$column] = $value;
		}
		
		return [$string, $param];
	}
	
	// SQL запрос
	public static function query($str_sql, $param = [], $all_result = false)
	{
		if (self::$pdo)
		{
			$place = debug_backtrace()[0];
			
			unset($place['function'], $place['class'], $place['type']);
			
			try
			{
				$time = microtime(true);
				
				if (! empty($param))
				{
					$stm = self::$pdo->prepare($str_sql);
					
					foreach ($param as $key => $value)
					{
						@list($column, $type) = explode('/', $key);
						
						$value = (! empty($value) ? $value : '');
						$set_type = PDO::PARAM_STR;
						
						if (isset($type) && $type == 'int')
						{
							$value = (int) $value;
							$set_type = PDO::PARAM_INT;
						}
						
						$stm->bindValue(':'.$column, $value, $set_type);
					}
					
					$stm->execute();
				}
				else
				{
					$stm = self::$pdo->query($str_sql);
				}
				
				$place['time'] = microtime(true) - $time;
				
				self::$log[] = $place;
			}
			catch (PDOException $e)
			{
				if (is_int(self::$transaction))
				{
					self::$transaction++;
				}
				
				$place['time'] = 0;
				$place['error'] = $e->getMessage();
				
				self::$log[] = $place;
				
				trigger_error('Base::query() in <b>' . $place['file'] . '</b> on line <b>' . $place['line'] . '</b>, description error: '.$place['error'].', '.print_r($place['args'], true), E_USER_WARNING);
				
				return false;
			}
			
			if ($stm)
			{
				$comand = mb_strtolower(explode(' ', $str_sql)[0], 'UTF-8');
				
				if ($comand == 'select')
				{
					if ($all_result)
					{
						return $stm->fetchAll(PDO::FETCH_ASSOC);
					}
					
					if ($array = $stm->fetch(PDO::FETCH_ASSOC))
					{
						if (count($array) == 1)
						{
							if (substr_count( array_keys($array)[0], '(' ) > 0)
							{
								return array_values($array)[0];
							}
						}
						
						return $array;
					}
					
					return false;
				}
				elseif ($comand == 'insert')
				{
					return self::$pdo->lastInsertId();
				}
				
				return true;
			}
		}
		
		return false;
	}
	
	// Подключение к базе данных
	public static function connect($type = 'mysql', $param = [])
	{
		switch ($type)
		{
			case 'mysql':
				$dsn = 'mysql:host='.(! empty($param['host']) ? $param['host'] : 'localhost').';'.(! empty($param['port']) ? 'port='.$param['port'].';' : null).'dbname='.$param['base'].';charset='.(! empty($param['charset']) ? $param['charset'] : 'utf8');
				break;
			case 'postgresql':
				$dsn = 'pgsql:host='.(! empty($param['host']) ? $param['host'] : 'localhost').';dbname='.$param['base'].';options="--client_encoding='.(! empty($param['charset']) ? $param['charset'] : 'utf8').'"';
				break;
			case 'oracle':
				$dsn = 'oci:dbname='.(! empty($param['host']) ? $param['host'] : 'localhost').'/'.$param['base'].';charset='.(! empty($param['charset']) ? $param['charset'] : 'utf8');
				break;
			case 'sqlite':
				$dsn = 'sqlite:'.$param['path'];
				break;
		}
		
		if (isset($dsn))
		{
			try
			{
				self::$pdo = new PDO($dsn, $param['user'], $param['pass'], [
					PDO::ATTR_ERRMODE				=> PDO::ERRMODE_EXCEPTION, 
					PDO::ATTR_DEFAULT_FETCH_MODE	=> PDO::FETCH_ASSOC,
				]);
			}
			catch (PDOException $e)
			{
				$place = debug_backtrace()[0];
				trigger_error('Base::connect() in <b>' . $place['file'] . '</b> on line <b>' . $place['line'] . '</b>, description error: ' . $e->getMessage(), E_USER_WARNING);
				echo $e->getMessage();
				return false;
			}
			
			return self::$pdo;
		}
		
		return false;
	}
	
	public static function end()
	{
		self::$pdo = false;
	}
}
