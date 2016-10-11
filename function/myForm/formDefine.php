<?php
define("FORM_MODE_INPUT",		1);
define("FORM_MODE_INPUT2",		2);
define("FORM_MODE_INPUT3",		3);
define("FORM_MODE_INPUT4",	4);
define("FORM_MODE_INPUT5",	5);
define("FORM_MODE_MAIL",	6);
define("FORM_MODE_JRA_MEMBER",	7);
define("FORM_MODE_NO_NICKNAME",	8);
define("FORM_MODE_NICKNAME_NO_USE",	9);
define("FORM_MODE_NICKNAME_USE",	10);
define("FORM_MODE_NO_NICKNAME_FROM_MAIL",	11);
define("FORM_MODE_NICKNAME_NO_USE_FROM_MAIL",	12);
define("FORM_MODE_NICKNAME_USE_FROM_MAIL",	13);

define("FORM_INPUT_TYPE_SPECIAL",	0);
define("FORM_INPUT_TYPE_OTHER",		1);
define("FORM_INPUT_TYPE_RADIO",		2);
define("FORM_INPUT_TYPE_SELECT",	3);
define("FORM_INPUT_TYPE_CHECK",		4);


// NULLチェックのmethodだけ特別扱い
define("NOT_NULL_CHECK_METHOD",	"notNull");

// パラメータセットするときの変換モード
define("CONVERT_MODE_INPUT", 1);
define("CONVERT_MODE_INPUT2", 2);
define("CONVERT_MODE_INPUT2_FROM_MAIL", 3);
