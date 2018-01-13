<?php

use Greenter\Model\Client\Client;
use Greenter\Model\Perception\Perception;
use Greenter\Model\Perception\PerceptionDetail;
use Greenter\Model\Retention\Exchange;
use Greenter\Model\Retention\Payment;
use Greenter\Ws\Services\SunatEndpoints;

require __DIR__ . '/../vendor/autoload.php';

$client = new Client();
$client->setTipoDoc('6')
    ->setNumDoc('20000000001')
    ->setRznSocial('EMPRESA 1');

$perception = new Perception();
$perception
    ->setSerie('P001')
    ->setCorrelativo('123')
    ->setFechaEmision(new \DateTime())
    ->setObservacion('NOTA PRUEBA />')
    ->setCompany(Util::getCompany())
    ->setProveedor($client)
    ->setImpPercibido(10)
    ->setImpCobrado(210)
    ->setRegimen('01')
    ->setTasa(2);

$pay = new Payment();
$pay->setMoneda('PEN')
    ->setFecha(new \DateTime())
    ->setImporte(100);

$cambio = new Exchange();
$cambio->setFecha(new \DateTime())
    ->setFactor(1)
    ->setMonedaObj('PEN')
    ->setMonedaRef('PEN');

$detail = new PerceptionDetail();
$detail->setTipoDoc('01')
    ->setNumDoc('F001-1')
    ->setFechaEmision(new \DateTime())
    ->setFechaPercepcion(new \DateTime())
    ->setMoneda('PEN')
    ->setImpTotal(200)
    ->setImpCobrar(200)
    ->setImpPercibido(5)
    ->setCobros([$pay])
    ->setTipoCambio($cambio);

$perception->setDetails([$detail]);

// Envio a SUNAT.
$see = Util::getSee(SunatEndpoints::RETENCION_BETA);

$res = $see->send($perception);
Util::writeXml($perception, $see->getFactory()->getLastXml());

if ($res->isSuccess()) {
    /**@var $res \Greenter\Model\Response\BillResult*/
    $cdr = $res->getCdrResponse();
    Util::writeCdr($perception, $res->getCdrZip());

    echo Util::getResponseFromCdr($cdr);
} else {
    var_dump($res->getError());
}

