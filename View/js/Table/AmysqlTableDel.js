/************************************************
 *
 * Amysql AMS
 * Amysql.com 
 * @param Object 
 * AmysqlTableDel  
 *
 */

ExtendArray.push({
	'_Events':[
		{'_Event':
			{'load':function ()
				{
					// 删除表 
					NavigationObject.add({
						'id':'N_del', 'text':L.Drop, 'defaults':DefaultActive.TableDel, 'content':'operation_sql', 
						'functions':function ()
						{
							SqlSubmitFormObject.ActionOperation.value == 1;
							SqlSubmitFormObject.operation_sql_text.value = 'DROP TABLE `' + SqlKeyword(TableName) + '`';
							SqlSubmitFormObject.UpOperationSqlNotice('确定<font color="red"><b> 删除 </b></font>数据表: ' + TableName + ' ?' , 0);
							SqlSubmitFormObject.confirm_sql.style.display = 'inline';
						}
					});
					// End **
				}
			},
		'_EventObj':window
		}
	],
	'_ExtendInfo':{
			'ExtendId':'TableDel',
			'ExtendName':'删除表',
			'ExtendAbout':'删除数据表扩展。',
			'Version':'1.00',
			'Date':'2012-04-06',
			'WebSite':'http://amysql.com',
			'PoweredBy':'Amysql'
	}
})
// ExtendArray End *****************