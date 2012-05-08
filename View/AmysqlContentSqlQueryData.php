<font id="SqlNotice"> 
	以下<font title="<?php echo $sql;?>" color="blue">SQL</font>执行完成，总 <b><?php echo number_format($SqlData[2]);?></b> 条记录。
	( 系统查询<font title="<?php echo $NewSql;?>" color="blue">SQL</font>花费 <?php echo $SqlData[1];?> 秒， 当前显示以下<font title="<?php echo $sql;?>" color="blue">SQL</font>的第 <i><?php echo $StartRead;?> ~ <?php echo ($SqlData[2] < $PageShow || $QueryResultSum < $PageShow) ? $SqlData[2] : $StartRead+$PageShow;?> &nbsp;</i>行。)
</font>

<script>
with(parent.window)
{
	sql = <?php echo json_encode($sql);?>;
	CanEdit = <?php echo $CanEdit;?>;	
	UpSqlOriginal = <?php echo json_encode($UpSqlOriginal);?>;

	TableDataFieldList = <?php echo isset($SqlData[3]) ? json_encode($SqlData[3]): '[]';?>;		// 查询结果字段数据
	TableDataArray = <?php echo $SqlStatus ? json_encode($SqlData[0]) : '[]';?>;				// 查询结果数据

	SqlStatus = <?php echo $SqlStatus;?>;												// Sql查询数据是否成功
	QueryResultSum = <?php echo $QueryResultSum;?>;										// 当前数据结果数量
	SqlTableSum = <?php echo $SqlData[2];?>;											// 所有数据总量

	OperationQuery = <?php echo json_encode($OperationQuery);?>	// 操作Sql结果
	page = <?php echo $page;?>;									// 当前页码
	PageShow = <?php echo $PageShow;?>;							// 一页显示
	PageSum = <?php echo ceil($SqlData[2]/$PageShow);?>;		// 总页数量


	// 表数据重写 **********************************
	// 没有操作Sql的情况 OR 操作没报错才重写数据
	List_loading('hide');
	if(!OperationQuery[0] || OperationQuery[0] == null)
	{
		TableDataObject = new TableData();
		TableDataObject.ThItem = TableDataFieldList;
		// 如果有结构字段就进行判断
		if(CanEdit && window.sqlField_IN_TableField && !sqlField_IN_TableField(TableDataObject.ThItem)) CanEdit = false;

		TableDataObject.AddItem(TableDataArray);
		TableDataObject._IfShow = false;
		TableDataObject.show();	// 显示
		
		// 有分页列表
		if(G('PageListTop'))
		{
			PageObject = new PageList('PageListTop');
			PageObject.set(page, PageSum);
			PageObject.show();
			C(PageObject.listT, 'In', C(C('span', 'In', ScreenSum), {'className':'ScreenSumBlock'}));
		}
	}

	// 激活内容块
	SqlSubmitFormObject.ActiveSetIng = true;	// 激活设置进行中
	NavigationObject.ActiveSet(NavigationObject.Item[ActiveSetID ? ActiveSetID : DefaultActiveSetID]);
	ActiveSetID = null;
	SqlSubmitFormObject.ActiveSetIng = false;	// 激活完成


	// 更新页面的SQL
	if(UpSqlOriginal) SqlSubmitFormObject.SqlformoOriginal.value = sql;
	SqlEdit.setValue(sql + "\n\n\n");
	SqlSubmitFormObject.sql_post.value = sql;

}

var SqlNotice = parent.SqlStatus ? document.getElementById('SqlNotice').innerHTML : <?php echo json_encode($SqlData[0]);?>;
parent.SqlSubmitFormObject.UpSqlNotice(SqlNotice, parent.SqlStatus);
parent.SqlSubmitFormObject.ActionOperation.value = 0;



 
// 显示操作sql 的结果　
if(parent.window.OperationQuery != '')
{
	if(parent.window.OperationQuery[0])	// 执行有报错
		parent.window.SqlSubmitFormObject.UpOperationSqlNotice('影响数据有 <b>' + parent.window.OperationQuery[1] + '</b> 行! 错误语句: <br />' + parent.window.OperationQuery[0], 0);
	else
		parent.window.SqlSubmitFormObject.UpOperationSqlNotice('SQL已完美执行， 影响数据有 <b>' + parent.window.OperationQuery[1] + '</b> 行!' , 1);

	parent.window.SqlSubmitFormObject.operation_sql_text.value = <?php echo json_encode($operation_sql_text);?>;
}
</script>
