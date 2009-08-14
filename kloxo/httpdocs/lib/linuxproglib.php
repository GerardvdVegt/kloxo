<?php 

function os_doUpdateExtraStuff()
{
	//exec_with_all_closed("service qmail restart");
}


function os_update_server()
{
	system("yum -y install --nosig webalizer lxjailshell autorespond unzip lxlighttpd lxzend lxphp >/dev/null 2>&1 &");
	os_fix_some_permissions();
	lxfile_touch("../etc/flag/lowmem.flag");
	os_createLowMem();

}


function os_createLowMem()
{
	if (!lxfile_exists("/proc/user_beancounters") && !lxfile_exists("/proc/xen")) {
		if (!lxshell_return("diff", "../file/lowmem/my.cnf.lowmem", "/etc/my.cnf")) {
			lxfile_cp("/etc/lxlabs.saved.my.cnf", "/etc/my.cnf");
			createRestartFile("mysqld");
		}
		return;
	}

	if (lxfile_exists("__path_program_etc/flag/lowmem.flag")) {

		if (!lxfile_exists("/etc/lxlabs.saved.my.cnf")) {
			lxfile_cp("/etc/my.cnf", "/etc/lxlabs.saved.my.cnf");
			lxfile_cp("../file/lowmem/my.cnf.lowmem", "/etc/my.cnf");
			createRestartFile('mysql');
			createRestartFile('courier-imap');
		}
		//lxfile_cp("../file/lowmem/spamassassin.lowmem", "/etc/sysconfig/spamassassin");
	}
}

function os_create_kloxo_service_once() { }
function os_set_iis_ftp_root_path() { }


function os_fix_some_permissions()
{
	//lxshell_return("chcon", "-R", "-t", "httpd_user_content_t", "/home/kloxo/");
	//lxshell_return("chcon", "-R", "-t", "httpd_user_content_t", "/home/httpd/");
}

function remove_lighttpd_error_log()
{
	$f = "/home/kloxo/httpd/lighttpd/error.log";
	$s = lxfile_size($f);
	if ($s > 50 * 1024 * 1024) {
		lunlink($f);
		createRestartFile("lighttpd");
	}
}

function create_dev()
{
	if (lxfile_exists("/sbin/udevd")) {
		lxfile_mv("/sbin/udevd", "/sbin/udevd.back");
	}
	lxshell_return('tar', '-C', '/dev', '-xzf', '../file/centos-5/vps-dev.tgz');
	lxshell_return('/sbin/MAKEDEV', 'pty');
	lxshell_return('/sbin/MAKEDEV', 'tty');
	lxshell_return('/sbin/MAKEDEV', 'loop');
}


function fix_hordedb_proper()
{
	lxshell_php("../bin/misc/lxinstall_hordegroupware_db.php");
}

function os_updateApplicableToSlaveToo()
{


	fix_arch();
	install_gd();
	install_bogofilter();
	move_clients_to_client();
	os_doUpdateExtraStuff();
	lxfile_cp("../file/phpmyadmin_config.inc.phps", "thirdparty/phpMyAdmin/config.inc.php");
	call_with_flag('installgroupwareagain');

	$desc = uuser::getUserDescription('admin');
	$list = posix_getpwnam('admin');
	if ($list && ($list['gecos'] !== $desc)) {
		lxshell_return("usermod", "-c", $desc, "admin");
	}

	if (os_is_php_six_four()) {
		$ver = get_package_version("kloxophpsixfour");
		installWithVersion("/usr/lib/kloxophp", "kloxophpsixfour", $ver);
		if (!lxfile_exists("/usr/lib/php")) {
			lxfile_symlink("/usr/lib64/php", "/usr/lib/php");
		}
	} else {
		$ver = get_package_version("kloxophp");
		installWithVersion("/usr/lib/kloxophp", "kloxophp", $ver);
	}
	$ver = get_package_version("lxwebmail");
	installWebmail($ver);
	$ver = get_package_version("lxawstats");
	installAwstats($ver);

	if (!lxfile_exists("/home/kloxo/httpd/back.webmail.4310")) {
		//lxfile_mv_rec("/home/kloxo/httpd/webmail/", "/home/kloxo/httpd/back.webmail.4310");
	}


	if (!lxfile_exists("/home/kloxo/httpd/webmail/roundcube/config/db.inc.php")) {
	}
	lxfile_cp("../file/webmail-chooser/db.inc.phps", "/home/kloxo/httpd/webmail/roundcube/config/db.inc.php");

	//installWithVersion("/home/kloxo/httpd/awstats/", "lxawstats", "4320");
	lxfile_mkdir("/etc/lighttpd/conf/kloxo");
	lxfile_mkdir("/var/bogofilter");
	lxfile_mkdir("/home/kloxo/httpd/lighttpd");
	rmdir("/home/admin/domain/");
	rmdir("/home/admin/old/");
	rmdir("/home/admin/cgi-bin/");
	rmdir("/etc/skel/Maildir/new");
	rmdir("/etc/skel/Maildir/cur");
	rmdir("/etc/skel/Maildir/tmp");
	rmdir("/etc/skel/Maildir/");
	system("cp ../cexe/lxrestart /usr/sbin/");
	system("chown root:root /usr/sbin/lxrestart");
	system("chmod 755 /usr/sbin/lxrestart");
	system("chmod ug+s /usr/sbin/lxrestart");
	lunlink("/usr/sbin/sendmail");
	lunlink("/usr/lib/sendmail");
	lxfile_cp("../file/linux/qmail-sendmail", "/usr/sbin/sendmail");
	lxfile_cp("../file/linux/qmail-sendmail", "/usr/lib/sendmail");
	lxfile_unix_chmod("/usr/lib/sendmail", "0755");
	lxfile_unix_chmod("/usr/sbin/sendmail", "0755");
	system("cp ../file/linux//lxredirecter.sh /usr/bin/");
	system("chmod 755 /usr/bin/lxredirecter.sh");
	if (!lxfile_exists("/usr/bin/php-cgi")) {
		lxfile_cp("/usr/bin/php", "/usr/bin/php-cgi");
	}

	if (!lxfile_exists("/usr/local/bin/php")) {
		lxfile_symlink("/usr/bin/php", "/usr/local/bin/php");
	}
	if (lxfile_exists('kloxo.sql')) {
		lunlink('kloxo.sql');
	}

	remove_lighttpd_error_log();
	call_with_flag("fix_secure_log");
	call_with_flag("remove_host_deny");

	
	installInstallSoft();

	if (!lxfile_exists("/etc/pure-ftpd/pureftpd.pdb")) {
		lxfile_touch("/etc/pure-ftpd/pureftpd.passwd");
		lxshell_return("pure-pw", "mkdb");
	}

	system("chkconfig gpm off");
	//system("rpm -e php-mcrypt >/dev/null 2>&1");
	lxfile_rm("phpinfo.php");

	$ret = lxshell_return("rpm", "-q", "maildrop-toaster");
	if ($ret) {
		lxshell_return("yum", "-y", "install", "maildrop-toaster");
	}

	$ret = lxshell_return("rpm", "-q", "spamdyke");
	if ($ret) {
		lxshell_return("yum", "-y", "install", "spamdyke", "spamdyke-utils");
	}

	lxfile_touch("/var/named/chroot/etc/kloxo.named.conf");
	lxshell_return("pkill", "-f", "gettraffic");


	install_if_package_not_exist("pure-ftpd");
	install_if_package_not_exist("simscan-toaster");
	install_if_package_not_exist("webalizer");
	install_if_package_not_exist("php-mcrypt");

	if (trim(lfile_get_contents("/var/qmail/control/me")) === "core.lxlabs.com") {
		system("echo `hostname` > /var/qmail/control/me");
		createRestartFile("qmail");
	}

	if (trim(lfile_get_contents("/var/qmail/control/me")) === "test.lxlabs.com") {
		system("echo `hostname` > /var/qmail/control/me");
		createRestartFile("qmail");
	}

	copy_script();
	install_xcache();

	lxfile_unix_chmod("/etc/init.d/kloxo", "0755");
	system("chkconfig kloxo on");
	install_if_package_not_exist("dos2unix");
	install_if_package_not_exist("rrdtool");
	addLineIfNotExistInside("/etc/shells", "/usr/bin/lxjailshell", "");
	lxfile_cp("htmllib/filecore/execzsh.sh", "/usr/bin/execzsh.sh");
	lxfile_unix_chmod("/usr/bin/execzsh.sh", "0755");
	lxfile_unix_chmod("/home", "0755");

	if (is_centosfive()) {
		lxshell_return("sh", "../pscript/centos5-postpostupgrade");
		lxfile_cp("../file/centos-5/CentOS-Base.repo", "/etc/yum.repos.d/CentOS-Base.repo");
		lxfile_rm("/etc/yum.repos.d/epel.repo");
		//lxfile_cp("../file/centos-5/epel.repo", "/etc/yum.repos.d/");
	}

	fix_rhn_sources_file();
	lxfile_symlink("__path_php_path", "/usr/bin/lxphp.exe");
	@ lxfile_rm("__path_program_htmlbase/ol.out");
	lxfile_cp("../file/apache/kloxo.conf", "/etc/httpd/conf/kloxo/kloxo.conf");
	lxfile_cp("../file/apache/default_ssl.conf", "/etc/httpd/conf.d/ssl.conf");
	lxfile_touch("/etc/httpd/conf/kloxo/webmail_redirect.conf");
	lxfile_touch("/etc/httpd/conf/kloxo/ssl.conf");
	lxfile_touch("/etc/httpd/conf/kloxo/default.conf");
	lxfile_touch("/etc/httpd/conf/kloxo/cp_config.conf");
	lunlink("../log/access_log");
	lunlink("../log/lighttpd_error.log");


	@ lxfile_rm("/etc/init.d/pure-ftpd");

	if (!lxfile_exists("/etc/xinetd.d/pureftp")) {
		lxfile_cp("../file/xinetd.pureftp", "/etc/xinetd.d/pureftp");
	}

	if(!lxfile_real("/etc/pki/pure-ftpd/pure-ftpd.pem")) {
		lxfile_mkdir("/etc/pki/pure-ftpd/");
		lxfile_cp("../file/program.pem", "/etc/pki/pure-ftpd/pure-ftpd.pem");
	}

	if (!lxfile_exists("/etc/xinetd.d/smtp_lxa")) {
		lxfile_cp("../file/xinetd.smtp_lxa", "/etc/xinetd.d/smtp_lxa");
	}
	@ lxfile_rm("/etc/xinetd.d/pure-ftpd");
	lxfile_cp("../file/qmail.init", "/etc/init.d/qmail");
	lxfile_unix_chmod("/etc/init.d/qmail", "0755");
	lxfile_cp("../file/lxrestricted", "/etc/lxrestricted");
	lxfile_cp("../file/sysconfig_spamassassin", "/etc/sysconfig/spamassassin");

	lxfile_cp("/var/qmail/control/me", "/var/qmail/control/defaultdomain");
	lxfile_cp("/var/qmail/control/me", "/var/qmail/control/defaulthost");
	$name = trim(lfile_get_contents("/var/qmail/control/me"));
	lfile_put_contents("/var/qmail/control/smtpgreeting", "$name - Welcome to Qmail");

	if (!lxfile_exists("/usr/bin/rblsmtpd")) {
		lxshell_return("ln", "-s", "/usr/local/bin/rblsmtpd", "/usr/bin/");
	}
	if (!lxfile_exists("/usr/bin/tcpserver")) {
		lxshell_return("ln", "-s", "/usr/local/bin/tcpserver", "/usr/bin/");
	}

	//system("yum -y install lxlighttpd");


	call_with_flag("enable_xinetd");
	fix_suexec();

	call_with_flag("restart_xinetd_for_pureftp");

	if (!lxfile_exists("/usr/bin/php-cgi")) {
		lxfile_cp("/usr/bin/php", "/usr/bin/php-cgi");
	}
	lxfile_unix_chmod("/usr/bin/php-cgi", "0755");
	lxfile_unix_chmod("../cexe/closeallinput", "0755");
	lxfile_unix_chown("../cexe/lxphpsu", "root:root");
	lxfile_unix_chmod("../file/phpsuexec.sh", "0755");
	lxfile_unix_chmod("../cexe/lxphpsu", "0755");
	lxfile_unix_chmod("../cexe/lxphpsu", "ug+s");
	system("chown -R apache:apache /home/kloxo/httpd/lighttpd/");
	system("chmod 777 /var/lib/php/session/");
	system("chmod o+t /var/lib/php/session/");
	system("chmod 777 /var/bogofilter/");
	system("chmod o+t /var/bogofilter/");
	system("pkill -f sisinfoc");

	lxfile_cp("../file/lighttpd/lighttpd.conf", "/etc/lighttpd/lighttpd.conf");
	lxfile_cp("../file/lighttpd/conf/kloxo/kloxo.conf", "/etc/lighttpd/conf/kloxo/kloxo.conf");
	lxfile_touch("/etc/lighttpd/conf/kloxo/webmail_redirect.conf");

	if (!lxfile_real("/etc/lighttpd/local.lighttpd.conf")) {
		system("echo > /etc/lighttpd/local.lighttpd.conf");
	}
	if (!lxfile_real("/etc/lighttpd/conf/kloxo/webmail_redirect.conf")) {
		system("echo > /etc/lighttpd/conf/kloxo/webmail_redirect.conf");
	}
	if (!lxfile_real("/etc/lighttpd/conf/kloxo/virtualhost.conf")) {
		//system("echo > /etc/lighttpd/conf/kloxo/virtualhost.conf");
	}
	if (!lxfile_real("/etc/lighttpd/conf/kloxo/domainip.conf")) {
		system("echo > /etc/lighttpd/conf/kloxo/domainip.conf");
	}

	if (!lxfile_real("/etc/lighttpd/conf/kloxo/ssl.conf")) {
		system("echo > /etc/lighttpd/conf/kloxo/ssl.conf");
	}

	if (!lxfile_real("/etc/lighttpd/conf/kloxo/mimetype.conf")) {
		system("echo > /etc/lighttpd/conf/kloxo/mimetype.conf");
	}
	lxfile_touch("/etc/httpd/conf/kloxo/domainip.conf");
	lxfile_touch("/etc/httpd/conf/kloxo/mimetype.conf");

	lxfile_cp("../file/lighttpd/etc_init.d", "/etc/init.d/lighttpd");

	if (!lxfile_exists("/etc/pure-ftpd/pureftpd.passwd")) {
		lxfile_cp("/etc/pureftpd.passwd", "/etc/pure-ftpd/pureftpd.passwd");
		lxshell_return("pure-pw", "mkdb");
		createRestartFile("xinetd");
	}

	if (!lxfile_exists("../etc/flag/xcache_enabled.flg")) {
		lunlink("/etc/php.d/xcache.ini");
	} else {
		//lxfile_cp("../file/centos-5/xcache.ini", "/etc/php.d/");
	}

	$ret = lxshell_return("rpm", "-q", "xinetd");
	if ($ret) {
	    lxshell_return("yum", "-y", "install",  "xinetd");
	}

	$ret = lxshell_return("rpm", "-q", "lxjailshell");
	if ($ret) {
		lxshell_return("yum", "-y", "install",  "lxjailshell");
	}

	$ret = lxshell_return("rpm", "-q", "php-xml");
	if ($ret) {
		lxshell_return("yum", "-y", "install",  "php-xml");
	}

	$ret = lxshell_return("rpm", "-q", "libmhash");

	if ($ret) {
	    lxshell_return("yum", "-y", "install", "lxphp");
	}

	//install_if_package_not_exist("ImageMagick");

	system("chmod 666 /dev/null");
	@ exec("chkconfig pure-ftpd off 2>/dev/null");
	$string = null;
	$uid = os_get_uid_from_user("lxlabs");
	$gid = os_get_gid_from_user("lxlabs");
	$string .= "#!/bin/sh\n";
	$string .= "export MUID=$uid\n";
	$string .= "export GID=$gid\n";
	$string .= "export TARGET=/usr/bin/php-cgi\n";
	$string .= "export NON_RESIDENT=1\n";
	$string .= "exec lxsuexec $*\n";
	lfile_put_contents("/home/httpd/nobody.sh", $string);
	lxfile_unix_chmod("/home/httpd/nobody.sh", "0755");


	system("sh ../bin/misc/lxpopuser.sh");


	installRoundCube();
	installChooser();
	installLxetc();
	lxfile_rm_content("__path_home_root/httpd/script/");
	lxfile_mkdir("/home/kloxo/httpd/script");
	lxfile_unix_chown_rec("/home/kloxo/httpd/script", "lxlabs:lxlabs");
	lxfile_cp("../file/script/phpinfo.phps", "/home/kloxo/httpd/script/phpinfo.php");
	lxfile_cp("../file/djbdns.init", "/etc/init.d/djbdns");
	//system("lphp.exe ../bin/fix/fixfrontpage.php");
	removeOtherDriver();
	lxfile_rm_rec("__path_program_root/cache");
	createRestartFile('syslog');
	lxfile_mkdir("/home/kloxo/httpd/awstats/dirdata");
	
	//call_with_flag("remove_ssh_self_host_key");
	remove_test_root();

	if (lxfile_exists("/etc/httpd/conf/httpd.conf")) {
		addLineIfNotExistInside("/etc/httpd/conf/httpd.conf", "Include /etc/httpd/conf/kloxo/kloxo.conf", "");
	}

	if (lxfile_exists("/proc/user_beancounters") || lxfile_exists("/proc/xen")) {
	    create_dev();
	    lxfile_cp("../file/openvz/inittab", "/etc/inittab");
	} else {
	    if (!lxfile_exists("/sbin/udevd")) {
		lxfile_mv("/sbin/udevd.back", "/sbin/udevd");
	    }
	}

}

function remove_test_root()
{
	$pass = slave_get_db_pass();
	$__tr = mysql_connect("localhost", "root", $pass);
	mysql_select_db("mysql", $__tr);
	mysql_query("delete from user where Host = 'test.lxlabs.com' and User = 'root'", $__tr);
}

function remove_ssh_self_host_key()
{
	remove_line("/root/.ssh/authorized_keys2", "root@self.lxlabs.com");
}

function remove_host_deny()
{
	system("echo > /etc/hosts.deny");
}