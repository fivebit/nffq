<?php

/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/

/* 去除数组中的数字下标 */
function array_unset_int_key(array &$arr)
{/*{{{*/
	foreach ($arr as $key => &$value) {
		if (is_int($key)) { unset($arr[$key]); }
	}
}/*}}}*/


/* 强制转成UTF-8编码 */
function toUTF8($str)
{/*{{{*/
	if (preg_match('/^.*$/u', $str) <= 0) {
		return iconv("GBK","UTF-8//ignore", $str);
	} else {
		return $str;
	}
}/*}}}*/


/* 强制转成GBK编码 */
function toGBK($str)
{/*{{{*/
	if (preg_match('/^.*$/u', $str) <= 0) {
		return $str;
	} else {
		return iconv("UTF-8","GBK//ignore", $str);
	}
}/*}}}*/


/* 打印进度条（脚本应用） */
function print_progress($cur, $sum, &$last) 
{/*{{{*/
	//1. 计算当前进度
	$step = 0;
	if($sum < 100){ 
		$step = $cur * (100/$sum);
	}else{  
		$step = ($cur*100) / $sum; 
	}       
	//2. 如果当前进度已更新，则显示
	if($step>=$last && $step <= 100){
		printf("progress: [%-100s] %d%%\r", str_repeat('#',$step), $step); 
		++$last;
	}       
}/*}}}*/

/* 获取微秒数，用于统计时间 */
function get_micro_time()
{/*{{{*/ 
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}/*}}}*/

/* 统计文件一共多少行 */
function count_line_num($file)
{/*{{{*/
	if(!is_file($file)){
		fprintf(STDERR, "UF.count_line_num fail: can not open $file.\n");
		exit(-1);
	}
	$fp=fopen($file,'r');
	if(!$fp){
		fprintf(STDERR, "UF.count_line_num fail: can not open $file.\n");
		exit(-1);
	}
	$res = 0; 
	while(!feof($fp)){
		fgets($fp);
		++$res;
	}   
	fclose($fp);
	return $res-1;
}/*}}}*/

?>
