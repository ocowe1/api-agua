<?php
header('Content-Type: application/json');
use Util\ConstantesGenericasUtil;
use Util\JsonUtil;
use Validator\RequestValidator;
use Util\RotasUtil;

require_once('bootstrap.php');

try {
    $requestValidator = new RequestValidator(RotasUtil::getRotas());
    $retorno = $requestValidator->processarRequest();

    $JsonUtil = new JsonUtil();
    $JsonUtil->processarArrayRetorno($retorno);

} catch (Exception $exception) {
    echo json_encode([
        ConstantesGenericasUtil::TIPO => ConstantesGenericasUtil::TIPO_ERRO,
        ConstantesGenericasUtil::RESPOSTA => $exception->getMessage()
    ]);
}
