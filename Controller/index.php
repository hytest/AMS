<?php

class index extends AmysqlController
{
	public $indexs = null;

	// 加载数据模型与函数类
	function AmysqlModelBase()
	{
		if($this -> indexs) return;
		$this -> _class('Functions');
		$this -> indexs = $this ->  _model('indexs');
	}

	// AMF首页 ******************
	function IndexAction()
	{
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



}