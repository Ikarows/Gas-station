<?php

include('./config.php');

/**
 *  /?type=gasoline&userAccount=10001&meno=210&pageSize=10
 *  {type} 加油类型
 *  {userAccount} 操作工号
 *  {meno} 员工号
 *  {pageSize} 显示数据数量
 */

//服务器时间
$toDayTime = date("Y-m-d"); //今天
$yesterDayTime = date("Y-m-d",strtotime("-1 day")); //昨天

//重定向
$host = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$httpHost = $_SERVER['HTTP_HOST'].$folderName;

if($host == $httpHost){
	header("location:".$defaultUrl);
}

if(empty($_GET['userAccount'])){
	echo "缺少类型参数！"; exit;
}else if(empty($_GET['meno'])){
	echo "缺少员工号参数！"; exit;
}else if(empty($_GET['pageSize'])){
	echo "缺少每页显示条数参数！"; exit;
}

// 1.先组装要发送的数据
$data = array (
	"userAccount" => $_GET['userAccount'], //类型
	"where" => " meno='[自助买单]".$_GET['meno']."' and UserAccount='".$_GET['userAccount']."' ", //按备注（员工号）和操作工号为条件来查询
	"pageIndex" => "0",
	"pageSize" => $_GET['pageSize'], //条数
	"orderBy" => 'OperateTime desc' //排序
);

// 2.发起请求
$response_data = $client->CallHttpPost ( "Get_ConsumeNotePagedV2", $data);

// 3.显示结果,status=0表示接口请求成功,否则(一般为status=-1)表示接口请求错误,返回的message为失败的具体原因
if ($response_data ["status"] === 0) {
	$data = $response_data ["data"];
	//var_dump($data);exit;
} else {
	$data = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>加油小助手系统</title>
	<link rel="stylesheet" href="./css/style.css">
	<script src="./js/jquery-1.12.3.js"></script>
</head>
<body>

	<div class="header">
		加油小助手 员工 <?php echo $_GET['meno'] ?> 报表
	</div>
	<div class="container">
		<div class="screening">
			<ul>
				<li class="<?php if($_GET['type'] == 'gasoline'){ echo 'active';} ?>" onclick="getData('<?php echo "$qy" ?>')">汽油</li>
				<li class="<?php if($_GET['type'] == 'diesel'){ echo 'active';} ?>" onclick="getData('<?php echo "$cy" ?>')">柴油</li>
				<div class="clear"></div>
			</ul>
			<ul class="daybtn-box">
				<li class="today-btn active">今天</li>
				<li class="yesterday-btn">昨天</li>
				<div class="clear"></div>
			</ul>
		</div>
		<div class="income">当日共 <span class="red" id="sum">0</span> 笔，总收入：<span class="red" id="rmb">0</span> 元</div>
		<div class="list-box">
			<ul class="today-box">
				<?php 
					foreach ($data as $key => $value) {

						if(date('Y-m-d',strtotime($value["OperateTime"])) == $toDayTime){
							$todayData = true; //标识是否有数据
				?>
							<li>
								<div class="left-data">
									<h1><?php echo $value["BillNumber"] ?></h1>
									<p class="small"><?php echo $value["OperateTime"] ?></p>
								</div>
								<div class="right-data">
									<p><span class="orange">￥<span class="money"><?php echo $value["TotalPaid"] ?></span></span></p>
									<p class="small">订单原价：￥<?php echo $value["TotalMoney"] ?></p>
								</div>
								<div class="clear"></div>
							</li>
				<?php 
						}
					}
				?>
				
				<?php
					if(!$todayData){
				?>
						<div class="no-data">暂无数据！</div>
				<?php
					}
				?>
			</ul>
			<ul class="yesterday-box">
				<?php 
					foreach ($data as $key => $value) {

						if(date('Y-m-d',strtotime($value["OperateTime"])) == $yesterDayTime){
							$yesterDayData = true; //标识是否有数据
				?>
							<li>
								<div class="left-data">
									<h1><?php echo $value["BillNumber"] ?></h1>
									<p class="small"><?php echo $value["OperateTime"] ?></p>
								</div>
								<div class="right-data">
									<p><span class="orange">￥<span class="money"><?php echo $value["TotalPaid"] ?></span></span></p>
									<p class="small">订单原价：￥<?php echo $value["TotalMoney"] ?></p>
								</div>
								<div class="clear"></div>
							</li>
				<?php 
						}
					}
				?>

				<?php
					if(!$yesterDayData){
				?>
						<div class="no-data">暂无数据！</div>
				<?php
					}
				?>
			</ul>
		</div>
	</div>
	
	<span class="reload"></span>

	<script>
		//加油类型跳转
		var getData = function (url) {
			window.location.href = url;
		}

		//今天数据切换
		$('.today-btn').on('click', function(){
			tabDay(1);
		});

		//昨天数据切换
		$('.yesterday-btn').on('click', function(){
			tabDay(2);
		});

		var tabDay = function (index) {
			$('.daybtn-box li').eq(index-1).addClass('active').siblings('li').removeClass('active');
			$('.list-box ul').eq(index-1).fadeIn().siblings('ul').hide();

			//显示当日笔数
			$('#sum').html($('.list-box ul').eq(index-1).find('li').length);

			//显示当日总金额
			var rmb = 0;
			$('.list-box ul').eq(index-1).find('.money').each(function(){
				rmb = rmb + Number($(this).html());
			});
			$('#rmb').html(rmb.toFixed(2)); //保留两位小数
		}

		//初始化
		new tabDay(1);

		//刷新页面
		$('.reload').on('click', function(){
			window.location.reload();
		});
	</script>
</body>
</html>