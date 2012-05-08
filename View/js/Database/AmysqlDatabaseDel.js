/************************************************
 *
 * Amysql AMS
 * Amysql.com 
 * @param Object 
 * AmysqlDatabaseDel  
 *
 */

ExtendArray.push({
	'_Events':[
		{'_Event':
			{'load':function ()
				{
					// 删除表 
					NavigationObject.add({
						'id':'N_del', 'text':L.Drop, 'defaults':false, 'content':'operation_sql', 
						'functions':function ()
						{
							SqlSubmitFormObject.ActionOperation.value == 1;
							SqlSubmitFormObject.operation_sql_text.value = 'DROP DATABASE `' + SqlKeyword(DatabaseName) + '`';
							SqlSubmitFormObject.UpOperationSqlNotice('确定<font color="red"><b> 删除 </b></font>数据库: ' + DatabaseName + ' ?' , 0);
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
			'ExtendId':'DatabaseDel',
			'ExtendName':'删除数据库',
			'ExtendAbout':'删除数据库扩展。',
			'Version':'1.00',
			'Date':'2012-04-06',
			'WebSite':'http://amysql.com',
			'PoweredBy':'Amysql'
	}
})
// ExtendArray End *****************
