<?php

class index extends AmysqlController
{
	public $indexs = null;

	// 加载数据模型与函数类
	function AmysqlModelBase()
	{
		if($this -> indexs) return;
		$this -> _class('Functions');
		Functions::AutoLogin();				// 自动登录
		$this -> indexs = $this ->  _model('indexs');
	}

	// AMF首页 ******************
	function IndexAction($language = null)
	{
		$this -> AmysqlLanguage = $language ? $language : (isset($_COOKIE['Language']) ? $_COOKIE['Language'] : 'en');
		$this -> _view('index');
	}


	// AMF框架内容 ***************
	function AmysqlContent()
	{
		$this -> AmysqlModelBase();
		$collations = $this -> indexs -> GetCollations();

		// 取得 MySql Collations 
		foreach ($collations as $key=>$val)
			$JsArr[] = "['$key' , ['" . implode("','", $collations[$key]) . "']]";
		$this -> AmysqlCollations = "[''," . implode(",", $JsArr) . ']';

		// 获取支持的引擎列表
		$this -> AmysqlEngines = $this -> indexs -> GetEngines();

		$this -> _view('AmysqlContent');
	}

	// AMF框架标签 **************
	function AmysqlTag()
	{
		$this -> _view('AmysqlTag');
	}

	// AMF框架左栏 *************
	function AmysqlLeft()
	{
		$this -> AmysqlModelBase();
		$this -> _class('DatabaseLeft');		// 载入数据库列表相关对象
		$this -> DatabasesList = json_encode($this -> indexs -> GetDatabasesList());

		$this -> _view('AmysqlLeft');
	}

	// ************************************************************************************************************
	// 登录 
	function AmysqlLogin()
	{
		if (isset($_COOKIE['LoginFileName']) && $_COOKIE['LoginFileName'])
		{
			// 已登录
			$this -> IndexAction();
			exit();
		}
			
		if (isset($_POST['login']))
		{
			$language = $_POST['language'];
			$time = $_POST['time'];
			$user = $_POST['user'];
			$password = $_POST['password'];
			setcookie('Language', $language, time()*1.1);

			$_SESSION['LoginError'] = '{js}L.LoginError{/js}';		// 默认
			$_SESSION['AddTime'] = $time;

			$LoginInfo = array('User' => $user, 'Password' => $password);
			$this -> _DBSet($LoginInfo);
			$this -> indexs = $this -> _model('indexs');
			$this -> indexs -> _query('SELECT 1');

			// 登录成功
			if($this -> indexs -> QueryStatus)
			{
				$TimeArr = array('20I' => 1200, '1H' => 3600, '8H' => 3600*8, '1D' => 86400, '1M' => 86400*30);
				$AddTime = isset($TimeArr[$time]) ? $TimeArr[$time] : 300;
				
				// 登录数据记录
				$FileName = md5($_SERVER['REMOTE_ADDR'].time());		// 数据保存文件名
				$LoginInfo['LoginKey'] = md5(str_shuffle($FileName));	// 登录KEY
				$LoginInfo['ip'] = $_SERVER['REMOTE_ADDR'];				// 登录IP
				$LoginInfo['AvailableTime'] = time() + $AddTime;		// 有效时间
				$LoginInfo['AddTime'] = $AddTime;						// 持续时间
				$LoginInfo['language'] = $language;						// 语言
				$this -> _plus('DataFile_login_' . $FileName . '.login', '<?php //' . json_encode($LoginInfo));

				setcookie('LoginFileName', $FileName, time()*1.1);
				setcookie('LoginKey', $LoginInfo['LoginKey'], time()*1.1);
				setcookie('LoginUser', $user, time()*1.1);
				$_SESSION['LoginError'] = null;
				$_SESSION['AddTime'] = null;
				$this -> IndexAction($language);
			}

			exit();
		}

		if(isset($_SESSION['LoginError']) && $_SESSION['LoginError'])
			$this -> LoginError = $_SESSION['LoginError'];
		$_SESSION['LoginError'] = null;									// 进行登录页面后清除错误信息

		$this -> AmysqlLanguage = isset($_COOKIE['Language']) ? $_COOKIE['Language'] : 'en';
		$this -> _view('AmysqlLogin');
	}

	// 退出
	function logout()
	{
		if(isset($_COOKIE['LoginFileName']) && !empty($_COOKIE['LoginFileName']))
			$this -> _del('DataFile_login_' . $_COOKIE['LoginFileName'] . '.login');
		$this -> _view('AmysqlNotice');
	}
	
	// 系统设置
	function AmysqlSystem()
	{
		$this -> AmysqlModelBase();
		if (isset($_POST['submit']))
		{
			$SystemConfig = array(	'SqlLine' => (int)$_POST['SqlLine'], 'TableDataLine' => (int)$_POST['TableDataLine'], 
									'time' => date('Y-m-d H:i:s', time()), 'language' => $_POST['language']);
			$SystemConfig['LogoutNotice'] = (isset($_POST['LogoutNotice']) && $_POST['LogoutNotice'] == 'on') ? 1 : 0;
			$SystemConfig['SqlUppercase'] = (isset($_POST['SqlUppercase']) && $_POST['SqlUppercase'] == 'on') ? 1 : 0;
			$SystemConfig['TableShowIndex'] = (isset($_POST['TableShowIndex']) && $_POST['TableShowIndex'] == 'on') ? 1 : 0;
			$SystemConfig['SqlBold'] = (isset($_POST['SqlBold']) && $_POST['SqlBold'] == 'on') ? 1 : 0;
			$this -> _plus('DataFile_system_config.system', '<?php //' . json_encode($SystemConfig));
			Functions::SetSystem();
		}

		$SystemConfigData = $this -> _file('DataFile_system_config.system');
		$this -> SystemConfig = json_decode(trim($SystemConfigData, '<?php //'));

		$this -> _view('AmysqlSystem');
	}

}