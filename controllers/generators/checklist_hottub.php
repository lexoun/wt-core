<?php

if (!isset($_REQUEST['html'])) {$_REQUEST['html'] = '';}

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";

$id = $_REQUEST['id'];

$getclientquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(realtodate, "%d. %m. %Y") as realtodateformat FROM demands WHERE id="' . $id . '"') or die($mysqli->error);
$getclient = mysqli_fetch_assoc($getclientquery);

$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $getclient['shipping_id'] . '" WHERE b.id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
$address = mysqli_fetch_assoc($address_query);

$location = calendar_location($address);

$searchquery = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.demand_id = '" . $getclient['id'] . "'") or die($mysqli->error);
$warehouse = mysqli_fetch_array($searchquery);

$paramsquery = $mysqli->query("SELECT value FROM demands_specs_bridge WHERE specs_id = '5' AND client_id = '" . $getclient['id'] . "'") or die($mysqli->error);
$params = mysqli_fetch_array($paramsquery);

$site_logo = '<img src="../../assets/images/spahouse-shop.png" width="180" style="margin: 20px 60px 0px; ">';

$mpdf = new Mpdf\Mpdf();

//==============================================================

$html = '
<style>
@page{
    sheet-size: 210mm 297mm;
    margin: 0;
    background-color: #FFFFFF;
}

body, div, p {
	font-family: "Roboto", Helvetica;
	font-size: 11px;
	color: #000000;
}

strong { font-family: Roboto-Medium, Helvetica; font-weight: 500;}

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
	font-size: 9pt;
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
	image-rendering: -moz-crisp-edges;		/* Firefox */
	image-rendering: -o-crisp-edges;		/* Opera */
	image-rendering: -webkit-optimize-contrast;/* Webkit (non-standard naming) */
	image-rendering: crisp-edges;
	-ms-interpolation-mode: nearest-neighbor;	/* IE (non-standard property) */
}


table {
    border-collapse: collapse;
}

table, th, td {
    border: 0;
}
table.bank-table td { padding: 3px 0; width: 150px;}


table.products {  color: #000; font-size: 13px;}

table.products td { text-align: center;border-bottom: 1px dotted #bedfe7;}


table.products td.first { text-align: left;}

table.products tr.head td { font-size: 11px; color: #2b2e39;}



table.total {  color: #000; font-size: 13px;}

table.total td { text-align: center;border-bottom: 1px dotted #dcdcdc; padding: 10px 15px 9px;}





table.tablus tr { background-color: #e2f3f7; border: 1px solid #d4eef4;}

table.total td.first { padding-left: 25px; text-align: left;}

table.tablus thead tr {background-color: #cbe6ed; border-bottom: 1px solid #e9f6f9;}
table.tablus thead td {border-right: 1px solid #e9f6f9;}
table.tablus td{ padding: 6px 15px 5px; text-align: center; border-right: 1px solid #d4eef4;}

table.tablus tfoot tr { background-color: #e2f3f7; border-top: 5px solid #c7e3ea;}

table.tablus tfoot td {font-weight: bold; font-family: Roboto-Light, Helvetica; font-size: 13px; padding: 10px 15px 10px;}




table.tablus2 tr { border-bottom: 1px dotted #e1f3f7;}

table.tablus2 td{ padding: 3px 2px 2px; border-right: 1px dotted #FFF;}

table.smallfont tr { border-bottom: 1px dotted #e1f3f7;}

table.smallfont td{font-size: 11px;padding: 5px 2px 4px; border-right: 1px dotted #FFF;}

</style>
<body>

<div style="width: 100%; margin-top: 0; text-align:center;">
' . $site_logo . '
</div>

<div style="margin-top: 10px; padding: 4px 60px 5px 60px;float: left; display:block; width: 100%;">

<div style="width: 50%; float: left;">
<p style="padding-bottom: 6px; font-size: 16px;"><strong>Dodavatel:</strong></p>
<p style="padding-bottom: 2px; font-size: 13px;"><strong>Wellness Trade, s. r. o.</p>
<p>Vrbova 1277/32, 147 00 Praha</p>
<p style="padding-bottom: 8px;">IČO: 291 54 871, DIČ CZ29154871</p>
<p style="padding-bottom: 12px;"><strong>Typ vířivé vany: ' . ucfirst($getclient['product']) . ' ' . ucfirst($params['value']) . '</strong></p>
<p><u><strong>Čas příjezdu:</strong></u></p>

</div>



<div style="width: 50%; float: left;">
<p style="padding-bottom: 6px; font-size: 16px;">Odběratel:<strong></strong></p>
<p style="padding-bottom: 2px; font-size: 13px;"><strong>' . $getclient['user_name'] . '</p>
<p>' . $location . '</p>
<p style="padding-bottom: 8px;">'.phone_prefix($address['billing_phone_prefix']).'  ' . number_format($address['billing_phone'], 0, ',', ' ') . '</p>
<p style="padding-bottom: 12px;"><strong>Číslo: <span style="color: #c9242a;">' . $warehouse['serial_number'] . '</span></strong></p>
<p><u><strong>Čas odjezdu:</strong></u></p>
</div>

<table class="tablus2"  style="margin: 14px 0 0; width: 100%;">
<tr>
<td width="24%">Použití bazénu</td>
<td width="10%"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td width="16%"><img src="../../assets/images/camera.png" width="24"></td>
<td width="24%">Průchod / umístění vířivky</td>
<td width="10%"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td width="16%"><img src="../../assets/images/camera.png" width="24"></td>

</tr>
<tr>
<td colspan="3"><strong>Kontrola případného poškození místa před realizací:</strong></td>
</tr>
<tr>
<td>Bez zjevného poškození</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
<td>Poškození</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
</tr>

<tr>
<td colspan="6" style="padding: 10px 2px;">Zjištěná poškození: .............................................................................................................................................................................</td>
</tr>

<tr>
<td>Kontrola rovnosti terénu</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;ANO</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;NE</td>
<td>Sklon terénu:</td>
<td colspan="2">....................... m2</td>
</tr>


<tr>
<td>Zapojení řídící jednotky</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
<td>Napinování</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
<td></td>
</tr>

<tr>
<td>Pojistka 1/8 - 125mA</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
<td>Kontrola zapojení 230/400</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td></td>
</tr>

<tr>
<td>Typ jističe</td>
<td>.......................</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
<td>Typ chrániče</td>
<td>.......................</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
</tr>

<tr>
<td>Typ/průměr kabelu</td>
<td>........................</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
<td>Zemnící kabel</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;ANO</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;NE</td>
</tr>

<tr>
<td style="padding: 10px 2px;">Sled fází - kontrola</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td></td>
<td>Umístění kabelu</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
</tr>


<tr>
<td>Protažení kabelu</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
<td>Servisní plomba</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td><img src="../../assets/images/camera.png" width="24"></td>
</tr>



</table>

<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: -10px; font-size: 14px; float: left; margin-top: 10px;">Kontrola vany</h2>



<table class="tablus2 smallfont"  style="margin: 14px 0 0; width: 100%;">
<tr>
<td width="24%">Pumpa 1</td>
<td width="14%"><span style="padding: 5px 30px; width: 20px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td width="14%">Pumpa 2</td>
<td width="14%"><span style="padding: 5px 30px; width: 20px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td width="14%">Microsilk</td>
<td width="14%"><span style="padding: 5px 30px; width: 20px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
</tr>

<tr>
<td>Blower</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td>In.Clear</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td>Vypouštěcí ventil</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
</tr>

<tr>
<td>Blower - tlakový ventil</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td>Ekozone</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td></td>
<td></td>
</tr>

<tr>
<td>Cirkulační pumpa</td>
<td colspan="2"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;již instalována</td>
<td colspan="3"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;instalována při realizaci</td>
</tr>

<tr>
<td style="padding: 8px 2px;">Umístění a typ cirkulační pumpy:</td>
<td colspan="5">....................................................................................................................................</td>
</tr>


</table>


<table class="tablus2"  style="margin: 14px 0 0; width: 100%;">
<tr>
<td width="24%">Kontrola + dotažení trysek</td>
<td width="14%"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td width="26%">Proplach/čištění vany</td>
<td width="14%"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td width="14%">Napuštění vany</td>
<td width="14%"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
</tr>



<tr>
<td>Odvzdušnění vany</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td>Kontrola podtlakového ventilu</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td></td>
<td></td>
</tr>


</table>









<h2 style=" font-family: Roboto-Light, Helvetica; font-weight: 600; margin-bottom: -5px; font-size: 12px; float: left; margin-top: 10px;">Kontrola ventilů regulace:</h2>

<table class="tablus2"  style="margin: 14px 0 0; width: 100%;">
<tr>
<td width="20%">Vodopád</td>
<td width="20%"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td width="20%">Fontána</td>
<td width="20%"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td width="24%">Diverter</td>
<td width="16%"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
</tr>

<tr>
<td>Kontrola LED</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td>Kontrola audio</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td>Upevnění držáku termokrytu</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
</tr>

<tr>
<td>Úprava vody:</td>
<td>Tvrdost: ............. <img src="../../assets/images/camera.png" width="24"></td>
<td>pH: ............. <img src="../../assets/images/camera.png" width="24"></td>
<td>OXY: ............. <img src="../../assets/images/camera.png" width="24"></td>
<td>Chlor:  ............. <img src="../../assets/images/camera.png" width="24"></td>
<td></td>
</tr>

<tr>
<td>Kontrola opláštění + lišt</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;OK</td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>

</table>

<p style="margin-top: 14px; line-height: 28px;">Doplňující informace ..............................................................................................................................................................................<br />.................................................................................................................................................................................................................</p>

<p style="margin-top: 14px; line-height: 28px;">Dodělávky / resty ..................................................................................................................................................................................<br />................................................................................................................................................................................................................</p>


<table class="tablus2"  style="margin: 14px 0 0; width: 100%;">
<tr>
<td width="50%">Kompletní fotodokumentace pro prezentaci na webu: </td>
<td width="10%"><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;PROVEDENO</td>
<td width="10%"></td>
<td width="30%"></td>
</tr>
<tr>
<td>+ kontrola kvality fotografií na místě: </td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;PROVEDENO</td>
<td></td>
<td></td>
</tr>
<tr>
<td>Revizní zpráva: </td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;ANO</td>
<td><span style="padding: 10px 30px; width: 40px; float: left; border: 2px solid #555;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;NE</td>
<td></td>
</tr>
</table>

<br>

<div style="width: 50%; float: left;">
<p>Podpis technika:</p>
</div>

</div>
<div style="clear: both;"></div>

';
//==============================================================
if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================

$mpdf->WriteHTML($html);

$mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/checklists/Checklist_hottub_' . $id . '.pdf', 'F');

Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=" . $id . "&success=generate_checklist");
exit;
