<?php

/************************************************
 *
 * Amysql AMS
 * Amysql.com 
 * @param Object 常用函数集
 *
 */

class Functions
{
	// 查询的SQl与实际名称互转 *********
	function SqlKeyword($str)
	{
		$str = str_replace(array('`'), array('``'), $str);
		Return $str;
	}
	function _SqlKeyword($str)
	{
		$str = str_replace(array('``','\\\`'), array('`'), $str);
		Return $str;
	}
	// ******************************

	// 过滤Sql其余字符
	function FilterSqlElse($sql)
	{
		$sql = trim($sql);
		$sql = str_replace(array("\n", "\r", "\t"), ' ', $sql);
		Return $sql;
	}

	// 判断是否为获取数据的sql
	function is_QueryData($sql)
	{
		$sql = self::FilterSqlElse($sql);
		if (preg_match('/^(SELECT|SHOW|DESCRIBE|EXPLAIN|CHECK|ANALYZE|REPAIR|OPTIMIZE)\s+(.*)$/i', $sql))
			Return true;
		Return false;
	}

	// 判断是否为select 查询数据表
	function is_select_QueryData($sql)
	{
		$sql = self::FilterSqlElse($sql);
		if(stripos($sql, 'SELECT') === 0)
			Return true;
		Return false;
	}


	// 判断是否为structure SQl
	function is_structure($sql)
	{
		$sql = self::FilterSqlElse($sql);
		if (preg_match('/^(CREATE|ALTER|DROP|FLUSH)\s+(VIEW|TABLE|DATABASE|SCHEMA)\s+/i', $sql))
			Return true;
		Return false;
	}

	// 判断是否存在Limit
	function ExistLimit($sql)
	{
		$sql = self::FilterSqlElse($sql);
		if (preg_match('/^(.*)\s+LIMIT\s+([0-9]{0,})\s*([\,]{0,})\s*([0-9]{1,})$/i', $sql))
			Return true;
		Return false;
	}

	// 是否为查询数据表列表
	function ShowTableStatus($sql)
	{
		$sql = self::FilterSqlElse($sql);
		if (preg_match('/^SHOW\s+TABLE\s+STATUS\s+FROM(.*)/i', $sql))
			Return true;
		Return false;
		
	}

	// 是否为查询数据库列表
	function ShowDatabases($sql)
	{
		$sql = self::FilterSqlElse($sql);
		if (preg_match('/^SHOW\s+DATABASES\s*(.*)$/i', $sql))
			Return true;
		Return false;
		
	}

	// Amysql系统SQL
	function AmysqlSql($sql)
	{
		$sql = self::FilterSqlElse($sql);
		if (preg_match('/^SHOW\s+SQL\s+HELP\s*(.*)$/i', $sql))
			Return 'DataFile_show sql help';
		elseif (preg_match('/^SHOW\s+ABOUT\s*(.*)$/i', $sql))
			Return 'DataFile_show about';
		Return false;
	}
	
	// Amysql系统SQL数据
	function AmysqlSqlData($data , $MysqlFetchType)
	{
		$data = json_decode($data);
		if($MysqlFetchType ==  MYSQL_ASSOC)
			Return $data;

		$_data = array();
		foreach ($data[0] as $key=>$val)
		{
			$_var = array();
			foreach ($val as $k=>$v)
				$_var[] = $v;
			$_data[] = $_var;
		}
		$data[0] = $_data;
		unset($_data);
		Return $data;
	}



	// ***********************************************************
	/**
	 * 匹配与分隔字符串函数 
	 * Amysql AMS
	 * Amysql.com 
	 * @param string $SplitTag  分隔符，为null不分隔。
	 * @param string $preg		正则规则，为null不进行匹配。
	 * @param string $TempName	匹配的数据保存至SaveTempName的名称。
	 *
	 */
	function StrSplit($sql, $SplitTag, $preg, $TempName)
	{
		global $LoadFunction, $SaveTempName;
		$SaveTempName = $TempName;
		if (!$LoadFunction)
		{
			// 保存字段与条件字符串至$SaveTempSql
			function SaveTempSql($match)
			{
				global $SaveTempSql, $SaveTempName, $sql;
				$k = strpos($sql, $match[0]) . '_' . rand(0,999999);	// 位置加随机号
				$SaveTempSql[$SaveTempName][$k] = $match[0];
				Return "[$SaveTempName" . $k . ']';
			}
			
			// 还原函数
			function GetTempSql($match)
			{
				global $SaveTempSql, $SaveTempName;
				if(!isset($SaveTempSql[$SaveTempName][$match[1]]))
					Return $match[0];
				Return $SaveTempSql[$SaveTempName][$match[1]];
			}
		}
		$LoadFunction = true;	// 只是首次加载函数

		// $preg不为NULL才进行匹配
		if($preg !== null) $sql = preg_replace_callback($preg, 'SaveTempSql', $sql);

		// $SplitTag不为NULL才进行分隔，并还原$SaveTempSql数据
		if($SplitTag !== null)
		{
			if ($SplitTag != '')
			{
				$SqlArr = explode($SplitTag, $sql);
				foreach ($SqlArr as $key=>$val)
				{
					$SqlArr[$key] = trim(preg_replace_callback('/\['. $SaveTempName. '([0-9\_]+)\]/iU', 'GetTempSql', $val));
					if (empty($SqlArr[$key]))
						unset($SqlArr[$key]);
				}
				Return $SqlArr;
			}

			Return trim(preg_replace_callback('/\['. $SaveTempName. '([0-9\_]+)\]/iU', 'GetTempSql', $sql));
		}
		
		Return $sql;
	}

	// 取得字符串实际库与表名称数组 
	function GetStrDatabaseTableName($FindStr, $Type = null)
	{
		$FindStr = self::FilterSqlElse($FindStr);

		$TableNameArr = explode('.', $FindStr);
		$Return = null;
		if (!empty($FindStr))
		{
			$tag = '`';
			if(count($TableNameArr) == 2)					// 同时有库名与表名
				$Return = array('DatabaseName'=>$TableNameArr[0],'TableName'=>$TableNameArr[1]);
			elseif(count($TableNameArr) == 1)
			{
				$Return = (strcmp($Type, 'DATABASE') == 0) ? array('DatabaseName'=>$TableNameArr[0],'TableName'=>'') : array('DatabaseName'=>'','TableName'=>$TableNameArr[0]);
			}
			
			if(isset($Return['DatabaseName'][0]) && $Return['DatabaseName'][0] != '`') $Return['DatabaseName'] = '`' . $Return['DatabaseName'] . '`'; 
			if(isset($Return['TableName'][0]) && $Return['TableName'][0] != '`') $Return['TableName'] = '`' . $Return['TableName'] . '`'; 

			$Return['DatabaseName'] = '`' . self::_SqlKeyword(substr($Return['DatabaseName'],1,-1)) . '`';	// Sql转实际库名
			$Return['TableName'] = '`' . self::_SqlKeyword(substr($Return['TableName'],1,-1)) . '`';			// Sql转实际表名
		}
		else
			$Return = array('DatabaseName'=>'``','TableName'=>'``');

		Return $Return;
	}

	// ***********************************************************


	// 判断更新Left返回库表名
	function LoadLeft($sql)
	{
		$sql = self::FilterSqlElse($sql);
		if (preg_match('/^(CREATE|DROP)\s+(VIEW|TABLE|DATABASE|SCHEMA)\s*/i', $sql))
		{
			// 删除库或表  ************
			if (stripos($sql, 'DROP') === 0)
			{
				preg_match_all('/^DROP\s+(VIEW|TABLE|DATABASE)\s*(.*)/i', $sql, $data);
				$TableList = trim($data[2][0]);

				// 有多个需要进行拆分处理
				$preg = "/\\\`/iU";
				$TableList = self::StrSplit($TableList, null, $preg, 'self');
				$preg = "/`(.*)`/iU";
				$TableList = self::StrSplit($TableList, ',', $preg, 'splitIng');
				foreach ($TableList as $key=>$val)
					$TableList[$key] = self::StrSplit($val, '', null, 'self');

				if (is_array($TableList) && count($TableList) > 0)
				{
					foreach ($TableList as $key=>$val)
					{
						$TableList[$key] = self::GetStrDatabaseTableName($val, $data[1][0]);
						$TableList[$key]['type'] = 'del';
					}
				}
			}
			else
			{
				if (preg_match('/^CREATE\s+TABLE\s*/iU', $sql))
				{
					// 新增数据表 ************
					preg_match_all('/^CREATE\s+TABLE\s*(.*)\(/iU', $sql, $data);
					if(!empty($data[1][0])) 
						$TableList[0] = self::GetStrDatabaseTableName($data[1][0]);
					$TableList[0]['TableType'] = 'TABLE';
				}
				elseif (preg_match('/^CREATE\s+VIEW\s*/iU', $sql))
				{
					// 新增视图表 ************
					preg_match_all('/^CREATE\s+VIEW\s*(.*)\s*AS/iU', $sql, $data);
					if(!empty($data[1][0])) 
						$TableList[0] = self::GetStrDatabaseTableName($data[1][0]);
					$TableList[0]['TableType'] = 'VIEW';
				}
				elseif (preg_match('/^CREATE\s+DATABASE\s*/iU', $sql))
				{
					// 新增数据库 ************
					// 存特殊符号
					$preg = "/(\\\`)|(``)/iU";
					$sql = self::StrSplit($sql, null, $preg, 'self_A');
					$preg = "/`(.*)`/iU";
					$sql = self::StrSplit($sql, null, $preg, 'self_B');

					// 匹配数据库名称
					preg_match_all('/^CREATE\s+DATABASE\s*((?:([\w]+))|(?:(\[[^\]]+\])))/i', $sql, $data);
					
					// 恢复特殊符号
					$sql = self::StrSplit($data[1][0], '', null, 'self_B');
					$sql = self::StrSplit($sql, '', null, 'self_A');
					$TableList[0] = self::GetStrDatabaseTableName($sql, 'DATABASE');
				}

				$TableList[0]['type'] = 'add';
			}
			
			Return $TableList;
		}
		Return false;
	}


	// 以分号进行分隔SQL
	function SplitSql($sql)
	{
		// $sql = self::FilterSqlElse($sql);
		$sql = trim($sql);	// 2012-05-03改成首尾过滤回车空格

		// 首先把特殊的符号 \` 或 \' 或 \" 匹配存至$SaveTempSql[self]并返回值，避免影响下面的匹配。
		$preg = "/(\\\')|" . '(\\\")' . "|(\\\`)/iU";
		$sql = self::StrSplit($sql, null, $preg, 'self');

		// 接着以`` 或 '' 或 "" 进行匹配存至$SaveTempSql[splitIng]，接着以分号分隔并恢复$SaveTempSql[splitIng]的数据。
		$preg = "/\'(.*)\'|`(.*)`|\"(.*)\"/iU";
		$sql = self::StrSplit($sql, ';', $preg, 'splitIng');

		// 分隔后的SQL恢复$SaveTempSql[self]的数据。
		foreach ($sql as $key=>$val)
			$sql[$key] = self::StrSplit($val, '', null, 'self');

		Return $sql;
	}

	

	// 取得查询SQL的库、表名
	function GetQuerySqlFromName($sql)
	{
		$FindStr = '';
		$sql = self::FilterSqlElse($sql);
		if (preg_match('/^(SELECT|SHOW|DELETE|EXPLAIN)\s+(.*)$/i', $sql))
		{
			preg_match_all('/^(.*)([`|*|\s])(.*)\bFROM\b\s*(.*)\s*(\bUNION\b|\bRRIGHT\b|\bLEFT\b|\bAS\b|\bWHERE\b|\bGROUP\b|\bORDER\b|\bLIMIT\b){1,}\s*(.*)$/iU', $sql, $data);
			if(empty($data[4])) preg_match_all('/^(.*)([`|*|\s])(.*)FROM\s*(.*)\s*$/iU', $sql, $data);
			$FindStr = isset($data[4][0]) ? trim($data[4][0]) : '';

		}
		elseif(strpos($sql, 'INSERT') === 0)
		{
			preg_match_all('/^INSERT\s+INTO\s*(.*)\s*(\(|SET)(.*)$/iU', $sql, $data);
			$FindStr = trim($data[1][0]);
		}
		elseif(strpos($sql, 'UPDATE') === 0)
		{
			preg_match_all('/UPDATE\s*(.*)\s*SET(.*)$/iU', $sql, $data);
			$FindStr = trim($data[1][0]);
		}

		if (preg_match('/^SHOW\s+TABLE\s+STATUS(.*)$/i', $sql))	// 数据库页没有表加.``
			$FindStr .= '.``';

		Return self::GetStrDatabaseTableName($FindStr);
	}



	// 数据库的数据表列表数据处理
	function DBDealWith($data)
	{
		if(!isset($data[3])) Return $data;
		// 新增列'Checksum','PACK_KEYS', 'DELAY_KEY_WRITE'。 这三个属性MyISAM表才拥有。
		$OrderByList = array('Name', 'Rows', 'Auto_increment', 'Engine', 'Collation', 'Comment', 'Row_format','Checksum','PACK_KEYS', 'DELAY_KEY_WRITE');
		foreach ($data[3] as $key=>$val)
		{
			if (!in_array($data[3][$key] -> name, $OrderByList))
				$OrderByList[] = $data[3][$key] -> name;
		}
		
		// 处理每一行数据
		foreach ($data[0] as $key=>$val)
		{
			$NewVal = array();
			foreach ($OrderByList as $v)
			{
				$NotExistsNewAdd = null;
				if($v == 'PACK_KEYS')
				{
					$NotExistsNewAdd = 'DEFAULT';
					(strpos($val['Create_options'], 'pack_keys=0') !== false && $NotExistsNewAdd = '0');
					(strpos($val['Create_options'], 'pack_keys=1') !== false && $NotExistsNewAdd = '1');
				}
				$NewVal[$v] = isset($val[$v]) ? $val[$v] : $NotExistsNewAdd;
			}
			$data[0][$key] = $NewVal;
		}

		$NewVal = array();
		foreach ($OrderByList as $v)
		{
			$NotExists = true;
			foreach ($data[3] as $key=>$val)
			{
				if($val -> name == $v) 
				{
					$NewVal[] = $val;
					unset($data[3][$key]);
					$NotExists = false;
					break;
				}
			}
			if($NotExists)	// 新增加不存在的列名
			{
				$$v -> name = $v;
				$$v -> table = 'TABLES';
				$NewVal[] = $$v;
			}
		
		}
		$data[3] = $NewVal;

		Return $data;
	}


	// *********************************************************************

	// 统计字节
	function CountSize($size) 
	{
		foreach (array('', 'K', 'M', 'G') as $val)
		{
			if($size < 1024) 
				Return round($size, 1) . $val . 'B';
			$size /= 1024;
		}
	}
}

?>