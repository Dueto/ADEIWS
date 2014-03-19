<?php


       // $host = 'localhost';
       // $port = 12345;         
        //$server = new clientSupport($host , $port );

require('adei.php');
$url = "http://localhost/adei-branch/adei/services/getdata.php?db_server=autogen&db_name=hourly&db_group=default&db_mask=0&experiment=1329043331-1344422531&window=2592000&format=csv";
$query = parse_url($url, PHP_URL_QUERY);

$req = new BASICRequest($query);
$qwe = new SOURCERequest($req->GetProps());

$request = new REQUEST($req->GetProps());
$asd = new CACHEDB($qwe);




$cachepostfix2 = $qwe -> GetQueryString();
$fdafda = $asd->GetItemList();
$asddd = $asd->GetTableName();
$cachepostfix = $asd->GetCachePostfix();
printf($cachepostfix2);
printf("\n");
printf($cachepostfix);
printf("\n");
printf($asddd);
printf("\n");
foreach($fdafda as $gggg)
{
	printf("\n");
	foreach($gggg as $reu)
	{
		printf($reu);printf("  ");
	}	
}



           
?>