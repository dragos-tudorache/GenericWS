<?php
define("BASE_PATH", dirname(dirname(__FILE__)));
set_include_path(
	get_include_path().
	PATH_SEPARATOR.BASE_PATH."/".
	PATH_SEPARATOR.BASE_PATH."/lib"
	);

const DB_TYPE="mysql";
const DB_HOST="localhost";
const DB_NAME="mesher";
const DB_USER="root";
const DB_PASS="";

const TIMEZONE_FORMAT="Y/m/j H:i:s";