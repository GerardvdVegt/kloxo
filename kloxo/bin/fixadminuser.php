<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');
$list = posix_getpwnam('admin');

if (!$list) {
	os_create_system_user('admin', $login->password, 'admin', '/sbin/nologin', '/home/admin');
	lxfile_unix_chown_rec("/home/admin", "admin");
	lxshell_return("__path_php_path", "../bin/misc/fixwebdnsfullupdate.php");
	lxshell_return("__path_php_path", "../bin/misc/fixftpuserclient.phps");
}