<?php
    header ( "Content-Type: text/html; charset=UTF-8" );
    date_default_timezone_set('PRC');
    include ("lib/OpenApiClient.php");
    
    $OpenId = "443F6DD4FE594D059EED1B47EB268BE2";
    $Secret = "P3IHHF";

    $gonghao = "10001"; //汽油操作工号
    $yuanGongHao = "210"; //汽油员工号
    $gonghao2 = "20000"; //柴油操作工号
    $yuanGongHao2 = "210"; //柴油员工号
    $folderName = "/210/"; //文件夹名称（用于重定向）
    
    $qy = "?type=gasoline&userAccount=".$gonghao."&meno=".$yuanGongHao."&pageSize=200"; //汽油链接
    $cy = "?type=diesel&userAccount=".$gonghao2."&meno=".$yuanGongHao2."&pageSize=200"; //柴油链接
    $defaultUrl = $qy; //默认显示油的类型地址
    
    $client = new OpenApiClient ( $OpenId, $Secret );

    //屏蔽掉PHP警告和错误提示
    ini_set("display_errors","Off"); 

    /*
     * http://openapi.1card1.cn/OpenApiDoc/Get_MemberInfo
     */
?>