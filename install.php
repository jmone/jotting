<?php
header("content-Type: text/html; charset=utf-8");

$thisUrl = "http://".$_SERVER['HTTP_HOST'].str_replace("/install.php","",$_SERVER["SCRIPT_NAME"]);

$file_site		=	"./database/config_site.php";
$file_mysql		=	"./database/config_mysql.php";
$file_admin		=	"./database/config_admin.php";
$file_config = "./mc-files/mc-conf.php";

if( !is_writable($file_config) ){
	die($file_config." 不可写");
}

if( isset($_POST['host'],$_POST['name'],$_POST['user'],$_POST['passwd'],$_POST['prefix'],$_POST['adminuser'],$_POST['adminpwd']) )
{
	$db_host = strtolower(trim($_POST['host']));

	$db_user = stripslashes(trim($_POST['user']));

	$db_pass = stripslashes(trim($_POST['passwd']));

	$db_name = stripslashes(trim($_POST['name']));

	$db_prefix = stripslashes(trim($_POST['prefix']));

	$admin_user = strAddslashes(trim($_POST['adminuser']));

	$admin_pass = stripslashes(trim($_POST['adminpwd']));

	if( empty($db_host) )
	{
		die("<script>alert('Mysql 地址不能为空');</script>");
	}

	if( empty($db_name) )
	{
		die("<script>alert('Mysql 库名称不能为空');</script>");
	}

	if( empty($db_user) )
	{
		die("<script>alert('Mysql 用户不能为空');</script>");
	}

	if( empty($db_prefix) )
	{
		die("<script>alert('Mysql 表前缀不能为空');</script>");
	}

	if( substr($db_prefix,-1) != "_" )
	{
		die("<script>alert('Mysql 表前缀必须是以下划线结束');</script>");
	}

	if( strlen($admin_user) < 2 || strlen($admin_user) > 10 )
	{
		die("<script>alert('用户名长度应控制在2至10个字符之间。');</script>");
	}

	if( strlen($admin_pass) < 6 || strlen($admin_pass) > 18 )
	{
		die("<script>alert('密码长度应控制在6至18个字符之间。');</script>");
	}

	$site_str = str_replace('"siteurl" => "",','"siteurl" => "'.$thisUrl.'",',file_get_contents($file_site));

	$handle = @fopen($file_site, 'w');

	if ( @flock($handle, LOCK_EX) )
	{
		@fwrite($handle, $site_str);

		@flock($handle, LOCK_UN);
	}
			
	@fclose($handle);

	$config_str = "<?php";

	$config_str .= "\n";

	$config_str .= '$admin_config = array(';

	$config_str .= '"username"=>"'.$admin_user.'",';

	$config_str .= '"password"=>"'.md5($admin_pass).'",';

	$config_str .= '"authcode"=>"'.createSecureKey().'"';

	$config_str .= ");\n";

	$config_str .= '?>';

	$handle = @fopen($file_admin, 'w');

	if ( @flock($handle, LOCK_EX) )
	{
		@fwrite($handle, $config_str);

		@flock($handle, LOCK_UN);
	}
			
	@fclose($handle);

	$config_str = "<?php";

	$config_str .= "\n";

	$config_str .= '$mysql_host		= "'.$db_host.'";';

	$config_str .= "\n\n";

	$config_str .= '$mysql_user		= "'.$db_user.'";';

	$config_str .= "\n\n";

	$config_str .= '$mysql_pass		= "'.$db_pass.'";';

	$config_str .= "\n\n";

	$config_str .= '$mysql_dbname	= "'.$db_name.'";';

	$config_str .= "\n\n";

	$config_str .= '$mysql_prefix	= "'.$db_prefix.'";';

	$config_str .= "\n";

	$config_str .= '?>';

	$handle = @fopen($file_mysql, 'w');

	if ( @flock($handle, LOCK_EX) )
	{
		@fwrite($handle, $config_str);

		@flock($handle, LOCK_UN);
	}
			
	@fclose($handle);

	$link = mysql_connect($db_host, $db_user, $db_pass) or die("<script>alert('Mysql连接失败！错误代码：".mysql_errno() ."');</script>");

	mysql_query("CREATE DATABASE IF NOT EXISTS ".$db_name,$link) or die("<script>alert('数据库创建失败！错误代码：".mysql_errno() ."');</script>");

	mysql_select_db($db_name, $link) or die("<script>alert('数据库连接失败！错误代码：".mysql_errno() ."');</script>");

	mysql_query("DROP TABLE IF EXISTS `".$db_prefix."blog`");

	mysql_query("CREATE TABLE `".$db_prefix."blog` (
	  `mid` mediumint(8) NOT NULL AUTO_INCREMENT,
	  `message` char(140) NOT NULL,
	  `picture` char(50) NOT NULL,
	  `dateline` int(10) NOT NULL,
	  `origin` char(10) NOT NULL,
	  `comments` mediumint(8) NOT NULL,
	  PRIMARY KEY (`mid`),
	  KEY `dateline` (`dateline`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	mysql_query("DROP TABLE IF EXISTS `".$db_prefix."comment`");

	mysql_query("CREATE TABLE `".$db_prefix."comment` (
	  `cid` mediumint(8) NOT NULL AUTO_INCREMENT,
	  `mid` mediumint(8) NOT NULL,
	  `nickname` char(15) NOT NULL,
	  `blogurl` char(60) NOT NULL,
	  `message` char(70) NOT NULL,
	  `dateline` int(10) NOT NULL,
	  `blogmaster` tinyint(1) NOT NULL,
	  `display` tinyint(1) NOT NULL,
	  PRIMARY KEY (`cid`),
	  KEY `mid` (`mid`,`cid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	mysql_query("DROP TABLE IF EXISTS `".$db_prefix."friend`");

	mysql_query("CREATE TABLE `".$db_prefix."friend` (
	  `fid` mediumint(8) NOT NULL AUTO_INCREMENT,
	  `ftype` tinyint(1) NOT NULL,
	  `furl` char(90) NOT NULL,
	  `fcode` char(16) NOT NULL,
	  `fupdate` int(10) NOT NULL,
	  `friendavatar` char(50) NOT NULL,
	  `friendname` char(15) NOT NULL,
	  `friendmid` mediumint(8) NOT NULL,
	  `friendmsg` char(140) NOT NULL,
	  `friendpic` char(50) NOT NULL,
	  `friendtime` int(10) NOT NULL,
	  `friendorigin` char(10) NOT NULL,
	  PRIMARY KEY (`fid`),
	  KEY `ftype` (`ftype`,`fupdate`,`friendtime`),
	  KEY `furl` (`ftype`,`furl`,`fcode`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	mysql_close($link);

	@unlink("install.php");

	die("<script>alert('安装成功！管理员用户名：".$admin_user."，密码：".$admin_pass."');top.location.href='./'</script>");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>安装 Jotting</title>
<link rel="stylesheet" type="text/css" href="./_skin/default/style.css" />
<style>
.install_main{text-align:center;width:310px;margin:100px auto 20px auto;}
.install_div{font-size:14px;color:#555;}
.install_div li{margin-bottom:1px;list-style:none;}
.install_div li label{width:100px;float:left;margin-left:2px;text-align:right}
.install_div li span{font-size:12px;color:#999;text-align:left}
</style>
</head>
<body>
<div class="install_main">
<h2>Jotting 安装面板</h2>
<div class="install_div">
<form name="install-form" id="install-form" method="post" target="sypost">
<ul>
<li style="padding-top:2px;">
<label>Mysql 地址：</label>
<input type="text" name="host" id="host" class="btn_input" value="<?php echo $mysql_host;?>"  />
</li>
<li class="bd_t1" style="padding:7px 0 6px 0;">
<label>Mysql 库名：</label>
<input type="text" name="name" id="name" class="btn_input" value="<?php echo $mysql_dbname;?>"  />
</li>
<li class="bd_t1" style="padding:7px 0 6px 0;">
<label>Mysql 用户：</label>
<input type="text" name="user" id="user" class="btn_input" value="<?php echo $mysql_user;?>" />
</li>
<li class="bd_t1" style="padding:7px 0 6px 0;">
<label>Mysql 密码：</label>
<input type="text" name="passwd" id="passwd" class="btn_input" value="<?php echo $mysql_pass;?>" />
</li>
<li class="bd_t1" style="padding:7px 0 6px 0;">
<label>Mysql 表前缀：</label>
<input type="text" name="prefix" id="prefix" class="btn_input" value="<?php echo $mysql_prefix;?>" />
</li>
<li class="bd_t1" style="padding:7px 0 6px 0;">
<label>系统安装目录：</label>
<input type="text" name="site_link" id="site_link" class="btn_input" value="<?php echo $thisUrl;?>" />
</li>
<li class="bd_t1" style="padding:7px 0 6px 0;">
<label>管理员用户名：</label>
<input type="text" name="adminuser" id="adminuser" class="btn_input" value="" />
</li>
<li class="bd_t1" style="padding:7px 0 6px 0;">
<label>管理员的密码：</label>
<input type="text" name="adminpwd" id="adminpwd" class="btn_input" value="" />
</li>
<li class="bd_t1">
<label>&nbsp;</label>
<input type="submit" value="安 装" class="btn_login">
</li>
</ul>
</form>
<iframe scrolling=no width=0 height=0 src="" name="sypost" id="sypost" style="display: none"></iframe>
</div>
</div>
</body>
</html>
