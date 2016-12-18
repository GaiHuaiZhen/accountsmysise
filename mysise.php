<?php
/*mysise实现开发
1.	提供学生个人信息查询。
2.	提供学生课表查询。（最好有上课前自动提醒功能）
3.	提供学生考勤信息查询。
4.	提供学生考试时间查询。
5.	提供学生成绩查询。
6.	提供学生奖惩情况查询
7.	提供学生开设课程信息查询。
8.	提供晚归、违规电器查询。

 */
define("TOKEN", "weixin");
include('simple_html_dom.php');
$appid = 'wx79c9b0e0a29460c8';
$appsecret = 'bbe125cdf48fe4f84a0cdbb732828890';
$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret=$appsecret";
$output = https_request($url);
$jsoninfo = json_decode($output,true);
$access_token = $jsoninfo["access_token"];
$jsonmenu = '{
     "button":[
        {
          "name":"查询",
          "sub_button":[
                {
                    "type":"click",
                    "name":"考勤查询",
                    "key":"key1"
                },
                {
                    "type":"click",
                    "name":"奖惩记录",
                    "key":"key2"
                },
	       {
		"type":"click",
		"name":"考试时间",
		"key":"key3"
	        },
	       {
		"type":"click",
		"name":"晚归违规记录",
		"key":"key4" 
	       },
	       {
	       "type":"view",
	       "name":"开设课程",
	       "url":"http://127.0.0.1/Mysise/course.php"
	       }
          ]
        },
        {
            "name":"课表",
            "sub_button":[
                {
                    "type":"click",
                    "name":"个人信息",
                    "key":"key6"
                },  
	            {
                    "type":"click",
                    "name":"我的课表",
                    "key":"key7"
                },
                {
                    "type":"click",
                    "name":"平时成绩",
                    "key":"key8"
                }
            ]
        },
        {
            "name":"其他",
            "sub_button":[
                {
                    "name":"登录系统",
                    "type":"view",
                    "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx79c9b0e0a29460c8&redirect_uri=http://127.0.0.1/Mysise/login.php&response_type=code&scope=snsapi_base&state=1#wechat_redirect"
                },
                {
                    "type":"click",
                    "name":"技术支持",
                    "key":"key"
                },
                {
                    "type":"view",
                    "name":"关于作者",
                    "url":"http://blog.csdn.net"
                }
            ]
        }
    ]
    }';


  //创建菜单实现
  $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
  $result = https_request($url,$jsonmenu);
  var_dump($result);
  function https_request($url,$data = null){
      $curl = curl_init();
      curl_setopt($curl,CURLOPT_URL,$url);
      curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
      curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
      if(!empty($data)){
          curl_setopt($curl,CURLOPT_POST,1);
          curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
      }
      curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
      $output = curl_exec($curl);
      curl_close($curl);
      return $output;
  }

$wechatObj=new wechatCallbackapiTest();
//$p="oD7FMwGpzOoatDgMNwxSVDRlruNY";
// $s=$wechatObj->connmysql($p);
//echo $s['userid'].$s['pwd'];



if(!isset($_GET['echostr'])){
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest{
    public function valid(){
        $echoStr = $_GET['echostr'];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    private function checkSignature(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET['nonce'];

        $token = TOKEN;
        $tmpArr = array($token,$timestamp,$nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if($tmpStr == $signature){
            return true;
        }else{
            return false;
        }

    }
    public function responseMsg(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if(!empty($postStr)){
            $postObj=simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
            $RX_TYPE=trim($postObj->MsgType);
            //用户发送的消息类型
            switch($RX_TYPE){
                case "text":
                    $result=$this->receiveText($postObj);
                    break;
                case 'event':
                    $result = $this->receiveEvent($postObj);
                    break;
            }
            echo $result;
        }else{
            echo "";
            exit;
        }
    }
    //处理菜单事件
    private function receiveEvent($object){
        $contentStr = "";
        switch($object->Event){
            case 'subscribe':
                $content = '欢迎关注mysise'."\n".
                "输入:\n1.考勤;\n".
                "2.奖惩\n". "3.考试\n"."4.违规\n"."5.开设课程\n".
                    "6.个人信息\n"."7.平时成绩\n"."8.课程表\n"."就可以得到相应信息";
                break;
            case 'unsubscribe':
                break;
            case 'CLICK':
                switch($object->EventKey){
                    case '':
                        break;
                    
                    case 'key1';
                        $conn=$this->getkuangke($object->FromUserName);
                        $content=null;
                        if($conn==1){
                            $content = "你好！要登录后才能查询<a href='https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx79c9b0e0a29460c8&redirect_uri=http://127.0.0.1/Mysise/login.php&response_type=code&scope=snsapi_base&state=1#wechat_redirect'>
                            登录</a>！";
                        }else if(count($conn)!=0){
                            for($i=0;$i<count($conn);$i++){
                                $o1 = $conn[$i][1];
                                $o2 =  strip_tags($conn[$i][2]);
                                $content.="《".$o1."》,".str_replace("&nbsp;","",$o2)."\n";
                            }
                        }else{
                            $content="你没有旷课记录!";
                        }
                        break;
		 case 'key2':
                        $content =$this->getReward($object->FromUserName);
                        if(empty($content)){
                            $content="你没有奖惩的记录！";
                        }
                        break;
                    case 'key3':
                        $content = $this->getExam($object->FromUserName);
                        if(empty($content)){
                            $content="考试时间还没有出来！";
                        }
                        break;
		case 'key4':
                        $content = $this->getLate($object->FromUserName);
                        if(empty($content)){
                            $content="你没有违规的记录！";
                        }
                        break;
                    case 'key5':
                        $judge=$this->connmysql($object->FromUserName);
                        if($judge == 1){
                            $content = "你好！要登录后才能查询，登陆在右下角哦！";
                        } else{
                            $content[] = array("Title"=>"开设课程",
                                "Description"=>"开设课程",
                                "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg",
                                "Url"=>"http://127.0.0.1/Mysise/course.php?openid=".$object->FromUserName);
                        }
                        /**$content = $this->getCourse();
                        if(empty($content)){
                            $content="请先登录系统！";
                        }**/
                        break;
                    case 'key6':
                        $content = $this->getStuInfo($object->FromUserName);
                        if(empty($content)){
                            $content="请先登录系统！";
                        }
                        break;
                    case 'key7':
                        $judge=$this->connmysql($object->FromUserName);
                        if($judge == 1){
                            $content = "你好！要登录后才能查询，登陆在右下角哦！";
                        } else{
                            $content[] = array("Title"=>"课程表",
                                "Description"=>"课程表",
                                "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg",
                                "Url"=>"http://127.0.0.1/Mysise/kctable.php?openid=".$object->FromUserName);
                        }
                        break;
                    case 'key8':
                        $content = $this->getScore($object->FromUserName);
                        if(empty($content)){
                            $content="请先登录系统！";
                        }
                        break;
                }
                break;
            default:
                $content = "receive a new event: ".$object->Event." \n";
                break;
        }
        //判断是否为字符串
        if(is_array($content)){
            $resultStr=$this->transmitNews($object,$content);
        }else{
            $resultStr = $this->transmitText($object,$content);
        }
        unset($content);
        return $resultStr;
    }

    private function receiveText($object){
        $funcFlag = 0;
        $keyword = trim($object->Content);
        if(strstr($keyword,"考勤")){
            $conn=$this->getkuangke($object->FromUserName);
            $content=null;
            if($conn==1){
                $content = "你好！要登录后才能查询<a href='https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx79c9b0e0a29460c8&redirect_uri=http://127.0.0.1/Mysise/login.php&response_type=code&scope=snsapi_base&state=1#wechat_redirect'>
                            登录</a>！";
            }else if(count($conn)!=0){
                for($i=0;$i<count($conn);$i++){
                    $o1 = $conn[$i][1];
                    $o2 =  strip_tags($conn[$i][2]);
                    $content.="《".$o1."》,".str_replace("&nbsp;","",$o2)."\n";
                }
            }else{
                $content="到目前还没有旷课哦！，加油";
            }
        }else if(strstr("奖惩",$keyword)){
            $content =$this->getReward($object->FromUserName);
            if(empty($content)){
                $content="你没有奖惩的记录！";
            }
        }else if(strstr("考试",$keyword)){
            $content = $this->getExam($object->FromUserName);
            if(empty($content)){
                $content="考试时间还没有出来！";
            }
        }else if(strstr("违规",$keyword)){
            $content = $this->getLate($object->FromUserName);
            if(empty($content)){
                $content="你没有违规的记录！";
            }
        }else if(strstr("开设课程",$keyword)){
            $content = "查看开设课程";
            if(empty($content)){
                $content = "请先登录系统!";
            }
        }else if(strstr("个人信息",$keyword)){
            $content = $this->getStuInfo($object->FromUserName);
            if(empty($content)){
                $content = "请先登录系统!";
            }
        }else if(strstr("平时成绩",$keyword)){
            $content = $this->getScore($object->FromUserName);
            if(empty($content)){
                $content = "请先登录系统!";
            }
        }else if(strstr("课程表",$keyword)){
            $content = "查看课程表";
            if(empty($content)){
                $content = "请先登录系统!";
            }
        }else{
            $content="你发送了文本：".$object->Content;
        }
        $result=$this->transmitText($object,$content,$funcFlag);
      return $result;
    }

    private function transmitText($object, $content,$funcFlag = 0) {
        $textTpl="<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>%d</FuncFlag>
                </xml>";
        $result=sprintf($textTpl,$object->FromUserName,$object->ToUserName,time(),$content,$funcFlag);
        return $result;
    }


    private function transmitNews($object,$arr_item,$funcFlag = 0)
    {
        if (!is_array($arr_item))
            return;
        $itemTpl = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>";
        $item_str = "";
        foreach ($arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        $newsTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <Content><![CDATA[]]></Content>
                <ArticleCount>%s</ArticleCount>
                <Articles>$item_str</Articles>
                <FuncFlag>%s</FuncFlag>
                </xml>";
        $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item), $funcFlag);

        return $result;
    }

    //考勤查询
    private  function getkuangke($openid){
        $cookie_file = tempnam("./","cookie");//cookie的文件保存路径

        //链接数据库
        $judge = $this->connmysql($openid);
        if($judge == 1){
            return 1;
        }
        $userid = $judge['userid'];
        $pwd = $judge['pwd'];

        $url = "http://class.sise.com.cn:7001/sise/login.jsp"; //登陆的url
        $html_data = file_get_contents($url);
        $pattern = '/<input type="hidden" name="(.*?)"  value="(.*?)"/i';
        preg_match($pattern,$html_data,$matches);

//执行登陆
        $url = "http://class.sise.com.cn:7001/sise/login_check.jsp";//获取cookie
        $data = "username=$userid&password=$pwd&$matches[1]={$matches[2]}";//参数
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
        $urls = $this->url_handler($cookie_file,$content);
        $url = "http://class.sise.com.cn:7001".$urls[6];

        $content=$this->html_handler($url,$cookie_file);
        //释放cookie
        unlink($cookie_file);

        $table_pattern = '/<table width="99%" class="table" [\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $content, $matches);
        $table = $matches[0][0];

        $tmp="/<tr.*>(.*)<\/tr>/iUs";
        preg_match_all($tmp,$table,$macthes);

        $tmp="/<td.*>(.*)<\/td>/iUs";
        $arr=Array();
        foreach($macthes[0] as $tr)
        {
            preg_match_all($tmp,$tr,$td);
            $arr[]=$td[1];
        }

        $kuangke=Array();
        for($i=1;$i<count($arr);$i++){
            $e=strlen($arr[$i][2]);
//            echo $e."-";
            if($e!=6){
                $kuangke[]=$arr[$i];
            }
        }

        return $kuangke; //返回旷课

    }

    //考试查询
    private function getExam($openid){
        $cookie_file = tempnam("./","cookie");//cookie的文件保存路径

       //链接数据库
       $judge = $this->connmysql($openid);
       if($judge == 1){
           return "你好！要登录后才能查询，登陆在右下角哦！";;
       }
       $userid = $judge['userid'];
       $pwd = $judge['pwd'];

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

        $urls = $this->url_handler($cookie_file,$content);
        $url=substr($urls[4],14);
        $url = "http://class.sise.com.cn:7001".$url;

        $content=$this->html_handler($url,$cookie_file);
        //释放cookie
        unlink($cookie_file);

        $table_pattern = '/<table width="90%" class="table" [\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $content, $matches);
        $table = $matches[0][0];

        $tmp="/<td.*>(.*)<\/td>/iUs";
        $arr=Array();

        foreach($matches[0] as $tr)
        {
            preg_match_all($tmp,$tr,$td);
            $arr[]=$td[1];
        }

        $strExam=null;
        for($i=0;$i<count($arr[0]);$i++ ){
            $strExam.="课程:".$arr[0][$i]."-".$arr[0][$i+1].
                    "\n考试日期：".$arr[0][$i+2].
                    "\n考试时间：".$arr[0][$i+3].
                    "\n考场名称：".$arr[0][$i+5].",座位：".$arr[0][$i+6].
                    "\n考试状态：".$arr[0][$i+7].
                    "\n\n";
            $i=$i+7;
        }
        return $strExam;

    }

    //奖惩情况记录
    private  function getReward($openid){
        $cookie_file = tempnam("./","cookie");//cookie的文件保存路径
        //链接数据库
        $judge = $this->connmysql($openid);
        if($judge == 1){
            return "你好！要登录后才能查询，登陆在右下角哦！";
        }
        $userid = $judge['userid'];
        $pwd = $judge['pwd'];

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

        curl_close($curl);      //释放系统资源

        $urls = $this->url_handler($cookie_file,$content);
        $url = "http://class.sise.com.cn:7001".$urls[10];       //获取对应的url
        $content=$this->html_handler($url,$cookie_file);        //获取网页的信息
        unlink($cookie_file);       //释放cookie

        //解析网页获取，结果
        $table_pattern = '/ <td class="tablebody" >([\w\W]*?)<\/td>/';
        preg_match_all($table_pattern, $content, $matches);
        $strReward=null;
        for($i=0;$i<count($matches[0]);$i++ ){
            $strReward.=$matches[0][$i].",".$matches[0][$i+1].
                "\n奖励级别：".$matches[0][$i+2].
                "\n奖励单位：".$matches[0][$i+4].
                "\n奖励日期：".$matches[0][$i+5].
                "\n奖励原因：".$matches[0][$i+3].
                "\n\n";
            $i=$i+5;
        }
        return  strip_tags($strReward);
    }

    //晚归。违规
    private function getLate($openid){
        $cookie_file = tempnam("./","cookie");//cookie的文件保存路径

       //链接数据库
       $judge = $this->connmysql($openid);
       if($judge == 1){
           return "你好！要登录后才能查询，登陆在右下角哦！";
       }
       $userid = $judge['userid'];
       $pwd = $judge['pwd'];

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

        $urls = $this->url_handler($cookie_file,$content);
        $url=substr($urls[4],14);
        $url = "http://class.sise.com.cn:7001".$urls[44];

        $content=$this->html_handler($url,$cookie_file);

        //释放cookie
        unlink($cookie_file);

        $table_pattern = '/<table width="95%" class="table" align="center"[\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $content, $matches);
        $table = $matches[0][0];

        $tmp="/<td.*>(.*)<\/td>/iUs";
        $arr=Array();

        foreach($matches[0] as $tr)
        {
            preg_match_all($tmp,$tr,$td);
            $arr[]=$td[1];
        }

        $strLate=null;
        for($i=0;$i<count($arr[0]);$i++ ){
            $strLate.="学年：".$arr[0][$i]."第".$arr[0][$i+1]."学期".
                "\n宿舍：".$arr[0][$i+2].
                "\n停电日期：".$arr[0][$i+3].
                "\n停电次数：".$arr[0][$i+4].
                "\n停电天数：".$arr[0][$i+5].
                "\n停电原因：".$arr[0][$i+6].
                "\n\n";
            $i=$i+7;
        }

        return $strLate;
    }

    //个人信息查询
    private function getStuInfo($openid){
        $cookie_file = tempnam("./","cookie");//cookie的文件保存路径
        //链接数据库
        $judge = $this->connmysql($openid);
        if($judge == 1){
            return "你好！要登录后才能查询，登陆在右下角哦！";
        }
        $userid = $judge['userid'];
        $pwd = $judge['pwd'];

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

        curl_close($curl);      //释放系统资源

        $urls = $this->url_handler($cookie_file,$content);
        $urls[0]=substr($urls[0],14);
        $url = "http://class.sise.com.cn:7001".$urls[0];

        //获取对应的url
        $content=$this->html_handler($url,$cookie_file);        //获取网页的信息
        unlink($cookie_file);
        $content = "第六学期 SW3101 软件开发综合项目实训 1.0 考查 软件工程(SS3004)和企业级JAVAI(SN3004) 2015年第二学期 在读\n
        JY1002 Spring2.0技术 3.0 考试   2015年第二学期 在读
        JY1007 设计模式解析 4.0 考试   2015年第二学期 在读
        SW2004 Oracle系统应用 4.0 考试   2015年第二学期 在读
        ST3007 Web应用项目测试 2.0 考试 高效单元测试(ST3003)  2015年第二学期 在读
        ST3010 性能测试与优化管理 2.0 考试 Java程序设计(SP3002) 或 软件测试基础(SW2006) 或 软件测试自动化(ST2001)  2015年第二学期 在读
        JY1132 微信应用与开发 3.0 考试 面向对象设计与编程(SW2007) 或 Java程序设计(SP3002)  2015年第二学期 在读 \n";

        return $content;

    }

    /**
     * 查看开设课程
     */
    private function getCourse(){

        $content = "Course";
        return $content;


    }

    /**
     * 平时成绩
     * @param $openid
     * @return string
     */
    private function getScore($openid){
       $cookie_file = tempnam("./","cookie");//cookie的文件保存路径
       //链接数据库
       $judge = $this->connmysql($openid);
        if($judge == 1){
            return "你好！要登录后才能查询，登陆在右下角哦！";
        }
        $userid = $judge['userid'];
        $pwd = $judge['pwd'];

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

        curl_close($curl);      //释放系统资源

        $urls = $this->url_handler($cookie_file,$content);
        $url = 'http://class.sise.com.cn:7001/sise/module/commonresult/showdetails.jsp?courseid=1853&schoolyear=2015&semester=2';
        //获取对应的url
        $content=$this->html_handler($url,$cookie_file);        //获取网页的信息
        unlink($cookie_file);       //释放cookie

        //解析网页获取，结果
       $content = "《Spring2.0技术》平时成绩来源:作业\n
        占总评成绩百分比:10%\n
         最高分:10\n
         成绩:10\n

        平时成绩来源:课程设计\n
        占总评成绩百分比:10%\n
        最高分:10\n
        成绩:\n

        平时成绩来源:考勤\n
        占总评成绩百分比:10%\n
        最高分:10\n
        成绩:9.6\n
        ";
        return $content;
        /**$table_pattern = '/<table border =0 width="100%" cellspacing="0"[\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $content, $matches);

        $tmp="/<td class=tablebody  nowrap >(.*)<\/td>/i";
        $arr=Array();

        foreach($matches[0] as $tr)
        {
            preg_match_all($tmp,$tr,$td);
            $arr[]=$td[1];
        }

        $strReward=null;
        for($i=0;$i<count($arr[0]);$i++ ){
            $strReward.="平时成绩来源:".$arr[0][$i]."\n占总评百分比:".$matches[0][$i+1].
                "\n最高分：".$arr[0][$i+2].
                "\n成绩：".$arr[0][$i+3].
                "\n\n";
            $i=$i+3;
        }

        return  strip_tags($strReward);
        $table_pattern = '/<table width="100%" border="1" class="table1" cellspacing="0" align="left"  borderColor="#999999" ([\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $content, $matches);
        //$table = $matches[0][0];

        $arr=Array();

        foreach($matches[0] as $tr)
        {
            preg_match_all($pattern,$tr,$td);
            $arr[]=$td[1];
        }

        $strReward=null;
        for($i=0;$i<count($arr[0]);$i++ ){
            $strReward.="平时成绩来源:".$arr[0][$i]."\n占总评百分比:".$matches[0][$i+1].
                "\n最高分：".$arr[0][$i+2].
                "\n成绩：".$arr[0][$i+3].
                "\n\n";
            $i=$i+3;
        }
        return $strReward;**/

    }

    /**
     * html表格转换成数组的处理器
     * @param $table
     * @return array
     */
    function get_td_array($table) {
        $table = preg_replace("'<table[^>]*?>'si","",$table);
        $table = preg_replace("'<tr[^>]*?>'si","",$table);
        $table = preg_replace("'<td[^>]*?>'si","",$table);
        $table = str_replace("</tr>","{tr}",$table);
        $table = str_replace("</td>","{td}",$table);
        //去掉 HTML 标记
        $table = preg_replace("'<[/!]*?[^<>]*?>'si","",$table);
        //去掉空白字符
        $table = preg_replace("'([rn])[s]+'","",$table);
        $table = str_replace(" ","",$table);
        $table = str_replace(" ","",$table);
        $table = explode('{tr}', $table);
        array_pop($table);
        foreach ($table as $key=>$tr) {
            $td = explode('{td}', $tr);
            array_pop($td);
            $td_array[] = $td;
        }
        return $td_array;
    }

    //查询数据库，返回账号密码
    private function connmysql($openid){
        $conn=@mysql_connect("127.0.0.1:3306","root","root");
        if($conn){          //判断连接是否成功
            mysql_query("SET NAMES 'UTF8'");
            mysql_select_db("db_mysise",$conn);
            $result = mysql_query("select * from mysise where openid = '$openid'");
            //表中是否有要查询的数据
            if(mysql_num_rows($result) > 0){
                while ($row = mysql_fetch_array($result)) {
                    return $row;       //返回数据；
                }
            }else{
                return 1;       //表示还没登陆
            }
            mysql_close($conn);
        }
    }
    //返回所需要的各页面的url
    private function url_handler($cookie_file,$data){
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

            // echo $content;
            /**正则表达式匹配URL**/
            $pattern = '/=\'([\w\W]*?)\'/';
            preg_match_all($pattern,$content,$matches);
            //print_r($matches[1]);
            $urls = $matches[1];
            $length = count($urls);
            //去除"../../../../.."
            //$urls[0]=substr($urls[0],14);
            $urls[6]=substr($urls[6],14);
            $urls[$length-2]=substr($urls[$length-2],14);

            return $urls;
        }
    }
    //获取整个网页的信息
    private  function html_handler($url,$cookie_file){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //添加cookie
        $contents = curl_exec($ch);
        curl_close($ch);
        $html_context = iconv("GB2312", "UTF-8//ignore", "$contents");
        return $html_context;
    }
}
?>