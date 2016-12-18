<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/29
 * Time: 21:15
 */

    function main(){

        $conn=@mysql_connect("127.0.0.1:3306","root","root");
        if($conn){
            mysql_query("SET NAMES 'UTF8'");
            mysql_select_db("db_mysise",$conn);
            $result = mysql_query("select * from class");
            if(mysql_num_rows($result) > 0) {
                while($row = mysql_fetch_array($result)) {
                    $id = $row['classId'];
                    $name = $row['className'];
                    $dept = $row['classDept'];
                    $credit = $row['credit'];
                    $exam = $row['examfunc'];
                    echo "<table>
                    <tr>
                    <td><?php echo $id; ?></td>
                    <td><?php echo $name; ?></td>
                    <td><?php echo $dept; ?></td>
                    <td><?php echo $credit; ?></td>
                    <td><?php echo $exam; ?></td>
                    </tr>
                    </table>";
                }
            }
            mysql_close($conn);
        }
    }

main();

?>

