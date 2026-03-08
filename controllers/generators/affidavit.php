<?php

if($data['affidavit'] == 0){

    $affidavit = '<td>- </td><td>-</td>';

}elseif($data['affidavit'] == 1){

    $affidavit = '<td>a, rodinný dům o ploše do 350m2, nebo byt o ploše do 120m2 </td><td><strong>12%</strong> § 48 odst. 5 ZDPH</td>';

}elseif($data['affidavit'] == 2){

    $affidavit = '<td>b, nezisková organizace do 350m2 (nad 350m2 přiděleno DIČ např.SVJ) </td><td><strong>12%</strong> § 49 ZDPH</td>';

}elseif($data['affidavit'] == 3){

    $affidavit = '<td>c, fyzická osoba v pronájmu </td><td><strong>12%</strong> § 49 ZDPH</td>';

}elseif($data['affidavit'] == 4){

    $affidavit = '<td>d, právnická osoba, která není plátcem DPH – nemá přidělen DIČ </td><td><strong>21%</strong></td>';

}elseif($data['affidavit'] == 5){

    $affidavit = '<td>e, fyzická osoba (OSVČ), která není plátcem DPH – nemá přidělen DIČ </td><td><strong>21%</strong></td>';

}elseif($data['affidavit'] == 6){

    $affidavit = '<td>f, právnická osoba – součást stavby (součást bytové výstavby), plátce DPH-přiděleno DIČ </td><td><strong>0%</strong> režim PDP (CZ-CPA 43.22.11)</td>';

}elseif($data['affidavit'] == 7){

    $affidavit = '<td>g, fyzická osoba – součást stavby, plátce DPH-přiděleno DIČ </td><td><strong>0%</strong> režim PDP (CZ-CPA 43.22.11)</td>';

}elseif($data['affidavit'] == 8){

    $affidavit = '<td>h, firma-prodej do EU, vždy bez instalace - přiděleno DIČ (VAT) </td><td><strong>0%</strong> režim reverse charge</td>';

}elseif($data['affidavit'] == 9){

    $affidavit = '<td>ch, fyzická osoba-prodej do EU, vždy bez instalace - bez DIČ </td><td><strong>21%</strong></td>';

}elseif($data['affidavit'] == 10){

    $affidavit = '<td>i, právnická i fyzická osoba-prodej do 3.země (mimo ČR a EU)</td><td><strong>21%</strong></td>';

}elseif($data['affidavit'] == 11){

    $affidavit = '<td>j, ostatní</td><td><strong>21%</strong></td>';

}



$html_affidavit = '
<div style="width: 100%; margin-bottom: 18px; text-align:center;">
' . $site_logo . '
</div>

<div style="padding: 5px 60px 5px 60px;float: left; width: 100%;">

<br />
<p style="padding-bottom: 10px; font-size: 12px;">1. PRODÁVAJÍCÍ</p>
<p style="padding-bottom: 6px; font-size: 12px;"><strong>Wellness Trade, s.r.o.</strong></p>
<p style="padding-bottom: 8px;">se sídlem Vrbova 1277/32, 147 00 Praha, IČ: 29154871, DIČ: CZ29154871</p>
<p>společnost zapsaná v obchodním rejstříku vedeném Městským soudem v Praze oddíl C, vložka 203387.</p>
<p>zastoupena jednatelem Michaelem Bäumelem. E-mail: info@spahouse.cz. Bankovní spojení: 2000364217/2010</p>
<p style="padding-bottom: 16px;">(dále jen "<strong>prodávající</strong>") na straně jedné a</p>
<br />
<p style="padding-bottom: 10px; font-size: 12px;">2. KUPUJÍCÍ</p>
<p style="padding-bottom: 6px; font-size: 12px;"><strong>' . $name . '</strong></p>
<p>' . $address . '</p>
<p style="padding-bottom: 8px;">E-mail: <strong>' . $billing['billing_email'] . '</strong>, Tel.: <strong>' . number_format((float)$billing['billing_phone'], 0, ',', ' ') . '</strong></p>
<p>(dále jen "<strong>kupující</strong>" nebo "<strong>zákazník</strong>“) na straně druhé</p>
<p>v souladu s ustanoveními § 2079 a násl. zák. č. 89/2012 Sb., občanského zákoníku, uzavřeli tuto</p>
<br />
<h1 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 20px; margin-top: 60px; font-size: 14px; text-align: center;">
ČESTNÉ PROHLÁŠENÍ – PŘÍLOHA KUPNÍ SMLOUVY</h1>

<div style="width: 100%;">

<p style="padding-bottom: 30px; line-height: 34px;">
<strong>Kupující tímto čestně prohlašuje, že se instalace, dodávka předmětu kupní smlouvy uskuteční na pozemku, v prostoru: <br></strong>
<br /><br />
</p>

<table class="tablus2">
<thead>
<tr style="border-bottom: 1px solid #000;">
    <td><strong>ZAŘAZENÍ ZAKÁZKY dle zákona</strong></td>
    <td><strong>SAZBA DPH</strong></td>
</tr>
</thead>
<tbody>
<tr>
'.$affidavit.'
</tr>
</tbody>
</table>

<p style="padding-bottom: 40px; padding-top: 40px; font-weight: bold;">Čestné prohlášení je nedílnou součástí kupní smlouvy a bez něj není kupní smlouva platná.</p>

<p style="padding-bottom: 40px; font-weight: bold; text-decoration: underline;">Změna zařazení zakázky v sazbě DPH po podpisu KUPNÍ SMLOUVY není možná!</p>

</div>

</div>
<div style="clear: both;"></div>
';
