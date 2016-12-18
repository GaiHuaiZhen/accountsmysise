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

<h2>所有开设课程</h2>
<table class="table">
    <thead>
    <tr>
        <th>课程代码</th>
        <th>课程名称</th>
        <th>教学承担系</th>
        <th>学分</th>
        <th>考核方式</th>
    </tr>
    </thead>
    <?php
    $conn=@mysql_connect("127.0.0.1:3306","root","root");
    if($conn){
        mysql_query("SET NAMES 'UTF8'");
        mysql_select_db("db_mysise",$conn);
        $result = mysql_query("select * from class");
        if(mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_array($result)) {
                $id = $row['classId'];
                $name = $row['className'];
                $dept = $row['classDept'];
                $credit = $row['credit'];
                $exam = $row['examfunc'];
?>
    <tr>
        <td><?php echo $id; ?></td>
        <td><?php echo $name; ?></td>
        <td><?php echo $dept; ?></td>
        <td><?php echo $credit; ?></td>
        <td><?php echo $exam; ?></td>
    </tr>
<?php
            }
        }

        mysql_close($conn);
    }
    ?>
</table>




</table>

<br>


</body>
</html>

