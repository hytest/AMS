/************************************************
 *
 * Amysql AMS
 * Amysql.com 
 * @param Object 
 * AmysqlSqlSubmitForm  
 * 
 */

var SqlEdit;

// Loading 
var List_loading;
var loadingObject = {};
//*****************************************

var AmysqlSqlSubmitForm = function ()
{
		
	this.sql_post = G('sql_post');
	this.SqlformoOriginal = G('SqlformoOriginal');
	this.SqlStatus = G('SqlStatus');
	this.SqlICOStatus = G('SqlICOStatus');
	this.OSqlNotice = G('OSqlNotice');
	this.operation_sql = G('operation_sql');
	this.OP_SqlICOStatus = G('OP_SqlICOStatus');

	this.OP_SqlStatus = G('OP_SqlStatus');
	this.operation_sql_text = G('operation_sql_text');
	this.OP_SqlNotice = G('OP_SqlNotice');
	this.confirm_sql = G('confirm_sql');
	this.ActionOperation = G('ActionOperation');
	this.SqlformPage = G('SqlformPage');
	this.SqlForm = G('SqlForm');
	this.cancel_confirm_sql = G('cancel_confirm_sql');

	

	// 更新Sql查询数据提示
	this.UpSqlNotice = function (html, SqlStatus)
	{
		this.SqlStatus.className = SqlStatus ? 'SqlSuccess' : 'SqlError';
		this.SqlICOStatus.className = SqlStatus ? 'ico ico_sqlsuccess' : 'ico ico_sqlError';
		this.OSqlNotice.innerHTML = html;
		this.operation_sql.style.display = 'none';
		set_location_top(0);
	}

	// 更新Sql操作数据提示
	this.UpOperationSqlNotice = function (html, SqlStatus)
	{
		this.operation_sql.style.display = 'block';
		this.OP_SqlStatus.className = SqlStatus ? 'SqlSuccess' : 'SqlError';
		this.OP_SqlICOStatus.className = SqlStatus ? 'ico ico_sqlsuccess' : 'ico ico_sqlError';
		this.operation_sql_text.className = SqlStatus ? 'success_sql_text' : 'warning_sql_text';
		this.OP_SqlNotice.innerHTML = html;
		this.confirm_sql.style.display = 'none';
		set_location_top(0);
	}
	
	// 确认提交操作Sql
	this.ConfirmSqlSubmit = function (Alerts, sql)
	{
		if(typeof(Alerts) == 'string' && Alerts != ''  && !confirm(Alerts)) return;	// 确认提示
		this.ActionOperation.value = 1;
		if(sql && sql != '') this.operation_sql_text.value = sql;
		this.SqlForm.onsubmit(parseInt(this.SqlformPage.value));
	}

	with(this)
	{
		// Sql编辑
		SqlEdit = CodeMirror.fromTextArea(this.sql_post, {
			lineNumbers: true,
			matchBrackets: true,
			indentUnit: 4,
			mode: "text/x-plsql"
		  });

		// Sql表单提交
		SqlForm.onsubmit = function (page)
		{
			SqlformPage.value = (typeof(page) == 'number') ? page : 1;
			List_loading();	
			if(page) this.submit();
		}

		// 点击确认操作SQL
		confirm_sql.onclick = function ()
		{
			ConfirmSqlSubmit(L.Reconfirmed + '：' + (OP_SqlNotice.innerText ? OP_SqlNotice.innerText : OP_SqlNotice.textContent));
		}

		// 取消确认操作SQL
		cancel_confirm_sql.onclick = function ()
		{
			ActionOperation.value = 0;
			operation_sql.style.display = 'none';
		}
	}

}


// 表单分页对象 **************************
var PageList = function (id)
{
	this.Item = new Array();
	this.listT = G(id);
	this.add = function (page, type, txt)
	{
		var o = {};
		o.id = page;
		if(type == 'button')
			o.H = C('input', {'type':'button', 'value':txt});
		else
		{
			o.H = C('a', {'innerHTML':page, 'href':'javascript:'});
			if(type == 'now') C(o.H, {'className':'page_now'});
		}
		o.H.onclick = function ()
		{
			SqlSubmitFormObject.sql_post.value = SqlSubmitFormObject.SqlformoOriginal.value;
			SqlSubmitFormObject.SqlForm.onsubmit(page);
		}
		this.Item.push(o);
	}

	this.set = function(page, total_page)
	{
		var start,end;
		if(page-7>0)
		{
			start = page-7;
			if(page+7<total_page)
				end=page+7;	
			else
			{
				if(total_page-10>0)
					start=total_page-10;
				else
					start=1;
				end=total_page;
			}
		}
		else
		{
			start=1;
			if(total_page<15)
				end=total_page;
			else 
				end=15;
		}
		if(start > 1)
			this.add(1, 'button', '<<');

		if(page>8)
			this.add(page-1, 'button', '<');
 		
		for(var i=start;i<=end;i++)
		{
			if (i != page)
				this.add(i);
 			else
				this.add(i, 'now');
 		}
		
		if(end<total_page)
			this.add(page+1, 'button', '>');
 		if(end!=total_page)
			this.add(total_page, 'button', '>>');
	}
	this.show = function ()
	{	
		if(this.Item.length == 0) return;
		this.listT.innerHTML = '';
		for (var x in this.Item)
			C(this.listT, 'In', this.Item[x].H);
	}
	
}


// 数据加载Loading 
var List_loading = function (type)
{
	if(!window.TableDataObject || TableDataObject.table == null || !TableDataObject.Item.length) return;
	if (type == 'hide')	// 删除
	{
		if(document.all)  { TableDataObject.table.style.filter = 'alpha(opacity=100);'; }
		TableDataObject.table.style.opacity = 1;
		// 可能存在hide多次的情况，不能hide就算拉。
		try { TableDataObject.list.removeChild(loadingObject); }
		catch (e){ return; }
		return true;
	}
	TableDataObject.table.style.opacity = 0.4;
	if(document.all)  { TableDataObject.table.style.filter = 'alpha(opacity=40);'; }
	loadingObject = C(C('div', 'In', new Array(C('span', 'In', '<b>Loading.......</b>'), C('div', {'id':'page_loading_img'}) )), {'id':'page_loading'});
	
	var top_height = (getScrollTop() - G('ListPageOffsetTop').offsetTop -100) < 180 ? 180 : (getScrollTop() - G('ListPageOffsetTop').offsetTop + 200);
	loadingObject.style.top = parseInt(TableDataObject.list.offsetHeight) < 580 ? TableDataObject.list.offsetHeight /2 + 'px' :  top_height + 'px';
	loadingObject.style.bottom = 'auto';

 	TableDataObject.list.appendChild(loadingObject);
}