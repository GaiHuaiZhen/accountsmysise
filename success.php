<?php
$userid=$_GET['userid'];
$pwd=$_GET['pwd'];
$openid=$_GET['openid'];
$yesorno=null;
//include('simple_html_dom.php');
//设置编码格式（可以解决乱码问题）
header("Content-Type:text/html;charset=utf-8");
$url = "http://class.sise.com.cn:7001/sise/login.jsp"; //登陆的url

//$html = new simple_html_dom();
//$html->load($content);
////获取密钥
//$secretKey = $html->find("input",0)->name;
//$secretValue = $html->find("input",0)->value;
//$html->clear();

   $html_data = file_get_contents($url);
    $pattern = '/<input type="hidden" name="(.*?)"  value="(.*?)"/i';
    preg_match($pattern,$html_data,$matches);




//执行登陆
$url = "http://class.sise.com.cn:7001/sise/login_check.jsp";//获取cookie
$data = "username=$userid&password=$pwd&$matches[1]={$matches[2]}";//参数
$content=https_request($url,$data);

//访问网络的方法
function https_request($url,$data=null){
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
    if(!empty($data)){
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
    }
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

preg_match('/<script>(.*?)<\/script>/', $content, $match);//判断转向地址是否为成功登录的转向地址（即验证是否可以成功登录）
$result = $match[1];

if(strpos($result, "/sise/index.jsp") == true){
    $conn=@mysql_connect("127.0.0.1:3306","root","root");
    if($conn){
        mysql_query("SET NAMES 'UTF8'");
        mysql_select_db("db_mysise",$conn);
        $selectID = mysql_query("select * from mysise where openid = '$openid'");
        //判断是否openid是否之前就有，
        if(mysql_num_rows($selectID) > 0){
            mysql_query("update mysise set userid='$userid',pwd='$pwd' where openid = '$openid'");
        }else{
            mysql_query("insert into mysise values('$userid','$pwd','$openid')");
        }

        mysql_close($conn);
    }
    $yesorno= "成功登陆！！！现在可以使用所有查询了";
}else {
    $yesorno= "失败，你输入的密码为：".$pwd;

}
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>登陆</title>
    <meta charset=utf-8/>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link href="css/success.css" type="text/css" rel="stylesheet">
    <link href="css/global.css" type="text/css" rel="stylesheet">
</head>
<div data-role="page" id="page1">
    <div data-role="content">
        <ul data-role="listview" data-inset="true">
            <li>
                <p>
                <div class="fieldcontain">

                </div>
                </p>
            </li>
        </ul>
    </div>
    <div data-theme="b" data-role="footer" data-position="fixed">
        <h3>MYSISE系统</h3>
    </div>

</div>
<div class="success">
    <img src="images/success.png" />
    <p><label for="userid"><h3><?php echo $yesorno?></h3></label></p>
    <div class="s_msg">
        <div class="s_title">您的用户名：<span class="s_red"><?php echo$userid?></span></div>
        <div class="s_title">IOS端的产品正在搭建中，请您移步微信公众号端
            技术支持，联系博客：</div>
        <span class="s_redlink"><a href="http://blog.csdn.net/u014427391" target="_blank">Nicky的blog </a></span>
        <br><a href="mysise.php">返回主页</a>
    </div>
</div>
<body>
</body>
</html>