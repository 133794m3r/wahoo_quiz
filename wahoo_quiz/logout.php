<?php
session_start();
session_destroy();
http_redirect('/index.php');