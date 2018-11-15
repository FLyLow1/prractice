<?php
// 引用 PHPOffice spreadSheet类（PHPExcel的升级版）
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 我们可以模仿（参考）系统中原有的控制器（如site控制器）来写代码
// 创建了一个名为Security（中文含义是安全的意思）的控制器，他继承自IController基类
class Security extends IController{
	// 一 login的方法，展示登录表单
	public function login(){
		//echo 1;
		// 渲染（加载）视图
		$this->redirect('login');		// 这个$this->login其功能等同于TP中的$this->display，但在这里不能使用$this->display，因为这是iwebshop，不是TP，iwebshop封装了自己的一套MVC语法
	}

	// 实现登录操作
	public function login_ok(){
		$adminname=IReq::get('adminname');
		$password=md5(IReq::get('password'));		// 接收用户输入的密码，使用md5对其加密，以便和表中存储密码对比
		// 调用IModel类的getObj方法查询数据，getObj返回单条数据（一维数组）
		$admin1=new IModel('admin1');
		$data=$admin1->getObj("adminname='$adminname' and password='$password'");
		// echo '<pre/>';
		// print_r($data);
		if($data){
			//echo '登录成功';
			// 将管理员的用户名存放到Session中
			$session=new ISession;
			$session->set('adminname',$adminname);
			// 显示用户列表页
			$this->redirect('/security/employee_list',true);
		}else{
			echo '登录失败';
		}

	}

	// 二 展示注册表单
	public function reg(){
		$this->redirect('reg');		// 渲染（加载）模板
	}

	// 三 实现注册功能（用户信息入库）
	public function reg_ok(){
		// 1 接收表单数据
		$username=IReq::get('username');
		$password=md5(IReq::get('password'));		// md5加密
		$truename=IReq::get('truename');
		$salary=IReq::get('salary');
		// 2 入库
		$user1=new IModel('user1');			// 实例化IModel类，使用$user1就可以以面向对象方式操作iwebshop_user1表了
		$data=[
			'username'=>$username,
			'password'=>$password,
			'truename'=>$truename,
			'salary'=>$salary,
		];
		$user1->setData($data);				// 调用需要入库的值
		$user1->add();						// 执行入库操作
	}

	// 四 展示用户列表、搜索、缓存
	public function employee_list(){
		$redis=new Redis;
		$redis->connect('127.0.0.1',6379);

		// 1 new一个IModel类
		$user1=new IModel('user1');

		// 获取用户的搜索关键词
		if(isset($_POST['keyword']) && !empty($_POST['keyword'])){
			$keyword=IReq::get('keyword');

			// 判断缓存中是否存在搜索过的数据
			if($redis->exists($keyword)){
				// 从缓存中读取数据
				$str=$redis->get($keyword);
				$data=json_decode($str,true);
				echo 'from redis';
			}else{
				$data=$user1->query("username like '%$keyword%'");		// 首次搜索，从数据库中查询
				// 搜索操作存储缓存（内存条）中
				$str=json_encode($data);		// 将查询结果（$data）转换成json字符串，以字符串形式存入redis的string类型中
				$redis->set($keyword,$str);
				echo 'from db';
			}
			
		}else{
			// 2 调用query方法，查询全部数据（返回二维数组）
			$data=$user1->query();
		}
		// echo '<pre/>';
		// print_r($data);die;
		// 向前台模板传递数据，在前台中可以遍历$data
		$this->setRenderData(['data'=>$data]);
		$this->redirect('employee_list');
	}

	// 五 导出、管理员操作记录到日志表
	public function export(){
		require 'plugins/vendor/autoload.php';			// 包含自动加载文件
		$spreadsheet = new Spreadsheet();				// 实例化工作簿
		$sheet = $spreadsheet->getActiveSheet();		// 获取工作表对象
		$sheet->setCellValue('A1', 'Hello World !');	// 设置A1单元格的值

		// 查询数据库，得到要导出的数据
		// 方法 1
		$user1=new IModel('user1');
		$data=$user1->query();
		// 方法2	使用PDO读写数据库
		// $pdo=new \PDO('mysql:host=localhost;dbname=iwebshop','root','root');
		// $data=$pdo->query('select * from iwebshop_user1')->fetchAll();
		// echo '<pre/>';
		// print_r($data);die;
		
		// 将$data数组中的数据填充到Excel的单元格中
		$spreadsheet->getActiveSheet()
		    ->fromArray(
		        $data,  	 // 具体的数据
		        NULL,        // Array values with this value will not be set
		        'A3'         // 数据区域左上角的位置
		    );

		 $sheet->setTitle('员工工资详情');		// 设置工作表标签


		// 保存文件
		$writer = new Xlsx($spreadsheet);
		$writer->save('public/员工信息1.xlsx');

		// 将管理员用户名、IP地址、导出时间存储到日志表iwebshop_admin_log
		$admin_log=new IModel('admin_log');
		$data=[
			'adminname'=>ISession::get('adminname'),		// 在登录时将管理员的用户名存储到session中，之后在此处读取出来
			'ip'=>$_SERVER['REMOTE_ADDR'],
			'addtime'=>time(),
		];
		$admin_log->setData($data);
		$admin_log->add();

	}

	// 六 导入
	public function import(){
		require 'plugins/vendor/autoload.php';			// 包含自动加载文件
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
		$reader->setReadDataOnly(TRUE);
		$spreadsheet = $reader->load("public/员工信息.xlsx");

		$worksheet = $spreadsheet->getActiveSheet();		// 获取激活的工作表
		// Get the highest row number and column letter referenced in the worksheet
		$highestRow = $worksheet->getHighestRow(); 			// 获取数据区域的行数
		$highestColumn = $worksheet->getHighestColumn(); 	// 获取数据区域的列数
		// Increment the highest column letter
		$highestColumn++;
		// 获取单元格中数据
		for ($row = 3; $row <= $highestRow; ++$row) {
		    for ($col = 'B'; $col != $highestColumn; ++$col) {
		             $data[$row-3][]=$worksheet->getCell($col . $row)->getValue();
		    }
		}
		// 		echo '<pre/>';
		// print_r($data);die;

		// 入库
		$user1=new IModel('user1');
		foreach ($data as $key => $value) {
			$arr=[
				'username'=>$value[0],
				'password'=>$value[1],
				'truename'=>$value[2],
				'salary'=>$value[3],
			];
			$user1->setData($arr);
			$user1->add();	
		}
	}
}