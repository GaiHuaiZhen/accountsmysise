<?php
/**
 * @author Nicky
 * 基于PHP爬虫技术开发的MYSISE系统
 */

    include "simple_html_dom.php";

    $cookie_file = tempnam("./temp","cookie");//cookie的文件保存路径
    $username = '1340112124';//学生系统登录学号
    $password = '13440514105225';//学生系统登录密码

    $base_url = 'http://class.sise.com.cn:7001';

    /**
     * 获取隐藏域字符串
     * @param $html_data
     * @return mixed
     */
    function getHiddenString($html_data){
        $pattern = '/<input type="hidden" name="(.*?)"  value="(.*?)"/i';
        preg_match($pattern,$html_data,$matches);
        return $matches;
    }

    /**
     * 模拟登录
     */
    function login_post($username,$password,$cookie_file){
        $login_url = 'http://class.sise.com.cn:7001/sise/login.jsp';
        $login_check_url = 'http://class.sise.com.cn:7001/sise/login_check.jsp';

        $html_data = file_get_contents($login_url);

        $matches = getHiddenString($html_data);
        $post = "$matches[1]={$matches[2]}&username={$username}&password={$password}";

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$login_check_url);//登录提交的URL
        curl_setopt($curl,CURLOPT_HEADER,1);//是否显示头信息
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//设置自动显示返回的信息
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl,CURLOPT_COOKIEJAR,$cookie_file);//设置Cookie信息保存在指定的文件中
        curl_setopt($curl,CURLOPT_POST,1);//post方式提交
        curl_setopt($curl,CURLOPT_POSTFIELDS,$post);//要提交的信息
        $data = curl_exec($curl);//执行CURL

        curl_close($curl);//释放系统资源

        return $data;
    }

    /**
     * URL解析器
     * @param $cookie_file
     *          Cookie
     * @param $data
     *          登录之后获取的数据
     * @return mixed
     */
    function url_handler($cookie_file,$data){
        $pattern = '/\/sise\/index\.jsp/i';
        if(preg_match($pattern,$data)){
            $url = "http://class.sise.com.cn:7001/sise/module/student_states/student_select_class/main.jsp";
            $content =html_handler($url,$cookie_file);
//            echo $content;
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
            for($i = 0; $i < $length; $i++){
                //if($urls[$i] != 'hand')
                    //echo $urls[$i].'<br>';
            }
            return $urls;
        }
    }

    /**
     * 个人信息处理函数
     * @param $cookie_file
     *          Cookie
     * @param $url
     *          URL
     */
    function get_student_info($cookie_file,$url){
        //$url = 'http://class.sise.com.cn:7001/SISEWeb/pub/course/courseViewAction.do?method=doMain&studentid=8m5H4D7Np/g=';
        $html_context = html_handler($url, $cookie_file);
        $table_pattern = '/<table width="90%" class="table" align="center"[\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $html_context, $matches);
        $table = $matches[0][0];
        $arr = get_td_array($table);
            //print_r($arr1);
        $length = count($arr);
        for ($i = 0; $i < $length; $i++) {
            echo implode($arr[$i]) . "<br><br>";
        }
    }

    /**
     * 获取课程表信息
     * @param $cookie_file
     *          Cookie
     * @param $url
     *          URL
     */
    function get_schedular($cookie_file,$url){
        //$url = 'http://class.sise.com.cn:7001/sise/module/student_schedular/student_schedular.jsp';
        //$pattern = '/\/sise\/index\.jsp/i';

        //输出网页内容
        /*$html_context = html_handler($url,$cookie_file);
        $p = "/学号: (.*?) &nbsp;姓名: (.*?) &nbsp;年级: (.*?) &nbsp;专业: (.*(.*?))/i";
        if (preg_match_all($p, $html_context, $matches)) {
            $user_num = $matches[1][0];
            $user_name = $matches[2][0];
            $user_grade = $matches[3][0];
            $user_project = $matches[4][0];
            echo $user_num . '<br>' . $user_name . '<br>' . $user_grade . '<br>' . $user_project;
        }
        echo '<br>';
        $table_pattern = '/<table borderColor="#999999"[\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $html_context, $matches);
        $table = $matches[0][0];*/
        $result =html_handler($url,$cookie_file);
        $html = str_get_html($result);
        $week = $html->childNodes(0)->childNodes(2)->childNodes(0)->childNodes(3)->childNodes(0)->childNodes(0)->childNodes(0)->childNodes(0)->childNodes(1)->plaintext;

        preg_match_all('/\d*/',$week,$matches);
        $currentWeek = $matches[0][56];
        print_r($currentWeek);
        echo $currentWeek;
        $table = $html->childNodes(0)->childNodes(2)->childNodes(0)->childNodes(4);

    }

    /**
     * 考勤信息处理的函数
     * @param $cookie_file
     *            Cookie
     * @param $url
     *          URL
     */
    function get_attendance($cookie_file,$url){
        //$url = "http://class.sise.com.cn:7001/SISEWeb/pub/studentstatus/attendance/studentAttendanceViewAction.do?method=doMain&studentID=8m5H4D7Np/g=&gzcode=XWKk4iFnUenW9txwLsYCnQ==";
        $content = "";
        $html_context = html_handler($url, $cookie_file);
            //echo $html_context;
        $table_pattern = '/<table width="99%" class="table" [\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $html_context, $matches);
        $table = $matches[0][0];
        $arr = table_handler($table);
        //print_r($arr);
        $length = count($arr);
        for ($i = 0; $i < $length; $i++) {
            $content .= implode($arr[$i]) . "<br><br>";
        }
        echo $content;
        return $content;
    }

    /**
     * 奖惩记录处理的函数
     * @param $cookie_file
     * @param $url
     */
    function get_reward_punish($cookie_file,$url){
        //$url = "http://class.sise.com.cn:7001/sise/module/encourage_punish/encourage_punish.jsp?stuname=马增群&gzcode=1340112124&serialabc=01928010419";
        $html_context = html_handler($url, $cookie_file);
        $table_pattern = '/td class="tablebody" >([\w\W]*?)<\/td>/i';
        preg_match_all($table_pattern,$html_context,$matches);
        $table = $matches[1];
        //print_r($table);
        $length = count($table);
        for ($i = 0; $i < $length; $i++) {
            echo $table[$i] . "<br>";
        }
    }

    /**
     * 查看开始课程
     * @param $cookie_file
     * @param $url
     */
    function get_course($cookie_file,$url){
        $html_context = html_handler($url, $cookie_file);
        $table_pattern = "/class='tablebody' ><span class='font12'>([\w\W]*?)>([\w\W]*?)<\/td>/";
        preg_match_all($table_pattern, $html_context, $matches);
        $table = $matches[1];
        //echo $table;
        //$arr = get_td_array($table);
        //print_r($arr);
        $length = count($table);
        for ($i = 0; $i < $length; $i++) {
            echo $table[$i]. "<br>";
        }
    }

    /**
     * 晚归、违规用电记录信息处理的函数
     * @param $cookie_file
     * @param $url
     */
    function get_late_punish($cookie_file,$url){
        //$url = "http://class.sise.com.cn:7001/SISEWeb/pub/studentstatus/lateStudentAction.do?method=doMain&gzCode=1340112124&md5Code=f22d9fc170be64f029f78c9da29590ff";
        $html_context = html_handler($url, $cookie_file);
            //echo $html_context;
        $table_pattern = '/<table width="95%" class="table" align="center"[\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $html_context, $matches);
        $table = $matches[0][0];
        $arr = get_td_array($table);
            //print_r($arr);
        $length = count($arr);
        for ($i = 0; $i < $length; $i++) {
            echo implode($arr[$i]) . "<br>";
        }
        echo'<br>';
    }

    function get_score($cookie_file,$url){
        $url1 = "http://class.sise.com.cn:7001/sise/module/commonresult/showdetails.jsp?courseid=1853&schoolyear=2015&semester=2";
        $html_context = html_handler($url1, $cookie_file);
        //echo $html_context;
        $table_pattern = '/<table width="100%" border="1" class="table1" [\w\W]*?>([\w\W]*?)<\/table>/';
        preg_match_all($table_pattern, $html_context, $matches);
        $table = $matches[0][0];

        $arr = get_td_array($table);
        //print_r($arr);
        $length = count($arr);
        for ($i = 0; $i < $length; $i++) {
            echo implode($arr[$i]) . "<br>";
        }
        echo'<br>';
    }

    /**
     * Html解析处理器
     * @param $url
     *          URL
     * @param $cookie_file
     *          Cookie
     * @return string
     */
    function html_handler($url,$cookie_file){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        $contents = curl_exec($ch);
        curl_close($ch);

        //清理cookie文件
        //unlink($cookie_file);
        //输出网页内容
        $html_context = iconv("GB2312", "UTF-8//ignore", "$contents");
        return $html_context;
    }

    /**
     * html表格转换数组的处理器
     * @param $html
     * @return array
     */
    function table_handler($html)
    {
        $html = eregi_replace(">[\r\n\t ]+<", "><", $html); // 去掉多余的空字符
        eregi("<table[^>]*>(.+)</table>", $html, $regs); // 提取表体
        $ar = split("</tr>", $regs[1]); // 按行分解成数组
        array_pop($ar); // 去处尾部多余的元素
        for ($i = 0; $i < count($ar); $i++) {
            $ar[$i] = split("</td>", $ar[$i]); // 分裂各列
            array_pop($ar[$i]); // 去处尾部多余的元素
        }
        for ($i = 0; $i < count($ar); $i++) {
            for ($j = 0; $j < count($ar[$i]); $j++) {
                if (eregi("colspan.*([0-9]+)", $ar[$i][$j], $regs)) { // 如果跨列
                    $t = array();
                    while (--$regs[1] > 0) // 补足差额
                        array_push($t, "");
                    $ar[$i] = array_merge(array_slice($ar[$i], 0, $j + 1), $t, array_splice($ar[$i], $j + 1));
                }
                if (eregi("rowspan.*([0-9]+)", $ar[$i][$j], $regs)) { // 如果跨行
                    if (!isset($t)) // 跨列、跨行不同时存在
                        $t = array("");
                    else
                        array_push($t, "");
                    $k = $regs[1];
                    while (--$k > 0) // 补足差额
                        $ar[$i + $k] = array_merge(array_slice($ar[$i + $k], 0, $j), $t, array_splice($ar[$i + $k], $j));
                }
                unset($t);
            }
        }
        // 除去html标记
        for($i=0;$i<count($ar);$i++) {
            while(count($ar[$i]) < count($ar[0])) // 保证各行的列相同，这里有点牵强
                array_push($ar[$i],"");
            for($j=0;$j<count($ar[$i]);$j++)
                $ar[$i][$j] = strip_tags($ar[$i][$j]);
        }
        return $ar;
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

    //模拟登录，获取数据
   $data = login_post($username,$password,$cookie_file);
    //index页面URL解析
   $urls = url_handler($cookie_file,$data);
    //获取个人信息
    get_student_info($cookie_file,$base_url.$urls[0]);
    //输出课程表
   get_schedular($cookie_file,$base_url.$urls[2]);
     //获取考勤信息
   get_attendance($cookie_file,$base_url.$urls[6]);
    //获取奖惩信息
   get_reward_punish($cookie_file,$base_url.$urls[10]);
   //查看开设课程
   //get_course($cookie_file,$base_url.$urls[22]);
    //晚归、违规用电记录
   get_late_punish($cookie_file,$base_url.$urls[44]);
    //查看平时成绩
    get_score($cookie_file,$base_url);


