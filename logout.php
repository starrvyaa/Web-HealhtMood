<?php
require_once 'config.php';
session_destroy();
redirect_to('login.php');
