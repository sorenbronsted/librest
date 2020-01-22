<?php
namespace sbronsted;

require 'settings.php';
require 'rest/Rest.php';
echo Rest::run($_SERVER, $_REQUEST);