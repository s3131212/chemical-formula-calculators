<?php
require("aw.php");
ini_set('display_errors',0);
if(isset($_GET['err'])&&$_GET["err"]=="1"){ $err="<div class='alert alert-warning'>請輸入一個正常的化學式</div>"; } //化學式錯誤
$url = $_SERVER['HTTPS'] ? 'https' : 'http' . '://'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];  //判斷目前網址，用於導向
if(!isset($_GET["gram"])||$_GET["gram"]==NULL||!isset($_GET["formula"])||$_GET["gram"]==NULL){ //檢查參數是否已設定 ?>
<html>
	<head>
		<title>化學式與莫耳數計算機</title>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
	</head>
	<body>
		<div class="container">	
			<h1 class='text-center'>化學式與莫耳數計算機</h1>
			<?php echo $err; ?>
			<form method="get" id="form">
				<div class="input-group input-group-lg">
           			<span class="input-group-addon">化學式</span>
            		<input type="text" required class="form-control input-lg" id="formula" name="formula" placeholder="碳酸鈣輸入 CaCO3 ，注意大小寫">
        		</div></br>
        		<div class="input-group input-group-lg">
           			<span class="input-group-addon">重量</span>
            		<input type="text" required class="form-control input-lg" id="gram" name="gram" placeholder="重量，以克為單位">
        		</div></br>
      			<input type="submit" value="送出" class="btn btn-primary">
    		</form>
		</div>
	</body>
</html>

<?php }else{
$formula=urldecode($_GET["formula"]);
if(!preg_match("/^[a-zA-Z0-9()]+$/",$formula)){header("location:$url?err=1");exit();}  //含有英文與數字與括號以外的字元則判定為錯誤化學式
if(!preg_match("/[A-Z]/",$formula)){header("location:$url?err=1");exit();}   //不含有大寫英文字母則判定為錯誤化學式
//分割化學式開始
$formulaarray = str_split($formula);//依字元分割
$count=mb_strlen($formula,'UTF-8');//計算字數
$matches=array();
$bw=array();
$i=0; $dontcountme=false;//初始化
while($i<$count){
	$k=$formulaarray[$i];
	if($formulaarray[$i]=="("){
		$brackets=true; $i++; $bn=0;//遇到括號時的處理
	}elseif($formulaarray[$i]==")"){
		$brackets=false;
		$bs=$formulaarray[$i+1];//結束括號時取得括號係數
		$q=0;
		while($q<$bn){
			$matches[]=$bw[$q];//把元素名稱放到陣列
			$matches[]=$bs;//把元素係數放到陣列
			$q++;
		}
		$i=$i+2;
		$dontcountme=true;//避免之後作判斷時重複放入係數
	}elseif (preg_match("/^[A-Z]$/",$formulaarray[$i])){
		//此項是元素
		if (preg_match("/^[a-z]$/",$formulaarray[$i+1])){
			//兩個字元的元素
			$k.=$formulaarray[$i+1];
			if($brackets){
				$bw[$bn]=$k;//如果在括號裡就放到$bw陣列
				$bn++;
			}else{
				$matches[]=$k;//如果不在括號裡就放到$matches陣列
			}
			$i=$i+2;//略過下一個字元，因下一個字元屬於此元素			
		}else{
			//一個字元的元素
			if($brackets){
				$bw[$bn]=$k;//如果在括號裡就放到$bw陣列
				$bn++;
			}else{
				$matches[]=$k;//如果不在括號裡就放到$matches陣列
			}
			$i++;
		}
	}elseif (preg_match("/^[0-9]$/",$formulaarray[$i])&&$dontcountme===false){
		//此項是係數
		if(preg_match("/^[0-9]$/",$formulaarray[$i+1])){
			$k=$formulaarray[$i+1]+($formulaarray[$i]*10);//如果係數是二位數，就覆蓋掉原本的值
			$i++;
		}
		$matches[]=$k;
		$i++;
	}elseif(preg_match("/^[0-9]$/",$formulaarray[$i])&&$dontcountme===true){  //如果$dontcountme是true，代表這是括號後面的係數，剛剛已經計算過了，所以略過
		$dontcountme=false;//將$dontcountme設回false
	}
}
//分割化學式結束
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $formula;?> - 化學式與莫耳數計算機</title>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
	</head>
	<body>
		<div class="container">
<?php
$gram=$_GET["gram"];
settype($gram, "double");
echo "<h1 class='text-center'>$formula</h1></br><table class='table table-striped'><tr><td>設定重量</td><td>$gram 克</td></tr>"; 
$f=count($matches);
$k=0;$j=0;$h=0;$i=0;//初始化
for ($g=0; $g < $f; $g++) { 
	if (!preg_match("/^[0-9]*[1-9][0-9]*$/",$matches[$g])) { //先檢查此項是否為係數
		$a=$g+1;//用於取得係數
		if($matches[$g]!=NULL){
			if (preg_match("/^[0-9]*[1-9][0-9]*$/",$matches[$a])) {
				//這裡是檢查元素後面是否有係數
				if($aw[$matches[$g]]!=NULL){
				echo "<tr><td>".$matches[$g]."的原子量</td><td>".$aw[$matches[$g]]."</td></tr>";
				echo "<tr><td>".$matches[$g]."對應的係數</td><td>".$matches[$a]."</td></tr>";
				}else{
				echo "<tr><td>".$matches[$g]."的原子量</td><td>無此元素</td></tr>";
				echo "<tr><td>".$matches[$g]."對應的係數</td><td>X</td></tr>";	
				}
				$b=$aw[$matches[$g]];//取得原子量
				$b=$b*$matches[$a];//將元素後面的係數乘以原子量
				$at[$k]=$b; //用於計算元素的重量
				$ak[$j]=$matches[$g];//用於計算元素的重量
			}else{
				if($aw[$matches[$g]]!=NULL){
				echo "<tr><td>".$matches[$g]."的原子量</td><td>".$aw[$matches[$g]]."</td></tr>";
				echo "<tr><td>".$matches[$g]."對應的係數</td><td>1</td></tr>";
				}else{
				echo "<tr><td>".$matches[$g]."的原子量</td><td>無此元素</td></tr>";
				echo "<tr><td>".$matches[$g]."對應的係數</td><td>X</td></tr>";	
				}
				$b=$aw[$matches[$g]];//如果沒有係數就直接取得原子量
				$at[$k]=$b; //用於計算元素的重量
				$ak[$j]=$matches[$g];//用於計算元素的重量
				$h++;//如果沒有係數就讓係數和+1
			}
		}
		$i=$i+$b;//和之前的分子量相加取得目前分子量
		$k++;$j++;
	}else{
		$h=$h+$matches[$g];//取得係數和
	}
}
echo "<tr><td>".$formula."的分子量</td><td> $i </td></tr>";//輸出分子量
echo "<tr><td>係數和</td><td> $h </td></tr>";
$mole=$gram/$i;
echo "<tr><td>".$gram."克的".$formula."</td><td>".$mole."莫耳</td></tr>";
$w=count($at);
for ($q=0; $q < $w; $q++) { 
	$m=$gram/$i*$at[$q];
	echo "<tr><td>".$gram."克的".$formula."中含有的".$ak[$q]."</td><td>".$m."克</td></tr>";
}
//取得原子數量
$h=$h*6*$mole;
echo "<tr><td>原子數量</td><td>";
if($gram==0){echo "0</td></tr>";}else{
$digits = ceil(log($h)/log(10)) - 1;
$str = round(($h / pow(10, $digits)),5) . 'x10<sup>' . ($digits+23) . "</td></tr>";
echo $str;
}
//取得單一分子或原子的重量
$ii=$i/6;
echo "<tr><td>單一分子</td><td>";
$digit = ceil(log($ii)/log(10)) - 1;
$str = round(($ii / pow(10, $digit)),5). 'x10<sup>' . ($digit-23) . "</sup>克</td></tr>";
echo $str;
?>
		</table>
		<a class="btn btn-default" href="<?php echo $url; ?>">重設數據</a>
	</div>
	</body>
</html>
<?php }?>