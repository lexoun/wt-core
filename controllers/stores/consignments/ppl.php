<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

use Salamek\PplMyApi\Api;
use Salamek\PplMyApi\Tools;
use Salamek\PplMyApi\Model\Package;
use Salamek\PplMyApi\Model\PackageNumberInfo;
use Salamek\PplMyApi\Model\Recipient;
use Salamek\PplMyApi\Enum\Country;
use Salamek\PplMyApi\Enum\Depo;
use Salamek\PplMyApi\Enum\Product;



$username = 'trade';
$password = 'trade';
$customerId = '13297/10';

$pplMyApi = new Api($username, $password, $customerId);
if ($pplMyApi->isHealthy())
{
    echo 'Healthy :)' . PHP_EOL;
}
else
{
    echo 'Ill :(' . PHP_EOL;
}


$recipient = new Recipient('Olomouc', 'Adam Schubert', 'My Address', '77900', 'adam@example.com', '+420123456789', 'https://www.wellnesstrade.cz', Country::CZ, 'My Compamy a.s.');

$packageNumber = '41054770852';
/* Or you can use Tools::generatePackageNumber to get this number only from $packageSeriesNumberId like 114
$packageSeriesNumberId = 114;
$packageNumberInfo = new PackageNumberInfo($packageSeriesNumberId, Product::PPL_PARCEL_CZ_PRIVATE, Depo::CODE_09);
$packageNumber = Tools::generatePackageNumber($packageNumberInfo); //40950000114
*/
$weight = 3.15;
$package = new Package($packageNumber, Product::PPL_PARCEL_CZ_PRIVATE, $weight, 'Testovaci balik', Depo::CODE_09, $recipient);

try
{
    $pplMyApi->createPackages([$package]);
}
catch (\Exception $e)
{
    echo $e->getMessage() . PHP_EOL;
}








// list package

//use Salamek\PplMyApi\Api;
//
//$username = 'my_api_username';
//$password = 'my_api_password';
//$customerId = 'my_api_customer_id';
//
//$pplMyApi = new Api($username, $password, $customerId);
//$result = $pplMyApi->getPackages($customRefs = null, \DateTimeInterface $dateFrom = null, \DateTimeInterface $dateTo = null, array $packageNumbers = []);
//print_r($result);










// get label



//require __DIR__ . '/vendor/autoload.php';
//
//use Salamek\PplMyApi\Tools;
//use Salamek\PplMyApi\Model\PackageNumberInfo;
//use Salamek\PplMyApi\Model\Package;
//use Salamek\PplMyApi\Model\Recipient;
//use Salamek\PplMyApi\Model\Sender;
//use Salamek\PplMyApi\Enum\Country;
//use Salamek\PplMyApi\Enum\Product;
//use Salamek\PplMyApi\Enum\Depo;
//use Salamek\PplMyApi\PdfLabel;
//use Salamek\PplMyApi\ZplLabel;
//
//
//$sender = new Sender('Olomouc', 'My Compamy s.r.o.', 'My Address', '77900', 'info@example.com', '+420123456789', 'https://www.example.cz', Country::CZ);
//$recipient = new Recipient('Olomouc', 'Adam Schubert', 'My Address', '77900', 'adam@example.com', '+420123456789', 'https://www.salamek.cz', Country::CZ, 'My Compamy a.s.');
//
//$packageNumber = 40950000114;
///* Or you can use Tools::generatePackageNumber to get this number only from $packageSeriesNumberId like 114
//$packageSeriesNumberId = 114;
//$packageNumberInfo = new PackageNumberInfo($packageSeriesNumberId, Product::PPL_PARCEL_CZ_PRIVATE, Depo::CODE_09);
//$packageNumber = Tools::generatePackageNumber($packageNumberInfo); //40950000114
//*/
//$weight = 3.15;
//$package = new Package($packageNumber, Product::PPL_PARCEL_CZ_PRIVATE, $weight, 'Testovaci balik', Depo::CODE_09, $sender, $recipient);
//
//// PDF Label
//$rawPdf = PdfLabel::generateLabels([$package]);
//file_put_contents($package->getPackageNumber() . '.pdf', $rawPdf);
//
//// ZPL Label
//$rawZpl = ZplLabel::generateLabels([$package]);
//file_put_contents($package->getPackageNumber() . '.zpl', $rawZpl);