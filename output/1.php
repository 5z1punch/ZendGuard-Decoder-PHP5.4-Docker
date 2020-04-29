<?php

include_once "inc/session.php";
session_start();
ob_start();
include_once "inc/conn.php";
include_once "inc/td_core.php";
include_once "inc/utility.php";
include_once "inc/utility_cache.php";

if ($_POST["LANGUAGE"] != "") {
	$LANG_ARRAY = get_lang_array();

	if (in_array($_POST["LANGUAGE"], $LANG_ARRAY)) {
		setcookie("LANG_COOKIE", $_POST["LANGUAGE"], time() + (60 * 60 * 24 * 1000), "/");
	}
}

$HTML_PAGE_TITLE = _("系统登录");
include_once "inc/header.inc.php";
echo "<body class=\"bodycolor\">\r\n<style>\r\n.MessageBox{\r\n\tmargin: 80px auto;\r\n}\r\n</style>\r\n";

if ($UNAME != "") {
	$USERNAME = $UNAME;
}
else if ($_POST["USERNAME"] != "") {
	$USERNAME = $_POST["USERNAME"];
}
else {
	$query = "select BYNAME from user where USER_ID ='$KEY_USER'";
	$cursor = exequery(TD::conn(), $query);

	if ($row = mysql_fetch_array($cursor)) {
		$USERNAME = $row[0];
	}
}

$USERNAME = trim($USERNAME);
$encode_type = intval($encode_type);
$PASSWORD = ($encode_type == 1 ? base64_decode((string) $PASSWORD) : $PASSWORD);
$LOGIN_MSG = login_check($USERNAME, $PASSWORD, $KEY_DIGEST, $KEY_SN, $KEY_USER, 0);

if ($LOGIN_MSG != "1") {
	$ARR_BTN = array(
		array("value" => _("重新登录"), "href" => "/")
		);
	$SYS_PARA = get_sys_para("RETRIEVE_PWD");
	if (($SYS_PARA["RETRIEVE_PWD"] == "1") && ($USERNAME != "admin")) {
		$_SESSION["RETRIEVE_PWD_USER"] = strip_tags($USERNAME);
		$ARR_BTN[] = array("value" => _("找回密码"), "href" => "/module/retrieve_pwd/");
	}

	Message(_("错误"), $LOGIN_MSG, "error", $ARR_BTN);

	if ($USERNAME == "admin") {
		echo "<br><div class=small1 align=center>" . _("忘记了admin密码？请参考官方网站/帮助与支持/OA知识库/Office Anywhere 疑难解答/清空admin密码") . "</div>";
	}

	exit();
}

$PARA_ARRAY = get_sys_para("SEC_INIT_PASS");
$SEC_INIT_PASS = $PARA_ARRAY["SEC_INIT_PASS"];
$modify_pwd = 0;

if ($SEC_INIT_PASS == "0") {
	$query = "select LAST_PASS_TIME from user where UID = '" . $_SESSION["LOGIN_UID"] . "'";
	$cursor = exequery(TD::conn(), $query);

	if ($ROW = mysql_fetch_array($cursor)) {
		$LAST_PASS_TIME = $ROW["LAST_PASS_TIME"];
	}

	if (($LAST_PASS_TIME == "") || ($LAST_PASS_TIME == "0000-00-00 00:00:00")) {
		$modify_pwd = 1;
	}
	else {
		$modify_pwd = 0;
	}
}

if (!empty($u)) {
	$_SESSION["USER_LOGGED"] = $_SESSION["LOGIN_USER_ID"];
	header("Location: $u");
	exit();
}

if (isset($_POST["THEME"]) && !preg_match("[^0-9]", $_POST["THEME"])) {
	$_SESSION["LOGIN_THEME"] = $_POST["THEME"];
}

$MENU_TYPE = GetUserInfoByUID($_SESSION["LOGIN_UID"], "MENU_TYPE");
$OA_UI = "./general/";
echo $uc_synclogin_script;
echo "<script>\r\nvar isIE = 0;\r\nif(/msie\s[6-8]/ig.test(navigator.userAgent)){\r\n    isIE = 1;\r\n}\r\nvar modify_pwd = \"";
echo $modify_pwd;
echo "\";\r\nvar url = \"./general/index.php?isIE=\"+isIE+\"&modify_pwd=0\";\r\nfunction goto_oa()\r\n{\r\n    window.location=url;//\"";
echo $OA_UI;
echo "\";\r\n}\r\n";
if (($MENU_TYPE == 1) || stristr($HTTP_USER_AGENT, "Opera") || stristr($HTTP_USER_AGENT, "Firefox") || stristr($HTTP_USER_AGENT, "MSIE 5.0") || stristr($HTTP_USER_AGENT, "TencentTraveler")) {
	echo "    goto_oa();\r\n";
}
else {
	echo "window.setTimeout('goto_oa();',3000);\r\nvar open_flag=window.open(\"";
	echo $OA_UI;
	echo "\",'";
	echo md5($USERNAME) . time();
	echo "',\"menubar=0,toolbar=";

	if ($MENU_TYPE == 2) {
		echo "1";
	}
	else {
		echo "0";
	}

	echo ",status=1,resizable=1\");\r\nif(open_flag== null){\r\n    goto_oa();\r\n}\r\nelse\r\n{\r\n    focus();\r\n    window.opener =window.self;\r\n    window.close();\r\n}\r\n";
}

echo "</script>\r\n\r\n<div class=big1>";
echo _("正在进入OA系统，请稍候...");
echo "</div>\r\n</body>\r\n</html>\r\n";

?>
