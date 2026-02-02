<?php
$data = json_decode(file_get_contents('c:/xampp/htdocs/utool/data/nfse_services.json'), true);
print_r(array_keys($data));
