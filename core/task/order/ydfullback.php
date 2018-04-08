<?php
error_reporting(0);
require '../../../../../framework/bootstrap.inc.php';
require '../../../../../addons/ewei_shopv2/defines.php';
require '../../../../../addons/ewei_shopv2/core/inc/functions.php';
global $_W;
global $_GPC;
ignore_user_abort();
set_time_limit(0);
$sets = pdo_fetchall('select uniacid from ' . tablename('ewei_shop_sysset'));
foreach ($sets as $set ) 
{
	$_W['uniacid'] = $set['uniacid'];
	if (empty($_W['uniacid'])) 
	{
		continue;
	}

	//查找商城设置
	$trade = m('common')->getSysset('trade', $_W['uniacid']);
	$data = m('common')->getSysset('shop');

	$ydfullbackrate = $data['ydfullbackrate'];

	$params =  array(':uniacid' => $_W['uniacid']);

	$members = pdo_fetchall("select * from ".tablename('mc_members')." where credit11>0 and  uniacid=:uniacid ",$params);

	foreach ($members as $key => $value ){

		$credit_rem = $value['credit11'];
		$add = $credit_rem*$ydfullbackrate/100;
		if($add<1){
			$add = $credit_rem;
		}
		pdo_update('mc_members', array('credit10' => ($value['credit10'] + $add),'credit11' => ($value['credit11'] - $add), array('uid' => $value['uid'])));
		$data= array("uniacid"=>$_W['uniacid'],"openid"=>$value['openid'],'sysrate'=>$ydfullbackrate
		,'priceevery'=>$add,'createtime'=>time());
		pdo_insert("ewei_shop_ydfullback_log",$data);
	}
}
?>