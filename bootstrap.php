<?php

ini_set('display_errors', 0);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ERROR);

define(HOST, 'localhost');
define(USUARIO, 'root');
define(BANCO, 'api');
define(SENHA, '');

define(DS, DIRECTORY_SEPARATOR);
define(DIR_APP, __DIR__);
define(DIR_API, 'api-challenge');

if (file_exists('autoload.php')){
    include 'autoload.php';
}else{
    die('Falha ao carregar autoload!');
}
