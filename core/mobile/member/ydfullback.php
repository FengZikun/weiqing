<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Ydfullback_EweiShopV2Page extends MobileLoginPage
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$_GPC['type'] = intval($_GPC['type']);
		include $this->template();
	}
	public function get_list() 
	{
		global $_W;
		global $_GPC;
		$isfullback = intval($_GPC['type']);
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = ' and uniacid=:uniacid and openid=:openid ';
		$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
		$list = array();
		if($isfullback){
			$list = pdo_fetchall('select * from ' . tablename('ewei_shop_ydfullback_log') .'  where 1 ' . $condition . ' order by createtime desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);

			$total = pdo_fetchcolumn('select count(id) from ' . tablename('ewei_shop_ydfullback_log') .'  where 1 ' . $condition . ' order by createtime desc', $params);
		}else{

			$data = m('common')->getSysset('shop');
			$ydfullbackrate = $data['ydfullbackrate'];

			$params =  array(':uniacid' => $_W['uniacid'],':openid' => $_W['openid']);
			$members = pdo_fetch("select m.*,sm.openid from ".tablename('mc_members')."m left join ".tablename("ewei_shop_member")." sm on m.uid =sm.uid where m.credit11>0 and  m.uniacid=:uniacid and sm.openid=:openid limit 1",$params);
			$list =array();

			if($members){
				$total =0;
			$begin=mktime(0,0,0,date('m'),date('d')+1,date('Y'));
			$credit_rem = $members['credit11'];
			while($credit_rem>0&&$total<20){
				$total++;
				$add = number_format($credit_rem*$ydfullbackrate/100,2);

				if($credit_rem<1){
					$add = number_format($credit_rem,2);
					$credit_rem = 0;
				}else
				{
					$credit_rem = $credit_rem-$add;

				}
				$list[]= array("uniacid"=>$_W['uniacid'],"openid"=>$members['openid'],'sysrate'=>$ydfullbackrate
				,'priceevery'=>$add,'createtime'=>$begin);

				$begin+=86400;

			}
			}
		}

		foreach ($list as &$row )
		{
			$row['createtime'] = date('Y-m-d', $row['createtime']);
			$row['type'] =$isfullback;
		}
	    $list2 = array_slice($list,($pindex - 1) * $psize,$psize);
		unset($row);
		show_json(1, array('list' => $list2, 'total' => $total, 'pagesize' => $psize));
	}
}
?>