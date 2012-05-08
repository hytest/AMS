<!-- SQL -->
<?php
	// 当前URL不含 GET SQL
	// preg_match('/c=(.*)&a=(.*)&/iU', $_SERVER['QUERY_STRING'], $url);
	$c = isset($_GET['c']) ? '&c=' . urlencode($_GET['c']) : '';
	$a = isset($_GET['a']) ? '&a=' . urlencode($_GET['a']) : '';
	$DatabaseName = isset($_GET['DatabaseName']) ? '&DatabaseName=' . urlencode($_GET['DatabaseName']) : '';
	$TableName = isset($_GET['TableName']) ? '&TableName=' . urlencode($_GET['TableName']) : '';
	$url = 'index.php?' . $c . $a . $DatabaseName . $TableName;
?>
<div style="width:97.3%">
	<form id="SqlForm" name="SqlForm" method="POST" target="GetTableData" action="<?php echo $url;?>">

		<!-- SQL操作提示-->
		<?php
			$OperationQueryShow = ($OperationQuery != '') ? 'style="display:block"' : ''; 
		?> 
		<div id="operation_sql" <?php echo $OperationQueryShow;?> >
			<?php if (empty($OperationQuery[0])) { ?>
			<!-- 操作SQl执行成功 -->
			<div class="SqlNotice" >
				<div id="OP_SqlStatus" class="SqlSuccess">
					 <input type="button" value="关闭" class="execute_sql" id="cancel_confirm_sql"/>
					 <input type="button" value="确认操作>>" class="execute_sql" id="confirm_sql"/>
					<b id="OP_SqlICOStatus" class="ico ico_sqlsuccess"></b>
					<font id="OP_SqlNotice" class="OSqlNotice">SQL已完美执行， 影响数据有 <b><?php echo $OperationQuery[1];?></b> 行!</font>
				 <br style="line-height:9px;"/>
				<div class="c"></div>
				</div>
			</div>
			<textarea id="operation_sql_text" name="operation_sql_text"><?php echo $operation_sql_text;?></textarea>
			<?php } else { ?>
			<!-- 操作SQl执行失败 -->
			<div class="SqlNotice" >
				<div id="OP_SqlStatus" class="SqlError">
					 <input type="button" value="关闭" class="execute_sql" id="cancel_confirm_sql"/>
					 <input type="button" value="确认操作>>" class="execute_sql" id="confirm_sql"/>
					<b id="OP_SqlICOStatus" class="ico ico ico_sqlError"></b>
					<font id="OP_SqlNotice" class="OSqlNotice">影响数据有 <b><?php echo $OperationQuery[1];?></b> 行! 错误语句: <br /><?php echo $OperationQuery[0];?></font>
				 <br style="line-height:9px;"/>
				<div class="c"></div>
				</div>
			</div>
			<textarea id="operation_sql_text" name="operation_sql_text" class="warning_sql_text" ><?php echo $operation_sql_text;?></textarea>
			<?php } ?>
			<input type="hidden" name="ActionOperation" id="ActionOperation" value="0" /> <!-- 是否执行operation_sql_text -->
		</div>


		<!-- 查询SQL -->
		<div id="SqlNotice" class="SqlNotice" >
			<?php if ($SqlStatus) { ?>
				<!-- 查询成功 -->
				<div id="SqlStatus" class="SqlSuccess">
					<input type="submit" value="执行>>" class="execute_sql"/>
					<b id="SqlICOStatus" class="ico ico_sqlsuccess"></b>
					<font id="OSqlNotice" class="OSqlNotice" >
					以下<font title="<?php echo $sql;?>" color="blue">SQL</font>执行完成，总 <b><?php echo number_format($SqlData[2]);?></b> 条记录。
					 ( 系统查询<font title="<?php echo $NewSql;?>" color="blue">SQL</font>花费 <?php echo $SqlData[1];?> 秒， 当前显示以下<font title="<?php echo $sql;?>" color="blue">SQL</font>的第 <i><?php echo $StartRead;?> ~ <?php echo ($SqlData[2] < $PageShow || $QueryResultSum < $PageShow) ? $SqlData[2] : $StartRead+$PageShow;?> &nbsp;</i>行。)</font>
				 <br style="line-height:9px;"/>
				<div class="c"></div>
				</div>
			<?php } else { ?>
				<!-- 查询失败 -->
				<div id="SqlStatus" class="SqlError">
					<input type="submit" value="执行>>" class="execute_sql"/>
					<b id="SqlICOStatus" class="ico ico ico_sqlError"></b>
					<font id="OSqlNotice" class="OSqlNotice" >
						<?php echo $SqlData[0];?>
					</font>
				 <br style="line-height:9px;"/>
				<div class="c"></div>
				</div>
			<?php } ?>
		</div>


		<input type="hidden" name="original_sql"  id="SqlformoOriginal"  value="<?php echo $sql. "\n\n\n";?>"/>
		<input type="hidden" name="page"  id="SqlformPage" value="1"  />
		<textarea  id="sql_post" name="sql" ><?php echo $sql. "\n\n\n";?></textarea>
	</form>
</div> 
<iframe src="" scrolling="auto" id="GetTableData" name="GetTableData"></iframe>

