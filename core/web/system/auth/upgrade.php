<?php
ini_set("max_execution_time",0);
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
if (!(defined('PHP_VERSION_ID'))) 
{
	$version = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($version[0] * 10000) + ($version[1] * 100) + $version[2]);
}
class Upgrade_EweiShopV2Page extends SystemPage 
{
	public function main() 
	{
		load()->func('file');
		global $_W,$_GPC;  
		if(empty($_GPC['op'])) {
			$_GPC['op'] = 'display';
		}
		$ver = include(IA_ROOT . '/addons/ewei_shopv2/version.php');
		$ver = EWEI_SHOPV2_VERSION;
		$namea = EWEI_SHOPV2_RELEASE;
		$hosturl = urlencode('http://' . $_SERVER['HTTP_HOST']);
		$hostip =  $_SERVER['SERVER_ADDR']?$_SERVER['SERVER_ADDR']:gethostbyname($SERVER_NAME);
		$updatehost = 'http://update.yixiuba.net/update.php';
		$lastver = file_get_contents($updatehost . '?a=check&v='.$ver);
		if($_GPC['op'] == 'display'){
			$op = $_GPC['op'];	
			$gettimeurl = $updatehost . '?a=client_check_time&v=' . $ver . '&u=' . $hosturl;
			$domain_time = file_get_contents($gettimeurl);	
			$chosturl = $updatehost . '?a=change&v=' . $ver. '&u=' . $hosturl;		
			$cinfo = file_get_contents($chosturl);
		}
		elseif($_GPC['op'] == 'update'){
			$op = $_GPC['op'];
			$updatehost = 'http://update.yixiuba.net/update.php';
			$updatehosturl = $updatehost . '?a=update&v=' . $ver . '&u=' . $hosturl. '&i=' .$hostip ;
			$updatenowinfo = file_get_contents($updatehosturl);
			if (strstr($updatenowinfo, 'zip')){		
					$pathurl = $updatehost . '?a=down&f=' . $updatenowinfo . '&u=' . $hosturl;

					$updatedir = IA_ROOT.'/addons/ewei_shopv2/data/Temp/update';

					if(!is_dir($updatedir)) {
						mkdirs($updatedir);
					}	
					$isgot = $this->get_file($pathurl, $updatenowinfo, $updatedir);
					
					if($isgot){				
						$updatezip = $updatedir . '/' . $updatenowinfo;
						require IA_ROOT . "/framework/library/phpexcel/PHPExcel/Shared/PCLZip/pclzip.lib.php";  
						$thisfolder = new PclZip($updatezip);
						$isextract = $thisfolder->extract(PCLZIP_OPT_PATH, $updatedir);
						if ($isextract == 0) {  
							message('解压更新包失败！',referer(),'error'); 
						} 
						
						$archive = new PclZip($updatezip);
						$list = $archive->extract(PCLZIP_OPT_PATH, IA_ROOT, PCLZIP_OPT_REPLACE_NEWER); 
						
						if ($list == 0) {  
							message('远程升级文件不存在或站点没有修改权限。升级失败！',referer(),'error'); 
						} 
						@unlink($updatedir . '/update.sql');
						$this->deldir($updatedir);
						if(file_exists(IA_ROOT . '/update.sql')) {						
							$sqlfile = IA_ROOT . '/update.sql';
							$this->runquery($sqlfile);               
						}
						@unlink(IA_ROOT . '/update.sql');
						message('系统更新完成！',webUrl('system/auth/upgrade'),'success');
					}
					else{
						message('查找不到更新包！',referer(),'error');
					}
			}
			else{
					message('微上宝提醒您:请检查授权状态或联系微上宝客服授权！',referer(),'error');
			}
		}

	include $this->template('system/auth/upgrade');
	}
	public function get_file($url,$name,$folder = './')
		{
			set_time_limit((24 * 60) * 60);
			$destination_folder = $folder . '/';
			$newfname = $destination_folder.$name;
			$file = fopen($url, 'rb');
			if ($file) {
				$newf = fopen($newfname, 'wb');
				if ($newf) {
					while (!feof($file)) {
						fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
					}
				}
			}
			if ($file) {
				fclose($file);
			}
			if ($newf) {
				fclose($newf);
			}
			return true;
		}

	public	function runquery($sql) {
		
			$file_path = $sql;  
			if(file_exists($file_path)){ 
				if($fp=fopen($file_path,"a+")){ 
					$buffer=1024; 
					$str=""; 
					while(!feof($fp)){ 
						$str.=fread($fp,$buffer); 
					}
				}
			}
			$query = $str;
			pdo_run($query);
		}		
	public	function deldir($dir) {
		  $dh=opendir($dir);
		  while ($file=readdir($dh)) {
			if($file!="." && $file!="..") {
			  $fullpath=$dir."/".$file;
			  if(!is_dir($fullpath)) {
				  unlink($fullpath);
			  } else {
				  $this->deldir($fullpath);
			  }
			}
		  }
		  closedir($dh);
		  if(rmdir($dir)) {
			return true;
		  } else {
			return false;
		  }
		}	
}
?>