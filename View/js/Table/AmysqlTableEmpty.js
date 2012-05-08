/************************************************
 *
 * Amysql AMS
 * Amysql.com 
 * @param Object 
 * AmysqlTableEmpty  
 *
 */
 //*****************************************

ExtendArray.push({
	'_Events':[
		{'_Event':
			{'load':function ()
				{
					// 清空数据
					NavigationObject.add({
						'id':'N_empty', 'text':L.Empty, 'defaults':DefaultActive.TableEmpty, 'content':'operation_sql', 
						'functions':function ()
						{
							SqlSubmitFormObject.operation_sql_text.value = 'TRUNCATE TABLE `' + SqlKeyword(TableName) + '`';
							SqlSubmitFormObject.UpOperationSqlNotice('确定<font color="red"><b> 清空 </b></font>数据表: ' + TableName + ' ?' , 0);
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
			'ExtendId':'TableEmpty',
			'ExtendName':'清空数据表',
			'ExtendAbout':'清空数据表扩展。',
			'Version':'1.00',
			'Date':'2012-04-06',
			'WebSite':'http://amysql.com',
			'PoweredBy':'Amysql'
	}
})
// ExtendArray End *****************
