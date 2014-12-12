<?php
require ( "aw.php" );
set_time_limit(20);
error_reporting(0);
if( isset($_GET['err']) && $_GET["err"] == "1" ) {
	$err = "<div class='alert alert-warning'>請輸入一個正常的化學式</div>";
} //化學式錯誤

$url = $_SERVER['HTTPS'] ? 'https' : 'http' . '://'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];  //判斷目前網址，用於導向

if ( !isset ( $_GET["formula"] )) { //檢查參數是否已設定

?>

<html>
	<head>
		<title>化學式與莫耳數計算機</title>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
		<link href="./bootstrap.min.css" rel="stylesheet">
	</head>
	<body>
		<div class="container">
			<h1 class='text-center'>化學式與莫耳數計算機</h1>
			<?php echo $err; ?>
			<form method="get" id="form">
				<div class="input-group input-group-lg">
           			<span class="input-group-addon">化學式</span>
            		<input type="text" required class="form-control input-lg" id="formula" name="formula" placeholder="碳酸鈣輸入 CaCO3 ，注意大小寫，必填">
        		</div></br>
        		<div class="input-group input-group-lg">
           			<span class="input-group-addon">重量</span>
            		<input type="text" class="form-control input-lg" id="gram" name="gram" placeholder="重量，以克為單位，選填">
        		</div></br>
      			<input type="submit" value="送出" class="btn btn-primary">
    		</form>
		</div>
	</body>
</html>


<?php

} else {
	$formula = urldecode( $_GET["formula"] ) ;
	if ( !preg_match("/^[a-zA-Z0-9()]+$/" , $formula ) ) {
		header ( "location:$url?err=1" );
		exit();
	}  //含有英文與數字與括號以外的字元則判定為錯誤化學式

	if ( !preg_match("/[A-Z]/" , $formula ) ) {
		header ("location:$url?err=1");
		exit();
	}  //不含有大寫英文字母則判定為錯誤化學式

	//分割化學式開始
	$formulaarray = str_split ( $formula );//依字元分割
	$count = mb_strlen ( $formula , 'UTF-8' );//計算字數
	$matches = array();
	$bw = array();
	$i = 0 ; $dontcountme = false ;//初始化
	while ( $i < $count ) {
		$k = $formulaarray[$i];
		if ( $formulaarray[$i] == "(" ) {
			$brackets = true;
			$i++;
			$bn=0; //遇到括號時的處理
		} elseif ( $formulaarray[$i] == ")" ) {
			$brackets = false;
			$bs = $formulaarray[$i+1];//結束括號時取得括號係數
			$q = 0 ;
			while ( $q < $bn ) {
				$matches[] = $bw[$q];//把元素名稱放到陣列
				$matches[] = $bs;//把元素係數放到陣列
				$q++;
			}
			$i = $i + 2;
			$dontcountme = true;//避免之後作判斷時重複放入係數
		} elseif ( preg_match ( "/^[A-Z]$/", $formulaarray[$i] ) ) {
			//此項是元素
			if ( preg_match ( "/^[a-z]$/", $formulaarray[$i+1] ) ) {
				//兩個字元的元素
				$k .= $formulaarray[$i+1];
				if ( $brackets ) {
					$bw[$bn] = $k;//如果在括號裡就放到$bw陣列
					$bn++;
				} else {
					$matches[] = $k;//如果不在括號裡就放到$matches陣列
				}
				$i = $i + 2;//略過下一個字元，因下一個字元屬於此元素
			} else {
				//一個字元的元素
				if ( $brackets ) {
					$bw[$bn] = $k;//如果在括號裡就放到$bw陣列
					$bn++;
				} else {
					$matches[] = $k;//如果不在括號裡就放到$matches陣列
				}
				$i++;
			}
		} elseif ( preg_match("/^[0-9]$/", $formulaarray[$i] ) && $dontcountme === false ){
			//此項是係數
			if ( preg_match ( "/^[0-9]$/" , $formulaarray[$i+1] ) ) {
				$k = $formulaarray[$i+1] + ( $formulaarray[$i] * 10 ) ;//如果係數是二位數，就覆蓋掉原本的值
				$i++;
			}
			$matches[] = $k;
			$i++;
		} elseif ( preg_match( "/^[0-9]$/" , $formulaarray[$i] ) && $dontcountme === true ) {  //如果$dontcountme是true，代表這是括號後面的係數，剛剛已經計算過了，所以略過
			$dontcountme = false;//將$dontcountme設回false
		}
	}
	//分割化學式結束
	//檢查重複
	$forumla_check_unique = array_unique($matches);
	if ( count($matches) != count($forumla_check_unique) ){
	    $i = 0;
	    $counta = count($matches);
	    $formula_temp = array();
	    while( $i < $counta ){
	    	if( !preg_match( "/^[0-9]$/" , $matches[$i] ) ){ // 如果是參數就直接丟進陣列，否則進行處理
	    		if( !in_array( $matches[$i] , $formula_temp ) ){ //如果先前沒出現過就直接丟進陣列
	    			$formula_temp[] = $matches[$i];
	    		}else{
	    			$key = array_search($matches[$i], $formula_temp);
	    			if( preg_match( "/^[0-9]$/" , $matches[$i+1] ) ){
	    				$y = $matches[$i+1]; //如果下個值是參數就先丟進暫存
	    				$i++; // 避免參數被重複丟入陣列
	    			}else{
	    				$y = 1; //如果下個值不是參數就把參數設為 1 然後丟進暫存
	    			}
	    			if( preg_match( "/^[0-9]$/" , $formula_temp[$key+1] ) ){
	    				$formula_temp[$key+1] += $y; //如果處理過後的陣列的下個值是參數就加上去
	    			}else{
	    				array_splice($formula_temp,$key+1,0,$y+$formula_temp[$key+1]+1); //如果下個值不是參數就插入一個參數
	    			}
	    		}
	    	}else{
	    		$formula_temp[] = $matches[$i];
	    	}
	    	$i++;
	    }
	    $matches = $formula_temp;
	}

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $formula;?> - 化學式與莫耳數計算機</title>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
		<link href="./bootstrap.min.css" rel="stylesheet">
		<link href="./style.css" rel="stylesheet">
		<script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
		<script type="text/javascript">
		MathJax.Hub.Config({
			tex2jax: {
				inlineMath: [['$','$'], ['\\(','\\)']]
		  	}
		});
		</script>
	</head>
	<body>
		<div class="container">

<?php
	if( !isset( $_GET["gram"] ) || $_GET["gram"] == null){
		$gram = 100;
	}else{
		$gram = $_GET["gram"];
	}
	settype ( $gram , "double" ) ;

?>
	<h1 class='text-center' id="line-1"><?php echo $formula ?></h1>
	<h1 class='text-center' id="line-2">分析結果</h1>
	</br>

	<?php
		$color = array(0 => "TURQUOISE", 1 => "EMERALD",  2 => "PETER", 3 => "AMETHYST", 4 => "CARROT", 5 => "CONCRETE", 6 => "ALIZARIN" );
		$chosen = rand()%6;
	?>

	<div class="board <?php echo $color[$chosen]; ?>">
		<div class="inner">

			<section class="chem">
				<div class="cnt">$ 原式分解 $</div>
				<div class="explain">$ 分子量 $</div>
			</section>
<?php
	$f = count ( $matches ) ;
	$k = $j = $h = $i = 0;
	for ( $g = 0 ; $g < $f ; $g++) {
		if ( !preg_match ( "/^[0-9]*[1-9][0-9]*$/" , $matches[$g] ) ) { //先檢查此項是否為係數
			$a = $g + 1;//用於取得係數
			if ( $matches[$g] != NULL ) {
				if ( preg_match ( "/^[0-9]*[1-9][0-9]*$/" , $matches[$a] ) ) {
					//這裡是檢查元素後面是否有係數
					if ( $aw[$matches[$g]] != NULL ) {
?>
					<?php if ( $g != 0 ) { ?>
						<section class="chem">
							<div class="cnt">$ + $</div>
						</section>
					<?php } ?>
						<section class="chem">
							<h1>
								$<?php echo $matches[$g]; ?><?php echo $matches[$a];?> $
							</h1>
							<div class="explain">
								$ <?php echo $aw[$matches[$g]];  ?> \times <?php echo $matches[$a];  ?> $
							</div>
						</section>

<?php
					} else {
?>
						<section class="chem">
							<h1>
								$<?php echo $matches[$g]; ?> ? $
							</h1>
							<div class="explain">
								$ Unknown $
							</div>
						</section>
<?php
					}
					$b = $aw[$matches[$g]];//取得原子量
					$b = $b * $matches[$a];//將元素後面的係數乘以原子量
					$at[$k] = $b; //用於計算元素的重量
					$ak[$j] = $matches[$g];//用於計算元素的重量
				} else {
					if ( $aw[$matches[$g]] != NULL ) {
?>
				<?php if ( $g != 0 ) { ?>
						<section class="chem">
							<div class="cnt">$ + $</div>
						</section>
					<?php } ?>
						<section class="chem">
							<h1>
								$<?php echo $matches[$g]; ?> $
							</h1>
							<div class="explain">
								$ <?php echo $aw[$matches[$g]];  ?> \times 1 $
							</div>
						</section>
<?php
					} else {
?>
						<section class="chem">
							<h1>
								$<?php echo $matches[$g]; ?> (?) $
							</h1>
							<div class="explain">
								$ Unknown $
							</div>
						</section>
<?php
					}
					$b = $aw[$matches[$g]];//如果沒有係數就直接取得原子量
					$at[$k] = $b; //用於計算元素的重量
					$ak[$j] = $matches[$g];//用於計算元素的重量
					$h++;//如果沒有係數就讓係數和+1
				}
			}
			$i = $i + $b;//和之前的分子量相加取得目前分子量
			$k++;
			$j++;
		} else {
			$h = $h + $matches[$g];//取得係數和
		}
	}


?>



		<section class="chem ">
			<div class="cnt">$ = $</div>
		</section>

		<section class="chem">
			<h1>
				$<?php echo $formula ?>$
			</h1>
			<div class="explain">
				$<?php echo $i ?>$
			</div>
		</section>
		<div class="clearfix"></div>
		<p class="tip">$ ( 原子量 \times 係數 )$</p>
	</div>

</div>
<div class="details en<?php echo $color[$chosen]; ?>">
		<div class="inner">
			<div class="row">
				<div class="col-xs-6">
					<div class="row">
						<div class="col-xs-6">
							<p>$<?php echo $gram ?>$ 克的  $<?php echo $formula ?>$ 中含有</p>
						</div>
						<div class="col-xs-6">

							<?php
	$mole = $gram / $i;
	$w = count ( $at );
	for ( $q = 0 ; $q < $w; $q++ ) {
		$m = $gram / $i * $at[$q];
?>
		<p><span class="letter-enmax">$<?php echo $ak[$q] ?>$</span>
		$<?php echo number_format($m, 2) ?>$克</p>
<?php
	}
	//取得原子數量
	$h = $h * 6 * $mole;
?>

<p><span class="letter-enmax">$ 共 $</span> $<?php  echo number_format($mole, 2); ?> $ 莫耳</p>
						</div>
					</div>
				</div>
				<div class="col-xs-6">


	<p>原子數量
<?php
	if ( $gram == 0 ) {
		echo "0</p>";
	} else {
		$digits = ceil ( log ( $h ) / log ( 10 ) ) - 1;
		$str = "$" . number_format( round ( ( $h / pow ( 10 , $digits ) ) , 5 ), 3) . '\times10^{' . ( $digits + 23 ) . "}$ </p>";
		echo $str;
	}
	//取得單一分子或原子的重量
	$ii = $i / 6;
	echo "<p>單一分子 ";
	$digit = ceil ( log ( $ii ) / log ( 10 ) ) - 1;
	$str = "$" . number_format( round ( ( $ii / pow ( 10 , $digit ) ) , 5 ), 3) . '\times10^{' . ( $digit - 23 ) . "}$ 克</p>";
	echo $str;
?>
				</div>
			</div>
	</div>
</div>
<div class="clearfix "></div>
		<a class="btn btn-default" href="<?php echo $url; ?>">重設數據</a>
	</div>
	</body>
</html>
<?php } ?>