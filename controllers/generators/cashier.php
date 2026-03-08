<?php

if (!isset($_REQUEST['html'])) { $_REQUEST['html'] = ''; }

include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php';

$location_id = $_REQUEST['location_id'];
$date = $_REQUEST['date'];

$month = date('m', strtotime($date));
$month_formated =  strftime("%B", strtotime($date));
$year = date('Y', strtotime($date));


$list = '';

$records_query = $mysqli->query("SELECT * FROM cashier WHERE MONTH(date) = MONTH('".$date ."') AND YEAR(date) = YEAR('".$date ."') AND location_id = '".$location_id."' ORDER BY id ASC") or die($mysqli->error);


        $i = 0;
        while ($record = mysqli_fetch_array($records_query)) {

            $i++;

            if (!empty($record['income'])) {

                $income = '<strong class="text-success">+' . thousand_seperator($record['income']) . ' Kč</strong>';

            } else {
                $income = '-';
            }


            if (!empty($record['outcome'])) {


                $outcome = '<strong class="text-danger">' . thousand_seperator($record['outcome']) . ' Kč</strong>';

            } else {
                $outcome = '-';
            }

            $list .= ' <tr class="even">
                <td class="text-center">
                   '.$i.'
                </td>
                <td class="text-center">
                    ' . $record['date'] . '
                </td>
                <td class="text-center">
                     ' . $record['invoice_id'] . '
                </td>
                <td class="text-center">
                     ' . $record['description'] . '
                </td>
                <td class="text-center">
                    ' . $income . '
                </td>
                <td class="text-center">
                    ' . $outcome . '
                    
                </td>
                <td class="text-center">
                    <strong>' . thousand_seperator($record['balance']) . '</strong> Kč
                </td>
            </tr>';

        }



$site_logo = '<img src="../../assets/images/wellnesstrade-shop.png" style="margin: 9px 0 0; float: left;" width="140">';

$mpdf = new Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);

//==============================================================

$html = '
<style>
@page{

    margin: 5px 0;
    background-color: #FFFFFF;
}

body, div, p {
    font-family: "Roboto", Helvetica;
    font-size: 6px;
    color: #000000;
}

strong { font-family: Roboto-Medium, Helvetica; font-weight: 500;}

.product {
    width: 13.6%; 
    float: left; 
    margin: 0 0.54% 10px; 
    float: left;
}

.product table {
    width: 100%; 
    float: left;
}


.product table tr td {
    border-bottom: 1px solid #fff; 
}

.gradient {
    border:0.1mm solid #220044;
    background-color: #f0f2ff;
    background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
    box-shadow: 0.3em 0.3em #888888;
}
h4 {
    font-weight: bold;
    margin-top: 1em;
    margin-bottom: 0.3em;
    margin-top: 0;
}
div.text {
    padding:0.8em;
    margin-bottom: 0.7em;
}
p { margin: 0.1em 0; }
p.code {
    background-color: #e5e5e5;
    margin: 1em 1cm;
    padding: 0 0.3cm;
    border:0.2mm solid #000088;
    box-shadow: 0.3em 0.3em #888888;
}
p.example, div.example {
    background-color: #eeeeee;
    margin: 0.3em 1em 1em 1em;
    padding: 0 0.3cm;
    border:0.2mm solid #444444;
}
.code {
    font-family: monospace;
    font-size: pt;
}
.shadowtitle {
    height: 8mm;
    background-color: #EEDDFF;
    background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
    padding: 0.8em;
    padding-left: 3em;
    font-family:sans;
    font-size: 26pt;
    font-weight: bold;
    border: 0.2mm solid white;
    border-radius: 0.2em;
    box-shadow: 0 0 1em 0.5em rgba(0,0,255,0.5);
    color: #AAAACC;
    text-shadow: 0.03em 0.03em #666, 0.05em 0.05em rgba(127,127,127,0.5), -0.015em -0.015em white;
}
h3 {
    margin: 3em 0 2em -15mm;
    background-color: #EEDDFF;
    background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
    padding: 0.5em;
    padding-left: 3em;
    width: 50%;
    font-family:sans;
    font-size: 16pt;
    font-weight: bold;
    border-left: none;
    border-radius: 0 2em 2em 0;
    box-shadow: 0 0 2em 0.5em rgba(255,0,0,1);
    text-shadow: 0.05em 0.04em rgba(127,127,127,0.5);
}
.css {
    font-family: arial;
    font-style: italic;
    color: #000088;
}
img.smooth {
    image-rendering:auto;
    image-rendering:optimizeQuality;
    -ms-interpolation-mode:bicubic;
}
img.crisp {
    image-rendering: -moz-crisp-edges;      /* Firefox */
    image-rendering: -o-crisp-edges;        /* Opera */
    image-rendering: -webkit-optimize-contrast;/* Webkit (non-standard naming) */
    image-rendering: crisp-edges;
    -ms-interpolation-mode: nearest-neighbor;   /* IE (non-standard property) */
}


table {
    border-collapse: collapse;
    margin: 20px;
}

table, th, td {
    border: 0;
}

table thead th {border-bottom: 1px solid #000;}

table.products {  color: #000; font-size: 10px;}

table.products td { text-align: center; border-bottom: 1px dotted #bedfe7; padding: 8px; }

table.products td.first { text-align: left;}

table.products tr.head td { font-size: 11px; color: #2b2e39;}

table.total {  color: #000; font-size: 13px;}
table.total td { text-align: center;border-bottom: 1px dotted #dcdcdc; padding: 13px 15px 12px;}
table.total td.first { padding-left: 25px; text-align: left;}

</style>
<body>

<div style="width: 100%; padding-bottom: 0;">

<div style="width: 100%; padding-left: 20px;">
' . $site_logo . '
<h1 style="float: right; padding-top: 1px; text-align: right; margin-right: 46px; margin-bottom: -1px; font-size: 16px;"><span style="color: #666;">Pokladna</span>  ' . $month_formated . ' ' . $year . '</h1>
</div>

</div>

<div style="clear: both;"></div>

</div>

<hr style="color: #e7e7ef; margin-left: 75px; margin-right: 75px; margin-top: 0; width: 100%; margin-bottom: 8px;">

<div style="clear: both;"></div>

<div style="margin: 0 10px;">
 <table class="table products table-bordered table-striped datatable dataTable" style="width: 100%;">
        <thead>
        <tr>
            <th width="" class="text-center">ID</th>
            <th width="" class="text-center">Datum</th>
            <th width="" class="text-center">Variabilní symbol</th>
            <th width="" class="text-center">Popis</th>
            <th width="" class="text-center">Příjem</th>
            <th width="" class="text-center">Výdej</th>
            <th class="text-center">Konečný stav</th>
        </tr>
        </thead>

        <tbody role="alert" aria-live="polite" aria-relevant="all">
' . $list . '
     </tbody>
    </table>
</div>
</div>
</body>
';
//==============================================================
if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================
$mpdf->WriteHTML($html);

$mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/export/cashier/cashier_'.$location_id.'_' . $year . '_' . $month . '.pdf', 'F');

//Header('Location:https://www.wellnesstrade.cz/admin/data/export/cashier/cashier_'.$location_id.'_' . $year . '_' . $month . '.pdf');
exit;
