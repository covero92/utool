<?php
$xmlContent = "<?xml version='1.0' encoding='UTF-8'?><DPS xmlns=\"http://www.sped.fazenda.gov.br/nfse\" versao=\"1.01\"><infDPS Id=\"DPS431410025112006600015600900000000000202551\"><tpAmb>1</tpAmb><dhEmi>2026-01-12T15:34:00-03:00</dhEmi><verAplic>Uniplus-6.12.32</verAplic><serie>900</serie><nDPS>202551</nDPS><dCompet>2026-01-12</dCompet><tpEmit>1</tpEmit><cLocEmi>4314100</cLocEmi><prest><CNPJ>51120066000156</CNPJ><fone>5599249284</fone><email>adm@academymaster.com.br</email><regTrib><opSimpNac>1</opSimpNac><regEspTrib>0</regEspTrib></regTrib></prest><toma><CNPJ>24146998000103</CNPJ><xNome>J H DE OLIVEIRA SOFTWARE</xNome><end><endNac><cMun>4317509</cMun><CEP>98801495</CEP></endNac><xLgr>RUA SETE DE SETEMBRO</xLgr><nro>134</nro><xCpl>SALA 01</xCpl><xBairro>CENTRO</xBairro></end><fone>5533132262</fone><email>contato@otimizer.com.br</email></toma><serv><locPrest><cLocPrestacao>4314100</cLocPrestacao></locPrest><cServ><cTribNac>171001</cTribNac><xDescServ>17.10 - PLANEJAMENTO E ORGANIZACAO DE FEIRAS RET. PASSO FUNDO.</xDescServ><cNBS>118066300</cNBS></cServ><infoCompl><xInfComp>Responsabilidade do ISS e do prestadorr do servico. IR RETIDO: R$ 0,08</xInfComp></infoCompl></serv><valores><vServPrest><vServ>5.00</vServ></vServPrest><trib><tribMun><tribISSQN>1</tribISSQN><tpRetISSQN>1</tpRetISSQN></tribMun><tribFed><piscofins><CST>01</CST><vBCPisCofins>5.00</vBCPisCofins><pAliqPis>0.65</pAliqPis><pAliqCofins>3.00</pAliqCofins><vPis>0.03</vPis><vCofins>0.15</vCofins><tpRetPisCofins>1</tpRetPisCofins></piscofins><vRetIRRF>0.08</vRetIRRF><vRetCSLL>0.05</vRetCSLL></tribFed><totTrib><vTotTrib><vTotTribFed>0.67</vTotTribFed><vTotTribEst>0.00</vTotTribEst><vTotTribMun>0.23</vTotTribMun></vTotTrib></totTrib></trib></valores><IBSCBS><finNFSe>0</finNFSe><indFinal>1</indFinal><cIndOp>040101</cIndOp><indDest>0</indDest><valores><trib><gIBSCBS><CST>000</CST><cClassTrib>000001</cClassTrib></gIBSCBS></trib></valores></IBSCBS></infDPS></DPS>";

$dom = new DOMDocument();
$dom->loadXML($xmlContent);

libxml_use_internal_errors(true);

if ($dom->schemaValidate(__DIR__ . '/NFSE Nacional/Schemas/DPS_v1.01.xsd')) {
    echo "VALIDATION SUCCESS";
} else {
    echo "VALIDATION FAILED\n";
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
        echo trim($error->message) . "\n";
    }
    libxml_clear_errors();
}
?>
