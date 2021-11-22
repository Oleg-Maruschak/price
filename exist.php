<?
//ignore_user_abort(true);
$parametrsExist = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/ini/config.ini', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/constant.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/class/config_db.php');

$timeUpdateStartExist = time();
$nameColumnExist = "price_".strval(date('dmY',$timeUpdateStartExist));
$idListCategory = $_GET["name"];
$cookieName = 'cookiesExist.txt';

$load = new conf ($parametrsExist['config_db']['db'],$parametrsExist['config_db']['user'],$parametrsExist['config_db']['pass'],$parametrsExist['config_db']['server']);
if($idListCategory == 1){
mysqli_query($load->connect,"ALTER TABLE `".$parametrsExist['exist']['table']."` ADD `".$nameColumnExist."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
}
for($i=0;$i<=386;$i++){
	
$nameLink = $load->getCountLink($parametrsExist['exist']['tableCategory'], $idListCategory);
if($nameLink == null) break;
$load->getExist($parametrsExist['exist']['table'],
                $parametrsExist['exist']['tableCategory'],
			    $parametrsExist['exist']['price'],
				$parametrsExist['exist']['stok'],
				$parametrsExist['exist']['brand'],
				$parametrsExist['exist']['count_page'],
				$parametrsExist['exist']['article'],
				$parametrsExist['exist']['url'],
				$nameColumnExist,
				$array_proxy,
				$userAgent,
				$idListCategory,
				$cookieName,
				$parametrsExist['exist']['kategoryName'],
				$parametrsExist['exist']['moreOffers'],
				$parametrsExist['exist']['idArticle'],
				$parametrsExist['exist']['productName'],
				$parametrsExist['exist']['countStok'],
				ON_OFF,
				$nameLink);
}								
$load -> exits();
$endTime = time();
echo date('H:i:s', ($endTime - $timeUpdateStartExist)-10800);



?>
