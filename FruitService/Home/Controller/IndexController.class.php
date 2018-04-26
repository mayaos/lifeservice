<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {

//首页方法	
    public function index(){
		header("Content-Type:text/html; charset=utf-8");
		
		//if(!isset($_SESSION['admin'])){
			//$this->login();//用户尚未登录，直接踢回登录页
			//exit();
		//}else{
			//用户已登录，拉取用户相关留言
			if(isset($_SESSION['admin'])){
				$data['admin']=$_SESSION['admin'];
				$conn = M('message');
				
				//$where['author'] = array('eq',trim($_SESSION['admin']));
				//$where['look'] = array('eq',1);
				//$c = $conn->where($where)->count();
				$this->assign('data',$data);
				$this->assign('message',$c);
			}

			$this->display('index:index');
		//}

    }
    
//登录检测
	public function logincheck(){
		header("Content-Type:text/html; charset=utf-8");
		$conn=M('user');
		echo ($conn);
		//验证帐号密码
		$where['username']=array('eq',trim($_POST['username']));
		$where['password']=array('eq',md5(trim($_POST['password'])));
		
		$row = $conn->where($where)->find();
// 		$info='test trace';
// 		trace($info,'test');
 
        
		if($row){
			session('admin',trim($_POST['username']));
			session('level',$row['level']);
			session('cname',$row['usercname']);

			$c = A('Vip');
			$c->vipcheck();
				
			if($row['level'] ==10){
				$this->success('登录成功','/index.php/home/index/manage',1);
			}else if($row['level'] >1 && $row['level'] <6){
			    //商家的入口
				$this->success('登录成功','/index.php/home/index/shopadmin',1);
			}else if($row['level']==9){
				$this->success('登录成功','/index.php/home/index/manage',1);	
			}else{
			    //用户入口
				//$this->success('登录成功','/index.php/home/index/index',1);	
				$this->success('登录成功','/index.php/home/StoreIndex/index',1);	
			}
		}else{
			$this->error('帐号或密码错误');
			$this->display('index:login');
		}

		//var_dump($row['id']);
	}
	
	//GET登录
		public function logincheckg(){
		 header("Content-Type:text/html; charset=utf-8");
		$conn=M('user');
		//验证帐号密码
		$where['username']=array('eq',trim($_GET['username']));
		$where['password']=array('eq',md5(trim($_GET['password'])));
		
		$row = $conn->where($where)->find();
		if($row){
			session('admin',trim($_GET['username']));
			session('level',$row['level']);
			$c = A('Vip');
			$c->vipcheck();
			if($row['level'] ==10){
				$this->success('登录成功','/index.php/home/index/manage');
			}else if($row['level'] >1 && $row['level'] <6){
				$this->success('登录成功','/index.php/home/index/shopadmin');
			}else if($row['level']==9){
				$this->success('登录成功','/index.php/home/index/manage');	
			}else{
				$this->success('登录成功','/index.php/home/index/index');	
			}
		}else{
			$this->error('帐号或密码错误');

		}
		//var_dump($row['id']);
	}

//GET登录检测APP
	public function logincheckget(){
		 header("Content-Type:text/html; charset=utf-8");
		$conn=M('user');
		//验证帐号密码
		$pwd = $_GET['password'];
		$where['username']=array('eq',$_GET['username']);
		$where['password']=array('eq',md5($_GET['password']));
		
		$row = $conn->where($where)->find();
		if($row){
			$arr= array('status'=>1,'username'=>$row['username'],'password'=>$pwd,'cn'=>$row['usercname'],'id'=>$row['id']);
			$arrstr = json_encode($arr);
			echo $arrstr;
			exit();
		}else{
			echo 0;
			exit();
		}
		//var_dump($row['id']);
	}
//POST登录检测APP
	public function logincheckpost(){
		 header("Content-Type:text/html; charset=utf-8");
		 if($_POST['name']==''){
			echo -1;
			exit();	 
		}
		$conn=M('user');
		//验证帐号密码
		$where['username']=array('eq',$_POST['name']);
		$where['password']=array('eq',md5($_POST['pwd']));
		$pwd = $_POST['pwd'];
		$row = $conn->where($where)->find();
		if($row){
			$arr= array('status'=>1,'username'=>$row['username'],'password'=>$pwd,'cn'=>$row['usercname'],'id'=>$row['id']);
			$arrstr = json_encode($arr);
			echo $arrstr;
			exit();
		}else{
			echo 0;
			exit();
		}

	}



//商家后台
	public function shopadmin(){
		$conn = M('log');
		$cfg = M('user');
		$s = A('Session');
		$s->chksession();
		$w['username'] = array('eq',$u);
		$row = $cfg->where($w)->find();
		$where['uid'] = array('eq',$row['id']);
		$where['paystatus'] = array('eq',1);
		$where['status'] = array('eq',0);
		$rows = $conn->where($where)->count();
		$c = $rows;
		$this->assign('c',$c);
		//var_dump($rows);
		$this->display('index:shopadmin');
	}
	
//商家订单列表 
	public function shoplist(){
		$s = A('Session');
		$s->chksession();
		import("@.ORG.Page"); //导入分页
		$conn=M('log');
		$wheres['pid']=array('eq',$_SESSION['admin']);
		$wheres['status']=array('eq',0);
		$row=$conn->where($wheres)->count(); //统计未核销订单
		
		$cfg = M("user");
		$w['username']= array('eq',$_SESSION['admin']);
		$rs= $cfg->where($w)->find(); //获取当前商家信息
		
		$where['tp_log.pid']=array('eq',$rs['id']);
		$rows=$conn->join("RIGHT JOIN tp_user ON tp_user.username=tp_log.username")->where($where)->field('tp_user.id,tp_user.username,tp_user.tel,tp_log.aid,tp_log.username,tp_log.sid,tp_log.status,tp_log.price,tp_log.cprice,tp_log.logtime,tp_log.pay,tp_log.num,tp_log.danwei,tp_log.shopname,tp_log.paystatus')->select();
		$this->assign('count',$row);
		
		$count = $conn->where($where)->count();
		$pagecount = 10;
		$page = new \Bootstrap\Page($count , $pagecount);
		//$page->parameter = $row; //此处的row是数组，为了传递查询条件
		$page->setConfig('first','首页');
		$page->setConfig('prev','上一页');
		$page->setConfig('next','下一页');
		$page->setConfig('last','尾页');			$page->setConfig('theme',''.'%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% <li><a aria-label="Next"><span aria-hidden="true">第'.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)</span></a></li>');
		$show = $page->show();
		$lists = $conn->where($where)->order('status asc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('lists',$lists);
		$this->assign('page',$show);
		$this->display('index:shoplist');
		//var_dump($row);
		//$UserID=$_GET['']	
	}
	
//商家业绩统计

	public function tongji(){
		$s = A('Session');
		$s->chksession();
		//查询惠民卡发行总数量
		$connect = M('cart');
		$wheres['lock']=array('eq',1);
		$row=$connect->where($wheres)->count();
		$this->assign('count',$row);

		//查询当前商家当天惠民卡消费次数
		$conn = M('log');
		$mwhere['username']=array('eq',$_SESSION['admin']);
		$mwhere['shop_status']=array('eq',0);
		$ywhere['username']=array('eq',$_SESSION['admin']);
		$ywhere['shop_status']=array('eq',1);
		$mrow=$connect->where($mwhere)->count();
		$datas['wy'] = floor($mrow/10);
		$datas['wx'] = $mrow;
		$c=$connect->where($ywhere)->count();
		$data['y']=floor($c/10);
		$data['x']=$c;
		$this->assign('data',$data);
		
		$this->assign('mcount',$datas);
		$sql = "select count(*) as dvip  from tp_log where (status=1 and xftype=1) and xftime between '".strtotime(date('Y-m-d',time()))."' and '".(strtotime(date('Y-m-d',time()))+(3600*24))."'";
		$rows['dayvip'] = $conn->query($sql);
		
		$sqls = "select count(*) as dnet  from tp_log where (status=1 and xftype=0) and xftime between '".strtotime(date('Y-m-d',time()))."' and '".(strtotime(date('Y-m-d',time()))+(3600*24))."'";//当天网络销售
		$rows['daynet'] = $conn->query($sqls);
		
		$yellow=date('Y',time());
		$daytime=date('Y-m',time());
		$mother=date('m',time());
		if($mother>=12){
			$mother=1;
		}else{
			$mother=$mother+1;	
		}
		$end = (strtotime($yellow.'-'.$mother));
		$msqlvip = "select count(*) as mvip  from tp_log where (status=1 and xftype=1) and xftime between '".strtotime($daytime)."' and '".$end."'";
//查询当月惠民卡
		$rows['mothervip'] = $conn->query($msqlvip);
		$msqlnet = "select count(*) as mnet from tp_log where (status=1 and xftype=0) and xftime between '".strtotime($daytime)."' and '".$end."'";
//查询当月网络销售
		$rows['mothernet'] = $conn->query($msqlnet);
		
//查询当前年份的惠民卡
		$y = date('Y',time());
		$endy = $y+1;
		$y = $y.'-1-1';
		$endy = (strtotime($endy.'-1-1')-3600*24);	
		$ysqlvip = "select count(*) as yvip  from tp_log where (status=1 and xftype=1) and xftime between '".strtotime($y)."' and '".$endy."'";	
		$rows['yellowvip'] = $conn->query($ysqlvip);
		
//查询当年网络销售
		$ysqlnet = "select count(*) as ynet  from tp_log where (status=1 and xftype=0) and xftime between '".strtotime($y)."' and '".$endy."'";	
		$rows['yellownet'] = $conn->query($ysqlnet);
		//var_dump($rows);
		//$lists=json_encode($rows);
		$this->assign('lists',$rows);
		
		$this->display('index:tongji');
		
	}
	
//订单标记已消费
	public function shopheyan(){
		$s = A('Session');
		$s->chksession();
		$conn=M('log');
		$data['status']=1;
		$where['aid']=$_GET['id'];
		$ret = $conn->where($where)->save($data);
		if($ret){
			$this->Success('订单核验成功!');	
		}else{
			$this->Error('操作失败，请联系管理员QQ:295440026!');
		}
	}	
	
	public function adminmenu(){
		$cfg = M('type');
		if($_SESSION['level']<10 && $_SESSION['level']>8){
			$where['level'] = array('ELT',9);
		}else if($_SESSION['level']=10){
			$where['level'] = array('ELT',10);
		}else{
			$this->error("您没有权限访问此页面");
			exit();
		}
		

		if($_SESSION['admin']==null){
			$this->error("您的登录已过期","/index.php/home/LoginAction/adminlogin",1);
			exit();
		}
		
		$where['tid']=0;  //一级菜单
		$rows = $cfg->where($where)->select();
		if(isset($_GET['tid'])){
			$_SESSION['tid1'] = $_GET['tid'];
			$tid1 = $_SESSION['tid1'];
			
		}else if(isset($_SESSION['tid1'])){
			$tid1 = $_SESSION['tid1'];
		}else{
			$tid1 = '';	
		}
		if(isset($_GET['thisid'])){
			$thisid = $_SESSION['thisid'] = $_GET['thisid'];
			
		}else if(isset($_SESSION['thisid'])){
			$thisid = $_SESSION['thisid'];
		}else{
			$thisid = $_SESSION['thisid'] = '';
		}
		
		if(!isset($_GET['tid']) && !isset($_GET['thisid'])){
			$tid1 = $_SESSION['tid'] = 5;
			$thisid = $_SESSION['thisid']=13;
			
		}
		
		$w['tid'] = $tid1;
		$w['level']= array('ELT',$_SESSION['level']);
		$rowk = $cfg->where($w)->order("px asc")->select(); 
		

			$this->assign('type1',$tid1);
			$this->assign('thisid',$thisid);
			$this->assign('type2',$rowk);
			$this->assign('adminmenu',$rows);
		
	}

//管理员管理商家列表



//商家删除操作
	public function listdel(){
		$conn = M('user');
		$select_id = $_POST['select'];
		if(count($select_id)>0){
			$conn = M('user');
			$where['id']=array('in',$select_id);
			$val = $conn->where($where)->delete();
			$this->success("删除成功");
		}	
	}


//商家删除操作
	public function user_del(){
		$conn = M('user');
		$select_id = $_POST['select'];
		if(count($select_id)>0){
			$conn = M('user');
			$where['id']=array('in',$select_id);
			$val = $conn->where($where)->delete();
			$this->success("删除成功");
		}	
	}

//新闻删除操作
	public function news_del(){
		$conn = M('news');
		$select_id = $_POST['select'];
		if(count($select_id)>0){
			$conn = M('news');
			$where['id']=array('in',$select_id);
			$val = $conn->where($where)->delete();
			$this->success("删除成功");
		}	
	}

//合作反向操作
	public function updatestatus(){
		 header("Content-Type:text/html; charset=utf-8");
		//var_dump('Success');
		$id=$_POST['select'];
		$conn=M('user');
		$where['id']=array('in',$id);
		$row=$conn->where($where)->select();
		
		
		foreach($row as $val){
			if($val['status']==0){
				$sql['id'] = array('eq',$val['id']);
				$data['status']=1;
				$conn->where($sql)->save($data);
			}else{
				$sql['id'] = array('eq',$val['id']);
				$data['status']=0;
				$conn->where($sql)->save($data);
			}
		   
		}
		$this->success('更新成功');	
		
	}


	public function listedit(){
		header("Content-Type:text/html; charset=utf-8");
		$conn=M('user');
		$id=$_GET['id'];
		$where['id']=array('eq',$id);
		$lists=$conn->where($where)->select();
		$this->assign('lists',$lists);
		$this->display('manage:lists_edit');
	}
	
	public function jiesuan(){
		header("Content-Type:text/html; charset=utf-8");
		$conn=M('log');
		$id=$_GET['id'];
		$where['uid']=array('eq',$id);
		$lists=$conn->where($where)->Sum('price')->select();
		$this->assign('lists',$lists);
		$this->display('manage:lists_edit');
		
	}
	
	public function listeditsave(){
		header("Content-Type:text/html; charset=utf-8");
		$conn=M('user');
		$id=$_POST['editids'];
		$where['id']=array('eq',$id);
		$data['usercname']=$_POST['usercname'];
		$data['username']=$_POST['username'];
		$data['zhekou']=$_POST['zhekou'];
		$data['jiesuan']=$_POST['jiesuan'];
		$data['status']=$_POST['status'];
		$data['yuanjia']=$_POST['yuanjia'];
		
		$data['zhekoujia']=$_POST['zhekoujia'];
		$data['tel']=$_POST['tel'];
		$data['address']=$_POST['address'];
		$data['email']=$_POST['email'];
		$where['id']=array('eq',$id);
		//var_dump($data);
		$ret = $conn->where($where)->save($data);
		if($ret){
			$this->success('保存成功','lists');
		}else{
			$this->error('数据保存失败，请联系技术员');	
		}
		
	}
	

//回到登录页
	public function login(){
		$this->display('index:login');
	}
	
	public function loginout(){
		session('admin',null);
		$this->success('退出成功','/index.php/home/index/login',1);
	}
	
	public function loginout2(){
		session('admin',null);
		$this->success('退出成功','/index.php/home/LoginAction/adminlogin',1);
	}

//管理员权限前往后台
	public function admin(){
		header("Content-Type:text/html; charset=utf-8");
		$this->display();	
	}
	
	public function manage(){
		$conn=M('type');
		$m = A('vip');
		$m->vipregister();
				
// 		$this->display('manage:admin');
	}
	
//商家搜索
	public function search(){
		if(isset($_POST['search'])){
			$keyword = $_POST['search'];
		}else{
			$keyword=$_GET['search'];
		}
		$conn = M('user');
		$where['username']=array('LIKE','%'.$keyword.'%');
		$where['usercname']=array('LIKE','%'.$keyword.'%');
		$where['levle'] =array('eq',5);
		$where['_logic']='OR';

		$row = array("search"=>$keyword,'tid'=>I('get.tid'),'thisid'=>I('get.thisid'));
			$count = $conn->where($where)->count();
			$pagecount =15;
			$page = new \Bootstrap\Page($count , $pagecount);
			$page->parameter = $row; //此处的row是数组，为了传递查询条件
			$page->setConfig('first','首页');
			$page->setConfig('prev','上一页');
			$page->setConfig('next','下一页');
			$page->setConfig('last','尾页');			$page->setConfig('theme',''.'%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% <li><a aria-label="Next"><span aria-hidden="true">第'.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)</span></a></li>');
			$show = $page->show();
			$data = $conn->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
			$this->assign('page',$show);
		$this->assign('searchlist',$data);
		$this->display('manage:searchlist');
	}
	
    public function UserSet(){
		header("Content-Type:text/html; charset=utf-8");
		$conn = M('user');
		$username=$_SESSION['admin'];
		if($username==0){
			$this->error('您还没有登录','/index.php/Home/Index/Login',1);	
		}else{
			//$where['username']=array('eq',$username);
			$row=$conn->join('LEFT JOIN tp_cart ON (tp_user.username = tp_cart.username)')->where("tp_user.username='$username'")->field("tp_cart.sid,tp_user.username,tp_user.jifen,tp_user.tel,tp_user.usercname,tp_user.card,tp_cart.erweima,tp_user.avatar,tp_cart.startime,tp_cart.endtime")->select();

			//$row['cardstr']=substr($rows['card'],0,4)."**********".substr($rows['card'],13,4);
			//var_dump($row);
			$this->assign('lists',$row);
			$this->display('UserSet');
		}
    }	
	
	public function news_list(){
		$conn = M('news');
		$s = A('Session');
		$s->chksession();
		$where['sub_user'] = array('eq',$_SESSION['admin']);
		$row = $conn->where($where)->field("id,title,pic,content,falg")->order('createtime,id desc')->select();
		$this->assign('lists',$row);
		$this->assign('emp','<span class="fontsize2">暂无优惠信息</span>');
		$this->display('index:news_list');	
	}

	public function help_list(){
		$conn = M('zhinan');
		//$where['sub_user'] = array('eq',$_SESSION['admin']);
		$row = $conn->field("id,title,pic,content,falg")->order('createtime,id desc')->select();
		$this->assign('lists',$row);
		$this->assign('emp','<span class="fontsize2">暂无优惠信息</span>');
		$this->display('index:help_list');	
	}
	
	public function message_list(){
		
		// 我们先来读取主题表出来
		$messageModel = M('Message');
		$retmsgMode = M('Retmsg');
		if(isset($_GET['mymsg'])){
			$w['author'] = $_SESSION['admin'];
			$this->assign('all',-1);
		}else if(isset($_GET['system'])){
			$w['ts_all'] = 1;
			$this->assign('all',0);
		}else{
			$w['author'] = $_SESSION['admin'];
			$w['ts_lv'] = array('elt',$_SESSION['level']);
			$this->assign('all',1);
		}
			
	
		$messageData = $messageModel->where($w)->select();
		// 先简单的读取5条主题出来，这样数据已经出来了。我们现在来重新拼装一下这个读取出来的主题的ID，以便于我们去读取回复内容的数据，只读这些。理解吧
		
		$tmp = '';
		foreach($messageData as $k => $v){
			if($tmp){
				$tmp .= ','.$v['id'];
			}else{
				$tmp = $v['id'];
			}
		}
		
		// 处理好了的主题ID值，我们现在来读取主题的回复数据
		$retmsgData = $retmsgMode->where(array('msgid'=>array('in',$tmp)))->select();	// 这样就读到了楼上这20条主题的回复数据了。
		
		
		// 这样就完事了。！！简单吧，我看看

		// 现在将这两个数据输出出去
		$this->assign('retmsgData',$retmsgData);
		$this->assign('messageData',$messageData);
		
	
		$this->display('index:message_list');
		

	}
	
	public function message_art(){
		
		// 我们先来读取主题表出来
		$messageModel = M('Message');
		$retmsgMode = M('Retmsg');	
		$aid = $_GET['id'];
		$updateSql['id'] = $aid;
		$updateData['look'] = 0;
		$readme = $messageModel->where($updateSql)->save($updateData);

		if(isset($_GET['mymsg'])){
			$w['author'] = $_SESSION['admin'];
			$this->assign('all',-1);
		}else if(isset($_GET['system'])){
			$w['author'] = $_SESSION['admin'];
			$w['ts_all'] = 1;
			$this->assign('all',0);
		}else{
			$w['author'] = $_SESSION['admin'];
			$w['ts_lv'] = array('elt',$_SESSION['level']);
			$this->assign('all',1);
		}
			
	
		$messageData = $messageModel->where($w)->select();
		
		// 	
		if($messageData){	
			$tmp = '';
			foreach($messageData as $k => $v){
				if($tmp){
					$tmp .= ','.$v['id'];
				}else{
					$tmp = $v['id'];
				}
			}
			
			// 处理好了的主题ID值，我们现在来读取主题的回复数据
			$retmsgData = $retmsgMode->where(array('msgid'=>array('in',$tmp)))->select();	// 这样就读到了楼上这20条主题的回复数据了。
			
			
			// 这样就完事了。！！简单吧，我看看
	
			// 现在将这两个数据输出出去
			$this->assign('retmsgData',$retmsgData);
			$this->assign('messageData',$messageData);
		}else{
			$this->assign('messageData',$messageData);
		}
		$this->display('index:message_art');
	}	


	public function help_art(){
		$conn = M('zhinan');
		$aid = $_GET['id'];
		//$where['falg'] = array('eq',1);
		$where['id'] = array('eq',$aid);
		$row = $conn->where($where)->field("id,title,pic,content")->select();
		$this->assign('lists',$row);
		$this->display('index:help_art');
	}
	
	public function news_add(){	
		$s = A('Session');
		$s->chksession();
		$conn = M('news');
		$this->display('index:news_add');	
	}	
	
	
	public function news_art(){
		$s = A('Session');
		$s->chksession();
		$conn = M('news');
		$aid = $_GET['id'];
		//$where['falg'] = array('eq',1);
		$where['id'] = array('eq',$aid);
		$row = $conn->where($where)->field("id,title,pic,content")->select();
		$this->assign('lists',$row);
		$this->display('index:news_art');
	}
	
	public function news_add_save(){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize  = 3145728 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath =  './Uploads/';// 设置附件上传目录
		//$upload->savePath = 'images/';
		$upload->autoSub = true;
		$d = date('Ymd',time());
		$upload->subName = array('date','Ymd');
		$upload->saveName = date('YmdHis',time()).rand(1000,9999); 
		
		//var_dump($_FILES['photo1']);
		//exit();
		$infos   =  $upload->uploadOne($_FILES['photo1']);
		if(!$infos) {// 上传错误提示错误信息
			$this->error($upload->getError());
			exit();
		}
		
		$conn = M('news');
		$data['title']=$_POST['title'];
		$data['pic']='/Uploads/'.$d."/".$upload->saveName.".".$infos['ext'];
		$data['content']=$_POST['content'];
		$data['sub_user']= $_SESSION['admin'];
		$data['createtime']=time();
		$row = $conn->add($data);
		$this->Success('资讯发布成功，等待管理员审核，审核通过后将会在资讯页显示。','/index.php/Home/Index/news_list');
		exit();
		
	}
	
	public function ajax_upload(){
		$upload = new \Think\Upload();// 实例化上传类
		//var_dump($_FILES['photo2']);
		$upload->maxSize  = 3145728 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath =  './Uploads/';// 设置附件上传目录
		$upload->autoSub = true;
		$upload->subName = array('date','Ymd');
		$upload->saveName = date('YmdHis',time()).rand(1000,9999); 
		$info   =  $upload->uploadOne($_FILES['photo2']);
		if(!$info) {// 上传错误提示错误信息
			$this->error($upload->getError());
		}else{
			//var_dump($info);
			echo "<br /><img style='max-width:60%;' src='/Uploads/".$info['savepath'].$info['savename']."' /><br />";	
		}
		
	}
	
	public function about(){
		$conn = M('About');
		if($_SESSION['admin']==null){
			$this->error("您还没有登录","/index.php/home/index/login",1);
		}
		$row = $conn->where("id = 1")->find();
		$this->assign('body',$row);
		$this->display('index:about');	
	}
	
}