<?php
require 'settings.php';
require 'rest/Rest.php';
echo Rest::run($_SERVER, $_REQUEST);