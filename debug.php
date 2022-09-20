<?php
fwrite($fp = fopen("php://stdout", "w"), trim(file_get_contents('php://input'))."\n");
fclose($fp);
