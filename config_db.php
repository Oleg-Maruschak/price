<?
class conf{
	private $db;  
    private $user;  
    private $pass; 
    private $server;  
	private $links;
	private $tableName;
	public  $connect;
	public static $priceAP = [];
	private $urlLink;
	private $urlAvtopro;
	
	public function __construct ($db, $user, $pass, $server) {
		
		$this->db = $db;
		$this->user = $user;
		$this->pass = $pass;
		$this->server = $server;
		
				
	/* 	$this->links  =  mysql_connect($this->server,  $this->user,  $this->pass);
    if(!$this->links)  die("Не  могу  соединиться  с  MySQL");
    mysql_select_db($this->db)  or  die("Не  могу  открыть  $this->db:  ".mysql_error()); */
		$this->connect = mysqli_connect($this->server, $this->user, $this->pass, $this->db);
		if(!$this->connect)  die("Не  могу  соединиться  с  MySQL");
	}
	
	public function delete_article($table){
		
		mysqli_query($this->connect, "DELETE FROM $table WHERE id <> 0");
		
	}
	
	public static function loading_positions_ukrparts ($brand, $article, $price, $stok, $url, $nameCol){
		
		 mysqli_query($this->connect, "UPDATE `ukrparts` SET ".$nameCol." = '".$price."' WHERE `url` = 'https://ukrparts.com.ua".$url."'");
		 
		 if(mysqli_affected_rows($this->connect) === 1) return;
		 
		  if (mysqli_affected_rows($this->connect) === 0){
			  
			  mysql_query("INSERT INTO ukrparts (brand, article, stok, url, ".$nameCol.") VALUES ('".$brand."', '".$article."', '".$stok."', 'https://ukrparts.com.ua".$url."', '".$price."')");
		  }
		//mysql_query("INSERT INTO ukrparts (brand, article, price, stok, url) VALUES ('".$brand."', '".$article."', '".$price."', '".$stok."', 'https://ukrparts.com.ua".$url."')");
		
	}
	
		

	
	
	public function load_price_dok_in_stok ($table_for, $patern_stok, $patern_price, $paternDokArt, $paternBrand){
		
				//$price_dok = mysql_query("SELECT url, id FROM $table_for");
				$price_dok = mysqli_query($this->connect, "select `url`, `id` from $table_for where price = ''");
	
	     while ($url_price = mysqli_fetch_array($price_dok)){
			 
			 	//if ($url_price['price'] !== '') continue; 
			 $abc = fopen('1111.txt', 'a+');
			 fwrite($abc, $url_price['id']."\r\n");
			 fclose($abc);
			 
				
			  $chCountList = curl_init();
		 
						curl_setopt($chCountList, CURLOPT_URL,$url_price['url']);
						curl_setopt($chCountList, CURLOPT_HEADER, 0);
						curl_setopt($chCountList, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCountList, CURLOPT_RETURNTRANSFER,1);
						curl_setopt($chCountList, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
						curl_setopt($chCountList, CURLOPT_SSL_VERIFYPEER, 0);
				
					$returnedList = curl_exec($chCountList);
						curl_close ($chCountList);
		
		preg_match($patern_stok, $returnedList, $stokValue);
		
		preg_match($patern_price, $returnedList, $price_value);
		preg_match_all($paternDokArt, $returnedList, $article_value);
		preg_match($paternBrand, $returnedList, $brand_value);
			
			if($stokValue[1] != 'Есть в наличии') continue;
			
		$articles = implode($article_value[1]);
				
	mysqli_query($this->connect, "UPDATE ".$table_for." SET price = '".$price_value[1]."', 
	                                        stok = 'Есть на складе', 
											brand = '".$brand_value[1]."', 
											art = '".$articles."' WHERE id = '".$url_price['id']."'");
	sleep(0.1);
		}
		
		}
		
			
		
		public function load_category($table, $url){
			
		mysqli_query($this->connect, "INSERT INTO $table (url) VALUES ('".$url."')");
		
}

    
	 

	 public function load_price_avtoto ($table_from, $table_for, $paternPrice, $paternStok, $paternBrand, $paternCountPage, $paternNotCount, $paternArticle, $paternCartUrl, $nameColumnAvtoto, $proxy, $login){
		
				$price_avtoto = mysqli_query($this->connect, "SELECT url, id FROM $table_for");
	
	     while ($url_price = mysqli_fetch_array($price_avtoto)){
			
			mysqli_query($this->connect, "UPDATE $table_for SET `page` = '1' where `id` = '".$url_price['id']."'");
			//$countContinue = 0;
			 	
		 $chCountList = curl_init();
		 
						curl_setopt($chCountList, CURLOPT_URL,$url_price['url']);
						curl_setopt($chCountList, CURLOPT_HEADER, 0);
						curl_setopt($chCountList, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCountList, CURLOPT_RETURNTRANSFER,1);
						//curl_setopt($chCountList, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
						curl_setopt($chCountList, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($chCountList, CURLOPT_PROXY, $proxy);
                        curl_setopt($chCountList, CURLOPT_PROXYUSERPWD, $login);
				
					$returnedList = curl_exec($chCountList);
						curl_close ($chCountList);
			 
			preg_match($paternCountPage, $returnedList, $stokPage);
			$brandPars = explode("/",$url_price['url']);
			$brandValue = strtoupper($brandPars[count($brandPars)-2]);
			
			if ($stokPage[1] <= 40){
				
				if(preg_match($paternNotCount,$returnedList)) continue;
			
			preg_match_all($paternStok, $returnedList, $stokValue);
			preg_match_all($paternArticle, $returnedList, $articleValue);
			preg_match_all($paternPrice, $returnedList, $priceValue);
			preg_match_all($paternCartUrl, $returnedList, $urlValue);
			
			$arrayPriceAvtoto = array('article'=>$articleValue[1],
									  'price'=>$priceValue[1],
									  'stok'=>$stokValue[1],
									  'url'=>$urlValue[1]);
			
			for($n = 0; $n<count($arrayPriceAvtoto['article']);$n++){
				
			if($arrayPriceAvtoto['stok'][$n] !== 'есть на складе') {
				//$countContinue++;
				continue;
			}
			
			mysqli_query($this->connect, "UPDATE $table_from SET `".$nameColumnAvtoto."` = '".$arrayPriceAvtoto['price'][$n]."' WHERE `url` = '".$arrayPriceAvtoto['url'][$n]."'");
			
			if(mysqli_affected_rows($this->connect) === 1) continue;
			
			else if (mysqli_affected_rows($this->connect) === 0){
			
			mysqli_query($this->connect, "INSERT INTO $table_from (article, brand, price, url, stok) VALUES ('".$arrayPriceAvtoto['article'][$n]."',
			                                                                                '".$brandValue."',
																							'".$arrayPriceAvtoto['price'][$n]."',
																							'".$arrayPriceAvtoto['url'][$n]."',
																							'".$arrayPriceAvtoto['stok'][$n]."')");
			}
			}
				
				
				
			}
			else {	 
			 
		 for ($i = 0; $i <= $stokPage[1]; $i+=40){
			//if($countContinue >= 40) break; 
			$urlPage = $url_price['url'].'?offset='.$i;
			
		 $chBrand = curl_init();
		 
						curl_setopt($chBrand, CURLOPT_URL,$urlPage);
						curl_setopt($chBrand, CURLOPT_HEADER, 0);
						curl_setopt($chBrand, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chBrand, CURLOPT_RETURNTRANSFER,1);
						//curl_setopt($chBrand, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
						curl_setopt($chBrand, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($chBrand, CURLOPT_PROXY, $proxy);
                        curl_setopt($chBrand, CURLOPT_PROXYUSERPWD, $login);
				
					$returnedBrand = curl_exec($chBrand);
						curl_close ($chBrand);
						
			if(preg_match($paternNotCount,$returnedBrand)) break;
			
			preg_match_all($paternStok, $returnedBrand, $stokValue);
			preg_match_all($paternArticle, $returnedBrand, $articleValue);
			preg_match_all($paternPrice, $returnedBrand, $priceValue);
			preg_match_all($paternCartUrl, $returnedBrand, $urlValue);
			
			$arrayPriceAvtoto = array('article'=>$articleValue[1],
									  'price'=>$priceValue[1],
									  'stok'=>$stokValue[1],
									  'url'=>$urlValue[1]);
			for($n = 0; $n<count($arrayPriceAvtoto['article']);$n++){
				
			if($arrayPriceAvtoto['stok'][$n] !== 'есть на складе') {
				//$countContinue++;
				continue;
			}
			
			mysqli_query($this->connect, "UPDATE $table_from SET `".$nameColumnAvtoto."` = '".$arrayPriceAvtoto['price'][$n]."' WHERE `url` = '".$arrayPriceAvtoto['url'][$n]."'");
			
			if(mysqli_affected_rows($this->connect) === 1) continue;
			
			else if (mysqli_affected_rows($this->connect) === 0){
			
		
			mysqli_query($this->connect, "INSERT INTO $table_from (article, brand, price, url, stok) VALUES ('".$arrayPriceAvtoto['article'][$n]."',
			                                                                                '".$brandValue."',
																							'".$arrayPriceAvtoto['price'][$n]."',
																							'".$arrayPriceAvtoto['url'][$n]."',
																							'".$arrayPriceAvtoto['stok'][$n]."')");
			}
			}
			
		 }
			 
			}
		
		 }
		}
		
		public function replaseArticle ($table){
			
			$price_avtoto = mysqli_query("SELECT `id`, `article`, `brand` FROM `".$table."`");
		 while ($url_price = mysqli_fetch_array($price_avtoto)){

           mysqli_query($this->connect, "UPDATE `".$table."` SET `article` = '".str_ireplace($url_price['brand'],'',$url_price['article'])."' WHERE id = '".$url_price['id']."'");			 
		
		 }
			
				
		}
		
	public function priceAutoklad($tableCategory, $table, $paternStok, $paternBrand, $paternArticle, $paternPrice, $paternCartUrl, $paternPage, $nameColumn, $proxy, $userAgent, $idListCategory, $cookieName, $rubrikName, $nameArticle, $nameLink, $robot){
		
		$stop = false;	 
			$randUser = $userAgent[array_rand($userAgent)];
			$randProxy = array_rand($proxy);
	     mysqli_query($this->connect, "UPDATE $tableCategory SET `page`= '1' WHERE `url` = '".$nameLink."'");
		  
		        
		 $chCountList = curl_init();
		               
						curl_setopt($chCountList, CURLOPT_URL, $nameLink);
						curl_setopt($chCountList, CURLOPT_HEADER, 0);
						curl_setopt($chCountList, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCountList, CURLOPT_RETURNTRANSFER,1);
						//curl_setopt($chCountList, CURLOPT_USERAGENT, $randUser);
						curl_setopt($chCountList, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
						curl_setopt($chCountList, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						curl_setopt($chCountList, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						curl_setopt($chCountList, CURLOPT_SSL_VERIFYPEER, 0);
						//curl_setopt($chCountList, CURLOPT_PROXY, $proxy[$randProxy]['ip']);
                        //curl_setopt($chCountList, CURLOPT_PROXYUSERPWD, $proxy[$randProxy]['pass']);
				
					$returnedList = curl_exec($chCountList);
						curl_close ($chCountList);
                     if(preg_match($robot, $returnedList)){
						 $stop = true;
						 return $stop;
						 exit;
						 } 
                        preg_match_all($paternPrice, $returnedList, $priceValue);
						preg_match_all($paternStok, $returnedList, $stokValue);
                        preg_match_all($nameArticle, $returnedList, $name);
						preg_match_all($paternBrand, $returnedList, $brandValue);
						preg_match_all($paternArticle, $returnedList, $articleValue);
						preg_match_all($paternCartUrl, $returnedList, $urlValue);
						preg_match_all($paternPage, $returnedList, $pageLast);
						preg_match($rubrikName, $returnedList, $rubrik);
						//preg_match_all($day, $returnedList, $dayDelivery);
						
			$countList = end($pageLast[1]);

    $arrayPriceUkrparts = array('price'=>$priceValue[1],
					            'url'=>$urlValue[1],
						        'stok'=>$stokValue[1],
						        'article'=>$articleValue[1],
						        'brand'=>$brandValue[1],
						        'name'=>$name[1],
								'rubrik'=>$rubrik[1]);
						   
	for ($j = 0; $j<count($arrayPriceUkrparts['price']); $j++){
		

			  mysqli_query($this->connect, "INSERT INTO $table(`article`, `brand`, `url`, `stok` ,$nameColumn, `category`, `name`) VALUES ('".$arrayPriceUkrparts['article'][$j]."',
			                                                                                                                              '".$arrayPriceUkrparts['brand'][$j]."',
																																		  'https://www.autoklad.ua".$arrayPriceUkrparts['url'][$j]."',
																																		  '".preg_replace('/<\/span>\s+<\/a>\s+<strong>/is',' ',preg_replace('/<\/strong>\s+<\/div>/is','',$arrayPriceUkrparts['stok'][$j]))."',
																																		  '".$arrayPriceUkrparts['price'][$j]."',
																																		  '".$arrayPriceUkrparts['rubrik']."',
																																		  '".$arrayPriceUkrparts['name'][$j]."')");
				
				
		  }
		  sleep(rand(0.3,1.2));

	  
  }
	
	
	
	public function updatePrice ($tableUpdate, $paternPriceUpdate, $nameColumnUpdate){
		
		//mysqli_query($this->connect,"ALTER TABLE ".$tableUpdate." ADD ".$nameColumnUpdate." VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
		
		//$priceUpdate = mysqli_query($this->connect,"SELECT `url`, `id` FROM `".$tableUpdate."` where `".$nameColumnUpdate."` = ''");
		$priceUpdate = mysqli_query($this->connect,"SELECT url, id FROM ".$tableUpdate." where `id` > '24195'");
		
		
		 while ($update_price = mysqli_fetch_array($priceUpdate)){
			 
			  if($tableUpdate == 'dok'){
				 $articleFile = fopen('dok.txt', 'a+');
			     fwrite($articleFile, $update_price['id']."\r\n");
			     fclose($articleFile);
			 }else if ($tableUpdate == 'avtoto'){
				 $articleFile = fopen('avtoto.txt', 'a+');
			     fwrite($articleFile, $update_price['id']."\r\n");
			     fclose($articleFile);
			 }else if ($tableUpdate == 'autoklad'){
				 $articleFile = fopen('autoklad.txt', 'a+');
			     fwrite($articleFile, $update_price['id']."\r\n");
			     fclose($articleFile);
				 //sleep(rand(2,5));
				 sleep(1);
			 }else if ($tableUpdate == 'ukrparts'){
				 $articleFile = fopen('ukrparts.txt', 'a+');
			     fwrite($articleFile, $update_price['id']."\r\n");
			     fclose($articleFile);
				 sleep(0.15);
			 }
			 
			  $chPrice = curl_init();
		 
						curl_setopt($chPrice, CURLOPT_URL,$update_price['url']);
						curl_setopt($chPrice, CURLOPT_HEADER, 0);
						curl_setopt($chPrice, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chPrice, CURLOPT_RETURNTRANSFER,1);
						curl_setopt($chPrice, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
						curl_setopt($chPrice, CURLOPT_SSL_VERIFYPEER, 0);
				
					$returnedPrice = curl_exec($chPrice);
						curl_close ($chPrice);
			 
			 
			 preg_match($paternPriceUpdate, $returnedPrice, $priceNew);
			 
			mysqli_query($this->connect,"UPDATE ".$tableUpdate." SET `price_18112019` = '".preg_replace('/[<span class="thousands"><\/span><span class="rest_amount">]/is','',$priceNew[1])."' WHERE id = '".$update_price['id']."'"); 
			
		 }
		
	}
	
	public static function update_id ($table_from, $table_for, $col_update){
		$idIndex = mysql_query("SELECT `id`, `article`, `brand` FROM ".$table_from."");
		
		
		 while ($updateId = mysql_fetch_array($idIndex, MYSQL_ASSOC)){
			 
			 mysql_query("UPDATE ".$table_for." SET ".$col_update." = '".$updateId['id']."' WHERE `article` = '".$updateId['article']."' AND `brand` = '".$updateId['brand']."'"); 
			 
		 }
		
		
		
	}
	
	public function loadArticleForAvtopartner (){
		
		$arrayBrand = [];
		$brandNameAp = mysqli_query($this->connect, "SELECT `id`, `brand`, `url_name` FROM brands");
		while ($brandValueAp = mysqli_fetch_array($brandNameAp)){
			$arrayBrand[$brandValueAp['id']] = array('brand' => $brandValueAp['brand'], 'url' => $brandValueAp['url_name'] );
			
		}
		
		$table_name = mysqli_query($this->connect, "SELECT table_name FROM information_schema.tables where table_schema='monitoring'");
		while ($updateId = mysqli_fetch_array($table_name)){
			
			if(!preg_match('/^[a-zA-Z]+$/is',$updateId['table_name']) || $updateId['table_name'] == 'avtopartner' || $updateId['table_name'] == 'brands') continue;
			//echo $updateId['table_name']."</br>";
			//$selectArticle = mysqli_query($this->connect, "SELECT `id`, `article`, `brand`, `id_brand_ap` FROM ".$updateId['table_name']."");
			$selectArticle = mysqli_query($this->connect, "SELECT `id`, `article`, `brand`, `id_brand_ap` FROM ".$updateId['table_name']." WHERE `price_31082020` = '' and `price_07092020` = '' and `price_14092020` = '' and `price_21092020` <> '' ");
			while ($updateArticle = mysqli_fetch_array($selectArticle)){
				$artNew = preg_replace('/[-\s,._\/\\\\#\&]+/is','',$updateArticle['article']);
				
				$artBrand = preg_replace('/(&amp;)/is','',$arrayBrand[$updateArticle['id_brand_ap']]['brand']);
				$urlValue = "https://avtopartner.com.ua/price/".$arrayBrand[$updateArticle['id_brand_ap']]['url']."_".$artNew."";
				
					if($updateArticle['id_brand_ap'] != 0){
						
							$artBrand = preg_replace('/(&amp;)/is','',$arrayBrand[$updateArticle['id_brand_ap']]['brand']);
							$urlValue = "https://avtopartner.com.ua/price/".$arrayBrand[$updateArticle['id_brand_ap']]['url']."_".$artNew."";
					}else {
						$artBrand2 = preg_replace('/(&amp;)/is','',$updateArticle['brand']);
						$artBrand = preg_replace('/[-\s,._\/\\\\#\&]+/is', '', $artBrand2);
						$urlValue = "https://avtopartner.com.ua/price/".$artBrand."_".$artNew."";
					}
				
			     $idTable = "id_".$updateId['table_name']."";
				$articleFile = fopen('brand'.$updateId['table_name'].'.txt', 'a+');
			     fwrite($articleFile, $urlValue."\r\n");
			     fclose($articleFile);  
				
				mysqli_query($this->connect, "UPDATE `avtopartner` SET ".$idTable." = '".$updateArticle['id']."' WHERE `url` = '".$urlValue."'");
				if(mysqli_affected_rows($this->connect) === 1) continue;
				else if (mysqli_affected_rows($this->connect) === 0){
			    mysqli_query($this->connect, "INSERT INTO `avtopartner` (".$idTable.", article, brand, url) VALUES ('".$updateArticle['id']."', '".$artNew."','".$artBrand."','".$urlValue."') "); 
				}
				
				
				
			}
			
		}
		
		
	}
	
	public function  upadateIdBrandAp ($table = false){
		
		if(!$table){
		$table = mysqli_query($this->connect, "SELECT table_name FROM information_schema.tables where table_schema='monitoring'");
		while ($brandId = mysqli_fetch_array($table)){
			if(!preg_match('/^[a-zA-Z]+$/is',$brandId['table_name']) || $brandId['table_name'] == 'avtopartner' || $brandId['table_name'] == 'brands' ) continue;
			$selectBrand = mysqli_query($this->connect, "SELECT `id`, `brand` FROM `brands`");
			
			  while ($bran = mysqli_fetch_array($selectBrand)){
		  
				mysqli_query($this->connect, "UPDATE `".$brandId['table_name']."` SET `id_brand_ap`= '".$bran['id']."' WHERE `brand` = '".$bran['brand']."'");
					
					}
			
			$selectBrands = mysqli_query($this->connect, "SELECT `id`, `brand` FROM `brands`");
			
			while ($brandsId = mysqli_fetch_array($selectBrands)){
			$selectBrandTable = mysqli_query($this->connect, "SELECT `id`, `brand` FROM `".$brandId['table_name']."` WHERE `id_brand_ap` = 0");
			  while ($brandTable = mysqli_fetch_array($selectBrandTable)){
				  $brandIn = preg_replace('/\W+/is', '', $brandsId['brand']);
				  $brandOut = preg_replace('/\W+/is', '', $brandTable['brand']);
               if (strncasecmp($brandIn, $brandOut , min(strlen($brandIn),strlen($brandOut))) != 0) continue;
			   mysqli_query($this->connect, "UPDATE `".$brandId['table_name']."` SET `id_brand_ap`= '".$brandsId['id']."' WHERE `id` = '".$brandTable['id']."'");
				  
				  
			  }
				
		}

			
			
			
		}
		} else {
			
					
			/* $selectBrands = mysqli_query($this->connect, "SELECT `id`, `brand` FROM `brands`");
			
			while ($brandsId = mysqli_fetch_array($selectBrands)){
			$selectBrandTable = mysqli_query($this->connect, "SELECT `id`, `brand` FROM `".$table."` WHERE `id_brand_ap` = 0");
			  while ($brandTable = mysqli_fetch_array($selectBrandTable)){
				  $brandIn = preg_replace('/\W+/is', '', $brandsId['brand']);
				  $brandOut = preg_replace('/\W+/is', '', $brandTable['brand']);
				   $articleFile = fopen('brandDok.txt', 'a+');
			     fwrite($articleFile, $brandTable['id']." | ".$brandIn." | ".$brandOut."\r\n");
			     fclose($articleFile);  
               if (strncasecmp($brandIn, $brandOut , min(strlen($brandIn),strlen($brandOut))) != 0) continue;
			   mysqli_query($this->connect, "UPDATE `".$table."` SET `id_brand_ap`= '".$brandsId['id']."' WHERE `id` = '".$brandTable['id']."'"); */
				
				//////////////////////////////////////////////////
			$selectBrands = mysqli_query($this->connect, "SELECT `id`, `brand` FROM `brands`");
				while ($brandsId = mysqli_fetch_array($selectBrands)){
				$selectBrandTable = mysqli_query($this->connect, "SELECT `brand` FROM `".$table."` WHERE `id_brand_ap` = '0' and `price_31082020` = '' and `price_07092020` = '' and `price_14092020` = '' and `price_21092020` <> '' GROUP BY `brand`");	
				while ($brandTable = mysqli_fetch_array($selectBrandTable)){
				 $brandIn = preg_replace('/\W+/is', '', $brandsId['brand']);
				  $brandOut = preg_replace('/\W+/is', '', $brandTable['brand']);
				  
				   $articleFile = fopen('brand'.$table.'.txt', 'a+');
			     fwrite($articleFile, $brandIn." | ".$brandOut."\r\n");
			     fclose($articleFile);  
				if($brandIn == $brandOut){
					mysqli_query($this->connect, "UPDATE `".$table."` SET `id_brand_ap`= '".$brandsId['id']."' WHERE `brand` = '".$brandTable['brand']."' and `price_31082020` = '' and `price_07092020` = '' and `price_14092020` = '' and `price_21092020` <> '' ");
					
				}else if (strncasecmp($brandIn, $brandOut , min(strlen($brandIn),strlen($brandOut))) !== 0) continue;				
                 				
				 mysqli_query($this->connect, "UPDATE `".$table."` SET `id_brand_ap`= '".$brandsId['id']."' WHERE `brand` = '".$brandTable['brand']."' and `price_31082020` = '' and `price_07092020` = '' and `price_14092020` = '' and `price_21092020` <> ''  ");
				}	
					
				}
				  
			  }
				
		
			
			}
	
	 public function getExist ($table, $tableCategory, $price, $stok, $brand, $count_page, $article, $url, $nameColumn, $proxy, $userAgent, $countListCategory, $cookieName, $categoryName, $moreOffers, $idArticle, $nameArticle, $countStok, $on_off, $nameLink) {
		 
		 //$category_exist = mysqli_query($this->connect, "SELECT url, id FROM $tableCategory WHERE `proxy-ip` = $countListCategory AND `page` = '0'");
		
		//while ($url_category = mysqli_fetch_array($category_exist)){
		/*  $FileExis = fopen($_SERVER['DOCUMENT_ROOT'].'/logs/exist_category.txt', 'a+');
			     fwrite($FileExis, $url_category['url']."\r\n");
			     fclose($FileExis);	 */
		$notScan = false;

        /* $randUser = $userAgent[array_rand($userAgent)];
	    $randProxy = array_rand($proxy);		 */
		 
		
		 
		 $countPageList = 0;
		 
		  $chCount = curl_init();
		               
						curl_setopt($chCount, CURLOPT_URL, $nameLink);
						//curl_setopt($chCount, CURLOPT_URL, 'https://exist.ua/kolodki-tormoznye/');
						curl_setopt($chCount, CURLOPT_HEADER, 0);
						curl_setopt($chCount, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCount, CURLOPT_RETURNTRANSFER,1);
						curl_setopt($chCount, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
						//curl_setopt($chCount, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						
						
						/* $FileExis = fopen($_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName, 'a+');
			            fwrite($FileExis, 'exist.ua	FALSE	/	FALSE	0	catalog_order_by	delivery_time'."\r\n");
			            fwrite($FileExis, 'exist.ua	FALSE	/	FALSE	0	catalog_page_size	50'."\r\n");
			            fclose($FileExis); */
						
						curl_setopt($chCount, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						curl_setopt($chCount, CURLOPT_SSL_VERIFYPEER, 0);
						//curl_setopt($chCount, CURLOPT_PROXY, $proxy[$randProxy]['ip']);
                        //curl_setopt($chCount, CURLOPT_PROXYUSERPWD, $proxy[$randProxy]['pass']);
				
					$returnedListCount = curl_exec($chCount);
						curl_close ($chCount);
		 preg_match_all($count_page, $returnedListCount, $countP);
		 preg_match($categoryName, $returnedListCount, $nameCategory);
		 $countPageList = ceil($countP[1][0]/50);
		 $name = str_replace('<!-- -->','',$nameCategory[1]); 
		
		 
		 for ($i=1;$i<=$countPageList;$i++){
			 
	     mysqli_query($this->connect, "UPDATE $tableCategory SET `page`= $i WHERE `url` = '".$nameLink."'");
		 
         if ($notScan == true) break;
		 
		/*  if ($i % 5 == 0) {
		
	     $randUser = $userAgent[array_rand($userAgent)];
	     $randProxy = array_rand($proxy);	
		
	} */
		 
		         /* $FileExist = fopen($_SERVER['DOCUMENT_ROOT'].'/logs/exist.txt', 'a+');
			     fwrite($FileExist, $url_category['url']."?page=$i"."\r\n");
			     fclose($FileExist); */
		 
		 $chCountList = curl_init();
		               
						curl_setopt($chCountList, CURLOPT_URL, $nameLink."?page=$i");
						curl_setopt($chCountList, CURLOPT_HEADER, 0);
						curl_setopt($chCountList, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCountList, CURLOPT_RETURNTRANSFER,1);
						curl_setopt($chCountList, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
						//curl_setopt($chCountList, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						
					/* 	$FileExis1 = fopen($_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName, 'a+');
			            fwrite($FileExis1, 'exist.ua	FALSE	/	FALSE	0	catalog_order_by	delivery_time'."\r\n");
			            fwrite($FileExis1, 'exist.ua	FALSE	/	FALSE	0	catalog_page_size	50'."\r\n");
			            fclose($FileExis1); */
						
						curl_setopt($chCountList, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						curl_setopt($chCountList, CURLOPT_SSL_VERIFYPEER, 0);
						//curl_setopt($chCountList, CURLOPT_PROXY, $proxy[$randProxy]['ip']);
                        //curl_setopt($chCountList, CURLOPT_PROXYUSERPWD, $proxy[$randProxy]['pass']);
				
					$returnedList = curl_exec($chCountList);
						curl_close ($chCountList);

                        preg_match_all($stok, $returnedList, $stokValue);
						preg_match_all($brand, $returnedList, $brandValue);
						preg_match_all($article, $returnedList, $articleValue);
						preg_match_all($price, $returnedList, $priceValue);
						preg_match_all($url, $returnedList, $urlValue);
						preg_match_all($moreOffers, $returnedList, $offers);
						preg_match_all($idArticle, $returnedList, $idArt);
						preg_match_all($nameArticle, $returnedList, $nameArt);
						preg_match_all($countStok, $returnedList, $stokK);
						

    $arrayPriceExist = array('price'=>$priceValue[1],
					       'url'=>$urlValue[1],
						   'stok'=>$stokValue[1],
						   'article'=>$articleValue[1],
						   'brand'=>$brandValue[1],
						   'nameCat'=>$name,
						   'moreOffers'=>$offers[1],
						   'idArticle'=>$idArt[1],
						   'name'=>$nameArt[1],
						   'countStok'=>$stokK[1]);
						   
	for ($j = 0; $j<count($arrayPriceExist['price']); $j++){
		
			  if ($arrayPriceExist['price'][$j] == "" || $arrayPriceExist['price'][$j] == Null || $arrayPriceExist['price'][$j] == "Продано" ) {
				$notScan = true;  
				break;  
			  }
			  
			
				 
				 /* mysqli_query($this->connect, "UPDATE $table SET $nameColumn = '".$arrayPriceExist['price'][$j]."' WHERE `url` = 'https://exist.ua/".$arrayPriceExist['url'][$j]."'");
				
				if(mysqli_affected_rows($this->connect) === 1) continue;

				 else if (mysqli_affected_rows($this->connect) === 0){ */
              if($arrayPriceExist['moreOffers'][$j] > 0 && $on_off == true){
				  $this->getMoreOffersExist($arrayPriceExist['moreOffers'][$j], $table, $nameColumn, $arrayPriceExist['idArticle'][$j], $proxy[$randProxy]['ip'], $proxy[$randProxy]['pass'], $randUser, $arrayPriceExist['nameCat'], $arrayPriceExist['name'][$j], $arrayPriceExist['article'][$j], $arrayPriceExist['brand'][$j], 'https://exist.ua/'.$arrayPriceExist['url'][$j]);
			  }else{
			  mysqli_query($this->connect, "INSERT INTO $table(`first`, `name-category`, `name`, `id-article` ,`article`, `brand`, `url`, `stok`, `count`, `moreOffers` ,$nameColumn, `article_priority`) VALUES ('1','".$arrayPriceExist['nameCat']."',
			                                                                                                                              '".$arrayPriceExist['name'][$j]."',
			                                                                                                                              '".$arrayPriceExist['idArticle'][$j]."',
			                                                                                                                              '".$arrayPriceExist['article'][$j]."',
			                                                                                                                              '".$arrayPriceExist['brand'][$j]."',
																																		  'https://exist.ua/".$arrayPriceExist['url'][$j]."',
																																		  '".$arrayPriceExist['stok'][$j]."',
																																		  '".$arrayPriceExist['countStok'][$j]."',
																																		  '".$arrayPriceExist['moreOffers'][$j]."',
																																		  '".$arrayPriceExist['price'][$j]."',
																																		  '".$arrayPriceExist['article'][$j]."-1')");
			  }
				 //}
				 
				 
		  }				

		sleep(rand(0.3,1.2)); 
	 }
	 //}
	 }
	 
	private function getMoreOffersExist($moreOffers, $table, $nameColumn, $idArticle, $proxy, $pass, $user, $nameCat, $name, $article, $brand, $url){
		$id = "https://exist.ua/api/v1/catalogue/search-by-number/?product_id=$idArticle";
		
		$chCount = curl_init();
		               
						curl_setopt($chCount, CURLOPT_URL, $id);
						curl_setopt($chCount, CURLOPT_HEADER, 0);
						curl_setopt($chCount, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCount, CURLOPT_RETURNTRANSFER,1);
						curl_setopt($chCount, CURLOPT_USERAGENT, $user);				
						curl_setopt($chCount, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie/cookiesExist.txt');
						curl_setopt($chCount, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($chCount, CURLOPT_PROXY, $proxy);
                        curl_setopt($chCount, CURLOPT_PROXYUSERPWD, $pass);
				
					$returnedListId = curl_exec($chCount);
						curl_close ($chCount);
               $urlDecod = json_decode($returnedListId, true);
			   

			   $priceCount = $urlDecod['data']['results']['product']['other_price_count'];
			   $oe = $urlDecod['data']['results']['product']['list'][0]['is_oe'];
			   $idPrice = array();
			   $priority = 0;
			   for($i=0;$i<count($urlDecod['data']['results']['product']['list']);$i++){
				  $priority = $i+1;				  
				 $idPrice[] = $urlDecod['data']['results']['product']['list'][$i]['price_id'];
				 
				   if($urlDecod['data']['results']['product']['list'][$i]['delivery']['date'] == 'today'){
					  $stok = 'СЕГОДНЯ'; 
				   }
				   else if($urlDecod['data']['results']['product']['list'][$i]['delivery']['date'] == 'tomorrow'){
					 $stok = 'ЗАВТРА';  
				   }else if($urlDecod['data']['results']['product']['list'][$i]['delivery']['date'] == null ){
                     $stok = 'В наличии';					   
				   }else {
					 $stok = $urlDecod['data']['results']['product']['list'][$i]['delivery']['date'];
				   }
				   if($i == 0){
				   mysqli_query($this->connect, "INSERT INTO $table(`first`, `name-category`, `name`, `id-article` ,`article`, `brand`, `url`, `stok`, `count`, `moreOffers` ,$nameColumn, `article_priority`) VALUES ('1',
				                                                                                                                          '".$nameCat."',
			                                                                                                                              '".$name."',
			                                                                                                                              '".$idArticle."',
			                                                                                                                              '".$article."',
			                                                                                                                              '".$brand."',
																																		  '".$url."',
																																		  '".$stok."',
																																		  '".$urlDecod['data']['results']['product']['list'][$i]['quantity']."',
																																		  '".$moreOffers."',
																																		  '".$urlDecod['data']['results']['product']['list'][$i]['price']."',
																																		  '".$article.'-'.$priority."')");
				   }else{
					   mysqli_query($this->connect, "INSERT INTO $table(`name-category`, `name`, `id-article` ,`article`, `brand`, `url`, `stok`, `count`, `moreOffers` ,$nameColumn, `article_priority`) VALUES (
				                                                                                                                          '".$nameCat."',
			                                                                                                                              '".$name."',
			                                                                                                                              '".$idArticle."',
			                                                                                                                              '".$article."',
			                                                                                                                              '".$brand."',
																																		  '".$url."',
																																		  '".$stok."',
																																		  '".$urlDecod['data']['results']['product']['list'][$i]['quantity']."',
																																		  '".$moreOffers."',
																																		  '".$urlDecod['data']['results']['product']['list'][$i]['price']."',
																																		  '".$article.'-'.$priority."')");
				   } 
			   }
			   
				if($priceCount > 0){
					
					$this->getMoreProduktExist($priority, $idPrice, $oe, $idArticle, $article, $user, $proxy, $pass, $table, $nameColumn, $nameCat, $name, $brand, $url, $moreOffers);
					
				}				
		
		
		
	} 
	
	private function getMoreProduktExist($priority, $idPrice, $oe, $idArticle, $article, $user, $proxy, $pass, $table, $nameColumn, $nameCat, $name, $brand, $urlArticle, $priceCount){
		$url = 'https://exist.ua/api/v1/product/get-more-offers/?exclude=';
		$url2='';
		foreach($idPrice as $value){
			
			$url2 = $url2.$value.'%2C';
			
		}
		if($oe == true){
		$url3 = "&is_original=true";
		}else{
		$url3 = "&is_original=false";	
		}
		$url4 = '&is_product=true';
		$url5 = "&prag_id=$idArticle";
		$url6 = '&ware_num='.preg_replace('/\s/is','%',$article);
		
		$urlEnd = $url.$url2.$url3.$url4.$url5.$url6;
		$urlFor = preg_replace('/%2C&is/is','&is',$urlEnd);
		
		//mysqli_query($this->connect, "INSERT INTO `exist_new`(`url`) value ('".$urlFor."')");
		$chCou = curl_init();
		               
						curl_setopt($chCou, CURLOPT_URL, $urlFor);
						curl_setopt($chCou, CURLOPT_HEADER, 0);
						curl_setopt($chCou, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCou, CURLOPT_RETURNTRANSFER,1);
						curl_setopt($chCou, CURLOPT_USERAGENT, $user);				
						curl_setopt($chCou, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie/cookiesExist.txt');
						curl_setopt($chCou, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($chCou, CURLOPT_PROXY, $proxy);
                        curl_setopt($chCou, CURLOPT_PROXYUSERPWD, $pass);
		
		$returnedLis = curl_exec($chCou);
						curl_close ($chCou);
               $urlDecode = json_decode($returnedLis, true);
			   
			   
			   
			   for($i=0;$i<count($urlDecode['data']);$i++){
				   
				 $nextPriority = $priority + ($i+1);
				 
				   if($urlDecode['data'][$i]['delivery']['date'] == 'today'){
					  $stok = 'СЕГОДНЯ'; 
				   }
				   else if($urlDecode['data'][$i]['delivery']['date'] == 'tomorrow'){
					 $stok = 'ЗАВТРА';  
				   }else if($urlDecode['data'][$i]['delivery']['date'] == null){
					 $stok = 'В наличии';  
				   }else {
					 $stok = $urlDecode['data'][$i]['delivery']['date'];
				   }
			
			mysqli_query($this->connect, "INSERT INTO $table(`name-category`, `name`, `id-article` ,`article`, `brand`, `url`, `stok`, `count`, `moreOffers` ,$nameColumn, `article_priority`) VALUES ('".$nameCat."',
			                                                                                                                              '".$name."',
			                                                                                                                              '".$idArticle."',
			                                                                                                                              '".$article."',
			                                                                                                                              '".$brand."',
																																		  '".$urlArticle."',
																																		  '".$stok."',
																																		  '".$urlDecode['data'][$i]['quantity']."',
																																		  '".$priceCount."',
																																		  '".$urlDecode['data'][$i]['price']."',
																																		  '".$article.'-'.$nextPriority."')");
			
			   }   
		
	}
	 
	 
	public function getUkrparts($table, $tableCategory, $price, $stok, $nonStok, $brand, $count_page, $lastPage, $article, $url, $nameColumn, $proxy, $userAgent, $countListCategory, $cookieName, $nameRubrik, $nameLink){
		 
		 //$category_ukrparts = mysqli_query($this->connect, "SELECT url, id FROM $tableCategory WHERE `proxy-ip` = $countListCategory AND `page` = '0'");
		 
		 //while ($url_category = mysqli_fetch_array($category_ukrparts)){
        $stop = false;
		$notScan = false;
       /*  if($countListCategory == 1 ){
			$randUser = $_SERVER['HTTP_USER_AGENT'];
		}else{
        $randUser = $userAgent[array_rand($userAgent)];
		} */
		//$randUser = $userAgent[array_rand($userAgent)];
	    //$randProxy = array_rand($proxy);		
		 
		 for ($i=1;$i<=$count_page;$i++){
			 
	      if ($notScan == true) break;
			 
	     mysqli_query($this->connect, "UPDATE $tableCategory SET `page`= $i WHERE `url` = '".$nameLink."'");
		 

		 
		/*  if ($i % 3 == 0) {
		
	     $randUser = $userAgent[array_rand($userAgent)];
	     $randProxy = array_rand($proxy);	
		
	} */
		 
		        
		 $chCountList = curl_init();
		               
						curl_setopt($chCountList, CURLOPT_URL, $nameLink."?page=$i");
						curl_setopt($chCountList, CURLOPT_HEADER, 0);
						curl_setopt($chCountList, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCountList, CURLOPT_RETURNTRANSFER,1);
						//curl_setopt($chCountList, CURLOPT_USERAGENT, $randUser);
						curl_setopt($chCountList, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
						//curl_setopt($chCountList, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						curl_setopt($chCountList, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						curl_setopt($chCountList, CURLOPT_SSL_VERIFYPEER, 0);
						if ($countListCategory > 1){
						curl_setopt($chCountList, CURLOPT_PROXY, $proxy[$countListCategory]['ip']);
                        curl_setopt($chCountList, CURLOPT_PROXYUSERPWD, $proxy[$countListCategory]['pass']);
						}
						
					$returnedList = curl_exec($chCountList);
					$http_code = curl_getinfo($chCountList, CURLINFO_HTTP_CODE);
						curl_close ($chCountList);
				if ($http_code == 403){
					$stop = true;
					return $stop;
					break;
					exit;
					
					
				} 
                        if (!preg_match_all($price, $returnedList, $priceValue)) break;
						preg_match_all($stok, $returnedList, $stokValue);
                        //preg_match_all($nonStok, $returnedList, $nonStokValue);
						preg_match_all($brand, $returnedList, $brandValue);
						preg_match_all($article, $returnedList, $articleValue);
						preg_match_all($url, $returnedList, $urlValue);
						preg_match_all($lastPage, $returnedList, $pageLast);
						preg_match_all($nameRubrik, $returnedList, $rubrik);

    $arrayPriceUkrparts = array('price'=>str_replace(array('<span class="thousands">',
                                                  '</span>',
												  '<span class="rest_amount">'),'',$priceValue[1]),
					            'url'=>$urlValue[1],
						        'stok'=>$stokValue[1],
						        'article'=>$articleValue[1],
						        'brand'=>$brandValue[1],
						        'lastPage'=>end($pageLast[1]),
								'rubrik'=>$rubrik[1][3]);
						   
	for ($j = 0; $j<count($arrayPriceUkrparts['price']); $j++){
		
			  	/* $numRows = mysqli_query($this->connect, "SELECT 1 FROM $table WHERE `url` = 'https://ukrparts.com.ua".$arrayPriceUkrparts['url'][$j]."'. limit 1");	 
				$RowsNum = mysqli_num_rows($numRows);
				if ($RowsNum == 1) continue;
				else { */
					
				// mysqli_query($this->connect, "UPDATE $table SET $nameColumn = '".$arrayPriceUkrparts['price'][$j]."' WHERE `url` = 'https://ukrparts.com.ua".$arrayPriceUkrparts['url'][$j]."'");
				
				//if(mysqli_affected_rows($this->connect) === 1) continue;

				 //else if (mysqli_affected_rows($this->connect) === 0){

			  mysqli_query($this->connect, "INSERT INTO $table(`article`, `brand`, `url`, `stok`, $nameColumn, `rubrik`, `name`) VALUES ('".$arrayPriceUkrparts['article'][$j]."',
			                                                                                                                              '".$arrayPriceUkrparts['brand'][$j]."',
																																		  'https://ukrparts.com.ua".$arrayPriceUkrparts['url'][$j]."',
																																		  '".$arrayPriceUkrparts['stok'][$j]."',
																																		  '".$arrayPriceUkrparts['price'][$j]."',
																																		  '".$arrayPriceUkrparts['rubrik']."',
																																		  '".$arrayPriceUkrparts['rubrik'].' '.strtoupper($arrayPriceUkrparts['brand'][$j]).' '.strtoupper($arrayPriceUkrparts['article'][$j])."')");
				 //}
				//}
		  }				
    if ($i == $arrayPriceUkrparts['lastPage'] || $arrayPriceUkrparts['lastPage'] == null ){
		
		$notScan = true;
		break;
		
	}
		sleep(rand(0.3,1)); 
	 }
	 //}
		 
		 
		 
		 
		 
	 }
	 
	 
	 
	 public function getCountLink ($table,$proxy){
		$urlLink;
		if($table == 'dok_article'){
		$Link = mysqli_query($this->connect, "SELECT `id`, `article`, `brand` FROM `".$table."` WHERE `proxy-fakt` = '0' LIMIT 1");
		/* 
		if (!$Link) {
          printf("Error: %s\n", mysqli_error($this->connect));
          exit();
        }
          */
        while ($url_category = mysqli_fetch_array($Link)){
			mysqli_query($this->connect, "UPDATE `".$table."` SET `proxy-fakt` = '1' WHERE `id` = '".$url_category['id']."'");
			$urlLink = array('article'=>$url_category['article'], 'brand'=>$url_category['brand']);
			
		}
		return $urlLink;
		}else{
		$Link = mysqli_query($this->connect, "SELECT `url`, `id` FROM `".$table."` WHERE `page` = '0' AND `proxy-ip` = '".$proxy."' group by rand(`id`) LIMIT 1");
		
		while ($url_category = mysqli_fetch_array($Link)){
			mysqli_query($this->connect, "UPDATE $table SET `page` = '5000' WHERE `id` = '".$url_category['id']."'");
			$urlLink = $url_category['url'];
		}
		return $urlLink;
		}
	}
	
	public function getUrlAvtopro ($urlJson, $paternUrl, $userAgent, $array_proxy, $cookieName){
		$urlAvtopro;
		
		//for($i=0;$i<=count($urlJson);$i++){
			
			//$randUser = $userAgent[array_rand($userAgent)];
	        //$randProxy = array_rand($array_proxy);
		
			$data = array('Query' => $urlJson['article'],'RegionId'=> 1,'SuggestionType' => 'Regular');	

					$chCountList = curl_init();
						curl_setopt($chCountList, CURLOPT_URL,"https://avto.pro/api/v1/search/query");
						curl_setopt($chCountList, CURLOPT_HEADER, 0);
						curl_setopt($chCountList, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCountList, CURLOPT_RETURNTRANSFER,1);
						//curl_setopt($chCountList, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						//curl_setopt($chCountList, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						curl_setopt($chCountList, CURLOPT_CUSTOMREQUEST, "PUT");
                        curl_setopt($chCountList, CURLOPT_POSTFIELDS,http_build_query($data));
						//curl_setopt($chCountList, CURLOPT_USERAGENT, $randUser);
						curl_setopt($chCountList, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
						curl_setopt($chCountList, CURLOPT_SSL_VERIFYPEER, 0);
						//curl_setopt($chCountList, CURLOPT_PROXY, $array_proxy[$randProxy]['ip']);
                        //curl_setopt($chCountList, CURLOPT_PROXYUSERPWD, $array_proxy[$randProxy]['pass']);
				
					$returnedList = curl_exec($chCountList);
						curl_close ($chCountList);
			$urlDecod = json_decode($returnedList, true);

			for($j=0;$j<count($urlDecod['Suggestions']);$j++){
				
				if(strcasecmp(preg_replace('/[\s\/]+/is', '', $urlJson['brand']), preg_replace('/[\s\/]+/is', '', $urlDecod['Suggestions'][$j]['FoundPart']['Part']['Brand']['Name']))==0){
					
					preg_match($paternUrl, $urlDecod['Suggestions'][$j]['Uri'], $url);
					
				$urlAvtopro = array('article'=>$urlDecod['Suggestions'][$j]['FoundPart']['Part']['FullNr'],
				                  'articleSearch'=>$urlDecod['Suggestions'][$j]['FoundPart']['Part']['ShortNr'],
				                  'brand'=>$urlDecod['Suggestions'][$j]['FoundPart']['Part']['Brand']['Name'],
								  'category'=>$urlDecod['Suggestions'][$j]['FoundPart']['Description'],
								  'url'=>'https://avto.pro/'.$url[1].'/',
								  'articleDok'=>$urlJson['article'],
								  'brandDok'=>$urlJson['brand']);	
					
				}else{
					continue;
				}
				
				
			}	
	
		//}
		
		return $urlAvtopro;
	}
	
	public function getAvtopro ($urlArticle, $cookieName, $userAgent, $array_proxy, $searchArticle, $price, $stokSity, $stokDayAvto, $providerAvto, $nameArticle, $table, $nameColumn, $prox_ip){
		//for($i=0; $i<count($urlArticle);$i++){
		    //$randUser = $userAgent[array_rand($userAgent)];
	        //$randProxy = array_rand($array_proxy);

					$chCountList = curl_init();
						curl_setopt($chCountList, CURLOPT_URL, $urlArticle['url']);
						curl_setopt($chCountList, CURLOPT_HEADER, 0);
						curl_setopt($chCountList, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($chCountList, CURLOPT_RETURNTRANSFER,1);
						//curl_setopt($chCountList, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						
						//$cookiesFiles = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						//$cookieRead = fopen($_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName, "r+");
						//fwrite($cookieRead, str_replace('Paging%22%3a50','Paging%22%3A350',str_replace('analogs-and-originals','analogs-vs-originals',$cookiesFiles)));
						//fclose($cookieRead);
						
						//curl_setopt($chCountList, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie/'.$cookieName);
						//curl_setopt($chCountList, CURLOPT_USERAGENT, $randUser);
						curl_setopt($chCountList, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
						curl_setopt($chCountList, CURLOPT_SSL_VERIFYPEER, 0);
						//curl_setopt($chCountList, CURLOPT_PROXY, $array_proxy[$randProxy]['ip']);
                        //curl_setopt($chCountList, CURLOPT_PROXYUSERPWD, $array_proxy[$randProxy]['pass']);
				
					$returnedList = curl_exec($chCountList);
						curl_close ($chCountList);
		
		               preg_match_all ($searchArticle, $returnedList, $article);			
			           preg_match_all ($price, $returnedList, $price);			
			           preg_match_all ($stokSity, $returnedList, $stokSity);			
			           preg_match_all ($stokDayAvto, $returnedList, $stokDay);
					   preg_match_all ($providerAvto, $returnedList, $provider);
					   preg_match_all ($nameArticle, $returnedList, $name);
					   
			$avtopro = array('article'=>$article[1],
			                 'price'=>$price[1],
							 'stokSity'=>$stokSity[1],
							 'stokDay'=>$stokDay[1],
							 'provider'=>$provider[1],
							 'name'=>$name[1]);
							 
			
             //print_r($avtopro);
			  
		for($j=0;$j<count($avtopro['article']);$j++){
			
			if(strcasecmp($avtopro['article'][$j], $urlArticle['articleSearch'])!=0){
				echo "Прервали"."</br>";
				break;
			} 
			
			if($avtopro['stokDay'][$j] == -1) $st = 'В наличии';
			else if ($avtopro['stokDay'][$j] == 0) $st = 'Сегодня';
			else if ($avtopro['stokDay'][$j] == 1) $st = 'Завтра';
			else if ($avtopro['stokDay'][$j] < 5)  $st = $avtopro['stokDay'][$j].' дня';
			else  $st = $avtopro['stokDay'][$j].' дней';
			
			$provi = urldecode($avtopro['provider'][$j]);
			//$prov = urldecode($avtopro['provider'][$j]);
			//echo $urlArticle[$i]['category']."|".$avtopro['name'][$j]."|".$urlArticle[$i]['brand']."|".$urlArticle[$i]['article']."|".$st.' '.$avtopro['stokSity'][$j]."|".$avtopro['stokDay'][$j]."|".$avtopro['provider'][$j]."|".$urlArticle[$i]['url']."|".$avtopro['price'][$j]."</br>";
			//mysqli_query($this->connect, "INSERT INTO `".$table."`(`rubrik`, `name`, `brand`, `article`, `stok`, `stok_day`, `provider`, `url`, `".$nameColumn."`) VALUES ('1','1','1','1','1','1','1','1','1')");
			//mysqli_query($this->connect,"ALTER TABLE `".$table."` ADD `".$provi."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
			
				mysqli_query($this->connect, "INSERT INTO `".$table."`(`rubrik`, `name`, `brand`, `article`, `stok`, `stok_day`, `provider`, `url`, `".$nameColumn."`, `proxy-ip`, `article_priority`, `line`) VALUES (
			'".$urlArticle['category']."',
			'".$avtopro['name'][$j]."',
			'".$urlArticle['brandDok']."',
			'".$urlArticle['articleDok']."',
			'".$st.' '.$avtopro['stokSity'][$j]."',
			'".$avtopro['stokDay'][$j]."',
			'".$provi."',
			'".$urlArticle['url']."',
			'".str_replace('.',',',$avtopro['price'][$j])."',
			'".$prox_ip."',
			'".$urlArticle['articleDok'].'-'.($j+1)."',
			'".($j+1)."')");	
			
			//mysqli_query($this->connect, "UPDATE `".$table."` SET `".$provi."` = '".$pric."', `article_priority` = '".$urlArticle['articleDok'].'-'.($j+1)."' WHERE `url` = '".$urlArticle['url']."'");	
			
		}			   
		
					   
		//}
		sleep(rand(0.3,1.5));
	}
	
	/* public  function test ($price,$stok,$url_value){
	           PRICE_AP = ($url_value => array('price'=>$price, 'stok'=>$stok));
	}
	
	public  function count_url (){
		print_r(PRICE_AP);
		
	} */
	
	public function getNameProvider ($table, $dir_export){
		
		$Link = mysqli_query($this->connect, "SELECT `provider` FROM `".$table."` GROUP BY `provider`");
		
        while ($url_category = mysqli_fetch_array($Link)){
			echo($dir_export);
			echo($url_category['provider']."</br>");
			
			$this->exportPrice($table, $dir_export, $url_category['provider']);
			
		}
		
	}
	
	public function exportPrice ($table, $dir_export, $name_provider){
		echo($dir_export.$name_provider.'.csv'."</br>");
	
	  
	  mysqli_query($this->connect, "SELECT * FROM `".$table."` where `provider` = '".$name_provider."' INTO OUTFILE '".iconv('UTF-8', 'CP1251', $dir_export.$name_provider).'.csv'."' FIELDS TERMINATED BY ';' ENCLOSED BY '\"' LINES TERMINATED BY '\n'");
	  
    }
	
	public function exits (){
		
		mysqli_close($this->connect);
		
	}
	
	
	
	
}
	 
	
	
	
		
		
		
	
  
	 
	  
  
	
	




?>