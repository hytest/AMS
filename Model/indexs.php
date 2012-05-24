<?php

class indexs extends AmysqlModel
{

	/**
	 * 查所有数据结构信息
	 *
	 */
	function GetDatabasesList()
	{
		$this -> UseDatabases('information_schema');
		if(!class_exists('DatabaseLeft')) Return;
		$sql = "SHOW DATABASES";
		$result = $this -> _query($sql);
		while ($rs = mysql_fetch_assoc($result))
		{
			$database = new DatabaseItem();
			$database -> set($rs['Database']);
			$database -> ChildItem = $this -> GetTableNameList($rs['Database']);
			$database -> ChildItemSum = count($database -> ChildItem);
			if($database -> ChildItemSum == 0) $database -> ChildItem = null;
			$data[] = $database; 
		}
		Return $data;
	}


	/**
	 * 查所有表名
	 *
	 * @param	string	 $databases	 数据库名
	 */
	function GetTableNameList($databases)
	{
		$_databases = Functions::SqlKeyword($databases);
		if(!class_exists('DatabaseLeft')) Return;
		$data = array();
		$sql = "SHOW TABLES FROM `$_databases`";
		$result = $this -> _query($sql);
		while ($rs = mysql_fetch_assoc($result))
		{
			$table = new TableItem();
			$table -> set($rs['Tables_in_' . $databases], $databases);
			$data[] = $table;
		}
		Return $data;
	}

	// ******************************************************

	/**
	 * 查表所有的字段
	 *
	 * @param	string	 $table		 表名
	 * @param	string	 $databases	 数据库名
	 */
	function GetFieldList($table, $databases)
	{
		global $Config;
		$_databases = Functions::SqlKeyword($databases);
		$_table = Functions::SqlKeyword($table);

		mysql_select_db('information_schema');
		$this -> _query("SET NAMES '" . str_replace('-', '', $Config['CharSet'])  . "'");

		$sql = "SELECT * FROM COLUMNS WHERE TABLE_SCHEMA LIKE '$databases'  AND TABLE_NAME LIKE '$table' ";
		$result = $this -> _query($sql);
		if(!$result) Return false;
		$data = array();
		while ($rs = mysql_fetch_assoc($result))
		{
			preg_match("/\((.*)\)/i", $rs['COLUMN_TYPE'], $Length); 
			$rs['Length'] = (isset($Length[1])) ? $Length[1] : '';			// 长度或值
			
			// 判断属性	$temp_if_type 把属性或值去掉先 *************************************
			$temp_if_type = str_replace($rs['Length'], '', $rs['COLUMN_TYPE']);
			$rs['COLUMN_PROPERTY'] = (strpos($temp_if_type, 'binary')) ? 'binary': ((strpos($temp_if_type, 'unsigned zerofill')) ? 'unsigned zerofill' : ((strpos($temp_if_type, 'unsigned')) ? 'unsigned' : ''));	
			
			if($rs['DATA_TYPE'] == 'timestamp')
			{
				if (!isset($create_sql))
				{
					$sql = "SHOW CREATE TABLE `$_databases`.`$_table`";
					$return_data = $this -> _row($sql);
					$create_sql = $return_data['Create Table'];
				}
				preg_match_all("/COMMENT '(.*)'|default '(.*)'/iU", $create_sql, $matches);		// 匹配COMMENT default内容 
				$create_sql = str_replace($matches[0], '', $create_sql);						// 去COMMENT default内容
				preg_match_all("/`(.*)`|on update CURRENT_TIMESTAMP/iU", $create_sql, $matches); // 匹配字段或 on update CURRENT_TIMESTAMP
				$OUCT_KEY = array_search('on update CURRENT_TIMESTAMP', $matches[0]);	// 找on update CURRENT_TIMESTAMP的键名
				if($OUCT_KEY &&  str_replace('"', '\"', $matches[0][$OUCT_KEY-1]) == "`$rs[COLUMN_NAME]`") $rs['COLUMN_PROPERTY'] = 'on update CURRENT_TIMESTAMP';
			}
			// ******** END *******

			$data[] = $rs;
		}

		mysql_select_db($databases);
		$this -> _query("SET NAMES '" . str_replace('-', '', $Config['CharSet'])  . "'");
		Return $data;
	}

	// 取得结果集字段
	function GetFields($result)
	{
		// mysql_field_name
		// mysql_field_len
		$fields = array();
		$num_fields = mysql_num_fields($result);
		for ($i = 0; $i < $num_fields; $i++) 
		{
			$fields[$i] = mysql_fetch_field($result, $i);			// 字段信息
			$fields[$i] -> flags = mysql_field_flags($result, $i);	// 取得字段真实标识
		}
		Return $fields;
	}


	//　获取查询sql的数据
	function GetSqlData($sql, $NewSql, $limit, $MysqlFetchType = MYSQL_NUM)
	{
		// $limit[0] 起读点、$limit[1] 读取几条
		$starttime = array_sum(explode(" ", microtime())); 
		$rs = $this -> _query($NewSql);
		$endtime = array_sum(explode(" ", microtime())); 
		if (!$rs) return array($this -> GetError(), round($endtime - $starttime, 5), 0);

		/* // 总和 
		$COUNT_SQL_ARR = explode(' FROM ', strtoupper($NewSql));
		$COUNT_SQL_ARR[1] = substr($COUNT_SQL_ARR[1] , 0, strrpos($COUNT_SQL_ARR[1], ' LIMIT'));
		$COUNT_SQL = 'SELECT COUNT(*) FROM ' . $COUNT_SQL_ARR[1];
		// $COUNT_SQL_DATA = mysql_fetch_row(mysql_query($COUNT_SQL));
		$SumResult = $COUNT_SQL_DATA[0];
		echo $COUNT_SQL; */

		$SumResult = ($NewSql == $sql) ? mysql_num_rows($rs) : mysql_num_rows(mysql_query($sql));	// 总和
		if (!$SumResult)  return array(array(), round($endtime - $starttime, 5), 0);				// 没数据返回

		// 字段名称等信息
		$fields = $this -> GetFields($rs);

		// 数据 ********************
		$all_rows = array();
		$i = $a = 0;
		while(true)
		{
			// 进来的Sql没有Limit直接通过同样读取$SumResult条记录
			if($i >= $limit[0] || ($NewSql != $sql))	
			{
				if($i == $limit[0]) mysql_data_seek($rs, $i);
				$rows = mysql_fetch_array($rs, $MysqlFetchType);
				if(empty($rows)) break;
				
				// BLOB判断(实际判断是否为blob binary) ******************
				$BI = 0;
				foreach ($rows as $key=>$val)
				{
					if(strpos($fields[$BI] -> flags, 'blob binary') !== false) 
						$rows[$key] = 'BLOB - ' . Functions::CountSize(strlen($val));
					++$BI;
				}
				// End ********************************************

				$all_rows[] = $rows;
				++$a;
			}

			if($a == $limit[1] || $a == $SumResult) break;	// 加够了 || 等于总和 就退出
			++$i;
		}

		$this -> _free($rs);
		Return array($all_rows, round($endtime - $starttime, 5), $SumResult, $fields);
		// 记录集, 运行时间, 总数, 字段名称
 	}

	// 执行操作类型SQL
	function OperationQuery($sql) 
	{
		if (empty($sql) || count($sql) == 0) Return false;
		if (!is_array($sql))
			$SqlArr[] = $sql;
		else 
			$SqlArr = $sql;

		$affected = 0;
		$status = array();
		foreach ($SqlArr as $key=>$val)
		{
			if(!empty($val))
			{
				$rs = $this -> _query($val);
				if ($rs) 
				{	
					$status[] = 'success';
					if(!Functions::is_QueryData($val)) 
						$affected += mysql_affected_rows();
				}
				else 
				{
					$status[] = 'fail';
					Return array(mysql_error(), $affected, $status);
				}
			}
		}
		Return array(null, $affected, $status);
	}

	

	// 分析sql 获取表的数量
	function ExplainSql($sql)
	{
		$sql = 'EXPLAIN ' . $sql;
		Return $this -> _sum($sql);

	}

	// 获取mysql COLLATIONS 字段校对
	function GetCollations($type = 'js')
	{
		$sql = "SHOW COLLATION";
		$result = $this -> _query($sql);
		while ($rs = mysql_fetch_assoc($result))
			$data[$rs['Charset']][] = $rs['Collation'];

		Return $data;
 	}

	// 获取MYSQL引擎
	function GetEngines()
	{
		$sql = "SHOW STORAGE ENGINES";
		$result = $this -> _query($sql);
		$i = 1;
		while ($rs = mysql_fetch_assoc($result))
		{
			if($rs['Support'] != 'NO')
				$data[($rs['Support'] == 'DEFAULT' ? 0 : ++$i)] = $rs['Engine'];
		}
		Return $data;
	}
	
	// 获取索引
	function ShowIndex($table, $database)
	{
		$_database = Functions::SqlKeyword($database);
		$_table = Functions::SqlKeyword($table);
		
		$sql ="SHOW INDEX FROM `$_database`.`$_table`";
		$result = $this -> _query($sql);
		if(!$result || mysql_num_rows($result) == 0) Return array();
		while ($rs = mysql_fetch_assoc($result))
		{
			if ('PRIMARY' == $rs['Key_name']) 
				$rs['type'] = 'PRIMARY';
			elseif ('FULLTEXT' == $rs['Index_type']) 
				$rs['type'] = 'FULLTEXT';
			elseif('0' == $rs['Non_unique']) 
				$rs['type'] = 'UNIQUE';
			else 
				$rs['type'] = 'INDEX';

			$data[] = $rs;
		}
		Return $data;
	}
	
	// 使用指定数据库　
	function UseDatabases($databases)
	{
		global $Config;
		mysql_select_db($databases);
		$this -> _query("SET NAMES '" . str_replace('-', '', $Config['CharSet'])  . "'");
	}

	function GetError()
	{
		Return  '#<b>'.mysql_errno() . "</b> : " . mysql_error();
	}
 
}

?>