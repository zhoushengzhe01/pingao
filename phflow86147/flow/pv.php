<?php
//计pv
$p = explode('&', base64_decode($_GET['p'] ? $_GET['p'] : null));

if(!empty($p)){
	
	$values = '';
	foreach ($p as $v) {
		if($v){
			$jafjpp = explode('&', base64_decode($v));
			if(count($jafjpp) != 8){
				?>
				    document.writeln("参数错误！");
				<?php
			    exit;
			}
			if(date('Y-m-d', $jafjpp[3]) != date('Y-m-d'))exit;
	        $values .= 'SELECT '.$jafjpp[4].','.$jafjpp[1].','.$jafjpp[2].' UNION ALL ';
			
		}
	}
    $values = rtrim($values, ' UNION ALL ');
    $conn = openconn();
	count_pv($values, $conn);
			
	colseconn($conn);

}else{
	?>
	    document.writeln("参数错误,不能为空！");
	<?php
	exit;
}