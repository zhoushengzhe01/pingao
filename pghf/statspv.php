<?php
//计有效数据和pv
$jafjpp = explode('&', base64_decode($_GET['u'] ? $_GET['u'] : null));

if(count($jafjpp) != 8){
    ?>
        document.writeln("参数错误！");
    <?php
    exit;
}
if(date('Y-m-d', $jafjpp[3]) != date('Y-m-d'))exit;

$linkurl = $_GET['u'];

count_pv($linkurl);
