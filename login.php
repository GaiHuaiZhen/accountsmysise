<?php
//获取用户openid，和账号密码储存，
$code=$_GET['code'];
$useropenid= getUserInfo($code);//用户的openid

function getUserInfo($code){
    $appid="wx79c9b0e0a29460c8";
    $appsecret="bbe125cdf48fe4f84a0cdbb732828890";
    $access_token_url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
    $acces_token_json=https_request($access_token_url);
    $access_token_array=json_decode($acces_token_json,true);
    $openid=$access_token_array['openid'];
    return $openid;
}
function https_request($url){
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    $data = curl_exec($curl);
    if(curl_error($curl)){ return 'ERROR'.curl_error($curl);}
    curl_close($curl);
    return $data;

}
?>
<html xmlns="http://www.w3.org/1999/html">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>登陆</title>
    <meta charset=utf-8/>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link href="css/login.css" type="text/css" rel="stylesheet">
    <link href="css/global.css" type="text/css" rel="stylesheet">
    <script type="text/javascript">
        //输入账号密码前
        function yanzheng()
        {
            var name=document.getElementById('userid').value;//账号
            var pwd=document.getElementById('pwd').value;//密码
            //验证用户的密码与账号是否正式
            if(!(name.length==10)){
                alert("请输入正确的账号！！");return ;
            }else if(pwd==""){
                alert("请输入密码！！");return ;
            }
            document.from1.submit();
        }
    </script>

</head>
<body  onload="succeed()">
<div class="login">
    <div class="login-title"><p>系统登录</p>
        <i></i>
    </div>
    <form method="GET" action="success.php" name="from1">
        <div class="login-bar">
            <ul>
                <li><img src="images/login_user.png"><input type="text" name="userid" id="userid" class="text" placeholder="请输入用户名" /></li>
                <li><img src="images/login_pwd.png"><input type="password" name="pwd" id="pwd" class="psd" placeholder="请输入确认密码" /></li>
            </ul>
        </div>
        <div class="login-btn">
            <button class="submit" type="button" onclick="yanzheng()">登陆</button>
            <input type="hidden" name="openid"  id="openid" value="<?php echo $useropenid ?>"/>
            <a href="register.html"><div class="login-reg"><p>莫有账号，先注册</p></div></a>
        </div>
    </form>
</div>

</body>
</html>
