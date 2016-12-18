<?php
$openid=$_GET['openid'];
$o=getCourseTable($openid);
function getCourseTable($openid){
    $cookie_file = tempnam("./","cookie");//cookie的文件保存路径

    $conn=@mysql_connect("127.0.0.1:3306","root","root");
    if($conn){
        mysql_query("SET NAMES 'UTF8'");
        mysql_select_db("db_mysise",$conn);
        $result = mysql_query("select * from mysise where openid = '$openid'");

        if(mysql_num_rows($result) > 0){
            while ($row = mysql_fetch_array($result)) {
                $userid = $row['userid'];
                $pwd = $row['pwd'];
            }
        }else{
            return 1;//表示还没登陆
        }
        mysql_close($conn);
    }

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

    $url = "http://class.sise.com.cn:7001/sise/login.jsp"; //登陆的url
    $html_data = file_get_contents($url);
    $pattern = '/<input type="hidden" name="(.*?)"  value="(.*?)"/i';
    preg_match($pattern,$html_data,$matches);

    //执行登陆
    $data = "username=$userid&password=$pwd&$matches[1]={$matches[2]}";//参数
    $url = "http://class.sise.com.cn:7001/sise/login_check.jsp";//获取cookie
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);//登录提交的URL
    curl_setopt($curl,CURLOPT_HEADER,1);//是否显示头信息
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//设置自动显示返回的信息
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl,CURLOPT_COOKIEJAR,$cookie_file);//设置Cookie信息保存在指定的文件中
    curl_setopt($curl,CURLOPT_POST,1);//post方式提交
    curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//要提交的信息
    $content = curl_exec($curl);//执行CURL

    curl_close($curl);//释放系统资源
    //获取url
    $urls = url_handler($cookie_file,$content);

    $url = "http://class.sise.com.cn:7001".$urls[2];


    $content=html_handler($url,$cookie_file);

    //释放cookie
    unlink($cookie_file);

    $table_pattern = '/<form name="form1">([\w\W]*?)<\/form>/';
    preg_match_all($table_pattern, $content, $matches);
//     $table = $matches[0][0];
    $form=$matches[0] ;

    $table_pattern = "/<td.*>(.*)<\/td>/iUs";
    preg_match_all($table_pattern, $form[0], $o);
    $arr=Array();
    for($i=21;$i<86;$i++){
        $arr[]=strip_tags($o[1][$i]);
    }
    return $arr;
}

//返回所需要的各页面的url
function url_handler($cookie_file,$data){
    $pattern = '/\/sise\/index\.jsp/i';
    if(preg_match($pattern,$data)){
        $url = "http://class.sise.com.cn:7001/sise/module/student_states/student_select_class/main.jsp";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        $content = curl_exec($ch);
        curl_close($ch);

        /**正则表达式匹配URL**/
        $pattern = '/=\'([\w\W]*?)\'/';
        preg_match_all($pattern,$content,$matches);
        //print_r($matches[1]);
        $urls = $matches[1];
        $length = count($urls);
        //去除"../../../../.."
        $urls[0]=substr($urls[0],14);

        $urls[6]=substr($urls[6],14);
        $urls[$length-2]=substr($urls[$length-2],14);
        return $urls;
    }
}

function html_handler($url,$cookie_file){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    $contents = curl_exec($ch);
    curl_close($ch);
    $html_context = iconv("GB2312", "UTF-8//ignore", "$contents");
    return $html_context;
}
?>

<html>
<head>
    <title>Practical CSS3 tables with rounded corners - demo</title>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
    <meta name="viewport" content="width=device-width,user-scalable=no,inital-scale=1" />
    <link href="http://libs.baidu.com/bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet">
    <script src="http://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script>
    <script src="http://libs.baidu.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>

</head>
<body>
<h2 align="center">课程表</h2>
<table class="table">
    <thead>
    <tr>
        <th></th>
        <th>星期一</th>
        <th>星期二</th>
        <th>星期三</th>
        <th>星期四</th>
        <th>星期五</th>
    </tr>
    </thead>
       <?php for($i=0;$i<64;$i++){
    ?>
    <tr>
        <tr>
        <td><?php echo substr($o[$i],0,strpos($o[$i],"节"));?>节</td>
        <td><?php echo $o[$i+1];?></td>
        <td><?php echo $o[$i+2];?></td>
        <td><?php echo $o[$i+3];?></td>
        <td><?php echo $o[$i+4];?></td>
        <td><?php echo $o[$i+5];?></td>
    </tr>
    </tr>
    <?php
    $i=$i+7;
}?>
</table>




</table>

<br>


</body>
</html>