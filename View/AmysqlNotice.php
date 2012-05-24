<?php

/************************************************
 *
 * Amysql AMS
 * Amysql.com 
 * @param Object 出错提示页面
 *
 */

// 任何出错都清除当前验证信息并跳转至登录页面
setcookie('LoginFileName', '', 0);
setcookie('LoginKey', '', 0);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Notice</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="View/js/language/<?php echo $AmysqlLanguage;?>.js"></script>
<script src="View/js/AmysqlFun.js"></script>
</head>
<body>
<style>
body {
	font-family:Arial,微软雅黑,宋体;
	color:#787B8D;
	font-size:12px;
}
</style>
<script>
var LoginError = <?php echo (isset($LoginError)) ? json_encode($LoginError) : "''";?>;
var obj = parent.parent.parent.window ? parent.parent.parent.window : (parent.parent.window ? parent.parent.window : (parent.window ? parent.window : window));
function go()
{
	if(parent.window.List_loading) 
		parent.List_loading('hide');

	if(LoginError != '' && !confirm(JsValue(LoginError)))
		return;

	// 出错跳转到登录页
	var LoginUrl = <?php echo json_encode(_Http . 'index.php?c=index&a=AmysqlLogin');?>;
	obj.location = LoginUrl;
}
go();
</script>
<p>Please Login...</p>
</body>

</HTML>
