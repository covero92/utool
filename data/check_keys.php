<?php
$data = json_decode(file_get_contents('c:/xampp/htdocs/utool/data/nfse_services.json'), true);
if (isset($data['LISTA.SERV.NAC.'][0])) {
    print_r(array_keys($data['LISTA.SERV.NAC.'][0]));
} else {
    echo "Key LISTA.SERV.NAC. not found or empty.\n";
    print_r(array_keys($data));
}
