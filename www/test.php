<?php


@set_time_limit(0);
@ini_set('max_execution_time',0);
echo ini_get('max_execution_time');
exit();

$start = time();
$finish = $start+35;


echo 'start<r />';


while(time() < $finish){
  
}

echo 'finish<r />';
$time = time()-$start;
echo 'time: '.$time.' sec';

