<!doctype html>  
<html lang="th">
<head>
	<script type="text/javascript" src="js/Chart.js"></script>
	<script type="text/javascript" src="js/chartjs-plugin-datalabels.js"></script>
	<!-- <script type="text/javascript" src="js/Chart.PieceLabel.min.js"></script> -->
	<script type="text/javascript" src="js/chartjs-plugin-labels.js"></script>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600' rel='stylesheet'>
	<style>
		html { font-family: 'Open Sans', sans-serif; display: block; margin: 0px auto; text-align: center;color: #000;}
	</style>

</head>
<?php
date_default_timezone_set("Asia/Bangkok");
if (empty($_POST["quantity"])) {
	$quantity = "";
	$setquantity = "359";
} else {
	$quantity = $_POST["quantity"];
	$setquantity = "-".$quantity;	
};

	
	#ดึงข้อมูล
	$url = "https://covid19.th-stat.com/json/covid19v2/getTodayCases.json";
	$content = file_get_contents($url);
	if ($content === false) {
		exit('เชื่อมต่อไม่ได้');
	}
	$content =  json_decode($content);
/*	{"Confirmed":439477,"Recovered":304456,"Hospitalized":131411,"Deaths":3610,"NewConfirmed":13002,
	"NewRecovered":8248,"NewHospitalized":4646,"NewDeaths":108,
	"UpdateDate":"21\/07\/2021 12:45","DevBy":"https:\/\/www.kidkarnmai.com\/"}
*/
	#เรียงข้อมูลใหม่
	$data = array($content->UpdateDate,$content->NewConfirmed,$content->NewRecovered,$content->NewHospitalized,$content->NewDeaths,
					$content->Confirmed,$content->Recovered,$content->Hospitalized,$content->Deaths);
	#แบ่งวันและเวลาเป็นคนละ Array
	$jsondatetime = explode(" ", $data[0]);
	#แบ่ง วัน/เดือน/ปี เป็นคนละ Array
	$jsondate = explode("/", $jsondatetime[0]);
	#เรียง Array เป็น เดือน/วัน/ปี เพื่อให้เข้ากับข้อมูลในไฟล์ที่บันทึก
	$jsondate = $jsondate[1]."/".$jsondate[0]."/".$jsondate[2];
	#เปลี่ยน Array วัน เวลา เป็นวันที่จัดเรียงใหม่แล้วอย่างเดียว
	$data[0] = $jsondate;
	#เอา $data มาทำเป็น text โดยแบ่งแต่ละ Array ด้วย -
	$textdata = implode("-", $data);
	
	#ดึงข้อมูลจาก Covid ทั้งหมดจาก txt
	//$covid19data = file_get_contents('./covid19data.txt', true);
	$covid19data = file('covid19data.txt');
	#แบ่งข้อมูลแต่ละบรรทัดเป็น Array ด้วย \n
	//$covid19data = explode("\n", $covid19data);
	#เลือกข้อมูลวันสุดท้ายในไฟล์
	$textlatestcovid19 = end($covid19data);
	#แบ่งข้อมูลของวันเป็น Array ด้วย -
	$latestcovid19data = explode("-", $textlatestcovid19);
	#ดูว่าข้อมูลล่าสุดที่บันทึกไว้ เดือน/ปี/วัน อะไร
	$latestdate = reset($latestcovid19data);
	
	#แสดงข้อความให้ดูเปรียบเทียบ
	echo 'ข้อมูลจาก JSON = '.$textdata;
	echo '<br>';
	echo 'ข้อมูลจาก FILE = '.$textlatestcovid19;
	echo '<br>';
	echo '<br>';
	
	#ถ้า JSON ล่าสุด และข้อมูลล่าสุดในไฟล์ เป็นวันเดียวกัน
	if ($jsondate == $latestdate) {
		#ถ้าข้อมูลจาก JSON และข้อมูลล่าสุดจากไฟล์ไม่ตรงกัน
		if ($textdata != $textlatestcovid19) {
			
			// load the data and delete the line from the array 
			$lines = file('covid19data.txt'); 
			$last = sizeof($lines) - 1 ;
			$lines[$last] = $textdata;
			// write the new data to the file 
			$fp = fopen('covid19data.txt', 'w'); 
			fwrite($fp, implode('', $lines)); 
			fclose($fp); 

			echo "อัพเดตข้อมูลวันล่าสุดเป็นข้อมูลใหม่แล้ว";
		} else {
			#ถ้าข้อมูลตรงกัน
			echo "ข้อมูลวันล่าสุดจาก JSON ยังเป็นข้อมูลเดิม เหมือนใน FILE";
		};
	} else {
		#ถ้าคนละวัน
		#เอา $data บันทึกลง txt
		file_put_contents("./covid19data.txt","\n".$textdata,FILE_APPEND);
		#แสดงข้อความ
		echo "บันทึกข้อมูลวันใหม่แล้ว";
	}

?>
<body>
<?php


$covid19data = file('covid19data.txt');
array_splice($covid19data,0,$setquantity);

$date = array();
$newConfirmed = array();
$newRecovered = array();
$newHospitalized = array();
$newDeaths = array();
$confirmed = array();
$recovered = array();
$hospitalized = array();
$deaths = array();

foreach ($covid19data as $each) {
	$each = explode("-", $each);
	$date[] = $each[0];
	$newConfirmed[] = $each[1];
	$newRecovered[] = $each[2];
	$newHospitalized[] = $each[3];
	$newDeaths[] = $each[4];
	$confirmed[] = $each[5];
	$recovered[] = $each[6];
	$hospitalized[] = $each[7];
	$deaths[] = $each[8];
}

$graphDate = json_encode($date);
$lastdate	= end($date);
$lastdate = explode("/", $lastdate);
$lastdate = $lastdate[1]."/".$lastdate[0]."/".$lastdate[2];
$graphNewConfirmed = implode(",",$newConfirmed);
$graphNewRecovered = implode(",",$newRecovered);
$graphNewHospitalized = implode(",",$newHospitalized);
$graphNewDeaths = implode(",",$newDeaths);
$graphConfirmed = implode(",",$confirmed);
$graphRecovered = implode(",",$recovered);
$graphHospitalized = implode(",",$hospitalized);
$graphDeaths = implode(",",$deaths);

$count = array(end($newConfirmed),end($newRecovered),end($newHospitalized),end($newDeaths));
$graphLabel = "'ผู้ป่วยใหม่','ผู้ป่วยที่เข้ารับการรักษา','ผู้ป่วยที่หายแล้ว','เสียชีวิต'";
$graphCount = implode(",",$count);
?>	
<table width="99%" style="margin-left: auto; margin-right: auto;">
<tr >
	<td style="vertical-align: top;">
		<canvas id="pie-chart1"></canvas>
	</td>
</tr>
<tr >
	<td>
<form action="index.php" method="post">
  <label for="quantity">จำนวนวันที่ต้องการดูย้อนหลัง (สูงสุด 500 วัน):</label>
  <input type="number" id="quantity" name="quantity" min="1" max="500" value="<?=$quantity?>">
  <input type="submit" value="ส่ง">
</form>
	</td>
</tr>
<tr >
	<td style="vertical-align: top;">
		<canvas id="line-chart1"></canvas>
	</td>
</tr>
<tr >
	<td style="vertical-align: top;">
		<canvas id="line-chart2"></canvas>
	</td>
</tr>
</table>
<script>
	Chart.defaults.global.defaultFontColor = 'black';
	Chart.defaults.global.defaultFontFamily = 'Open Sans';
	Chart.defaults.global.defaultFontSize = 14;
	Chart.defaults.global.defaultFontStyle = 'normal';
	//var ctx = document.getElementById("line-chart1");
	//ctx.height = 95;
	var ctx = document.getElementById("pie-chart1");
	ctx.height = 140;
	
Chart.plugins.register({
    afterRender: function(c) {
        console.log("afterRender called");
        var ctx = c.chart.ctx;
        ctx.save();
        // This line is apparently essential to getting the
        // fill to go behind the drawn graph, not on top of it.
        // Technique is taken from:
        // https://stackoverflow.com/a/50126796/165164
        ctx.globalCompositeOperation = 'destination-over';
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, c.chart.width, c.chart.height);
        ctx.restore();
    }
});
</script>

<?php
echo "
					<script>
							new Chart(document.getElementById('pie-chart1'), {
							type: 'pie',
							data: {
								labels: 	[$graphLabel],
								datasets: [{
									backgroundColor: ['#ff0000','#ff9900','#33cc33','#000000','#00ff00','#80ccff','#ffccff','#ffff80','#ccffcc','#f0f0f5','#99ff33','#80ff80','#99ffd6','#b3fff0','#b3ffff'],
									//label: 'Sensor หน้าบ้าน 1',
									//fill: false,
									//lineTension: 0.1,
									//borderColor: 'rgba(255,0,0,1)', // The main line color
									//backgroundColor: 'rgba(225,0,0,0.4)',
									//backgroundColor: 'rgba(255,0,0,0.4)',
									//borderCapStyle: 'square',
									//borderDash: [5, 15], // try [5, 15] for instance
									//borderDashOffset: 0.0,
									//borderJoinStyle: 'miter',
									pointBorderColor: 'black',
									pointBackgroundColor: 'white',
									//pointBorderWidth: 1,
									pointHoverRadius: 8,
									//pointHoverBackgroundColor: 'yellow',
									pointHoverBorderColor: 'brown',
									pointRadius: 4,
									pointHitRadius: 10,
									data: [$graphCount]
									//spanGaps: true,
								}]
							},
							options: {
								responsive: true,
								maintainAspectRatio: true,
								aspectRatio : 1,
								title: {
									fontSize: 30,
									display: true,
									padding: 45,
									text: 'ภาพรวมยอด COVID-19 ล่าสุด - $lastdate'
								},
								legend: {
									display: true,
									labels : {
										fontSize: 15
									}
								},
						    plugins: {
								labels: {
									render: 'label',
									fontSize: 22,
									fontColor: '#000',
									fontStyle: 'bold',
									arc: false,
									position: 'outside',
									//segment: false,
									outsidePadding: 0,
									textMargin: 5,
									showActualPercentages: true
								},
								datalabels: {
									display : 'auto',
									anchor : 'center',
									align : 'end',
									offset : 50,
									clamp: false,
									backgroundColor: function(context) {
										return context.dataset.backgroundColor;
									},
									borderRadius: 4, 
									clip: false,
									color: function(context) {
										var index = context.dataIndex;
										if((0 === index)|(3 === index)) {
											return value = 'white';
										} else {
											return value = 'white';
										}
									},
									font: {
										weight: 'bold',
										size:  '22'
									},
									formatter: function(value) {
									/*	return value.y; */
										return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
									}
								},
							},
							layout: {
								padding: {
									left: 0,
									right: 0,
									top: 50,
									bottom: 0
								}
							}
						}
					});
						
						
						new Chart(document.getElementById('line-chart1'), {
							type: 'line',
							data: {
								labels: 	$graphDate,
								datasets: [
								{
									//backgroundColor: ['#ff0000','#e65c00','#ff9933','#ffd633','#00ff00','#80ccff','#ffccff','#ffff80','#ccffcc','#f0f0f5','#99ff33','#80ff80','#99ffd6','#b3fff0','#b3ffff'],
									label: 'ผู้ป่วยใหม่',
									//fill: false,
									//lineTension: 0.1,
									borderColor: 'rgba(255,0,0,1)', // The main line color
									//backgroundColor: 'rgba(225,0,0,0.4)',
									backgroundColor: 'rgba(255,0,0,0.3)',
									//borderCapStyle: 'square',
									//borderDash: [5, 15], // try [5, 15] for instance
									//borderDashOffset: 0.0,
									//borderJoinStyle: 'miter',
									pointBorderColor: 'black',
									pointBackgroundColor: 'white',
									//pointBorderWidth: 1,
									pointHoverRadius: 6,
									//pointHoverBackgroundColor: 'yellow',
									pointHoverBorderColor: 'brown',
									pointRadius: 0,
									pointHitRadius: 5,
									data: [$graphNewConfirmed]
									//spanGaps: true,
								},{
									//backgroundColor: ['#ff0000','#e65c00','#ff9933','#ffd633','#00ff00','#80ccff','#ffccff','#ffff80','#ccffcc','#f0f0f5','#99ff33','#80ff80','#99ffd6','#b3fff0','#b3ffff'],
									label: 'ผู้ป่วยที่เข้ารับการรักษา',
									//fill: false,
									//lineTension: 0.1,
									borderColor: 'rgba(255,153,0,1)', // The main line color
									//backgroundColor: 'rgba(225,0,0,0.4)',
									backgroundColor: 'rgba(255,153,0,0.3)',
									//borderCapStyle: 'square',
									//borderDash: [5, 15], // try [5, 15] for instance
									//borderDashOffset: 0.0,
									//borderJoinStyle: 'miter',
									pointBorderColor: 'black',
									pointBackgroundColor: 'white',
									//pointBorderWidth: 1,
									pointHoverRadius: 6,
									//pointHoverBackgroundColor: 'yellow',
									pointHoverBorderColor: 'brown',
									pointRadius: 0,
									pointHitRadius: 5,
									data: [$graphNewHospitalized]
									//spanGaps: true,
								},{
									//backgroundColor: ['#ff0000','#e65c00','#ff9933','#ffd633','#00ff00','#80ccff','#ffccff','#ffff80','#ccffcc','#f0f0f5','#99ff33','#80ff80','#99ffd6','#b3fff0','#b3ffff'],
									label: 'ผู้ป่วยที่หายแล้ว',
									//fill: false,
									//lineTension: 0.1,
									borderColor: 'rgba(51,204,51,1)', // The main line color
									//backgroundColor: 'rgba(225,0,0,0.4)',
									backgroundColor: 'rgba(51,204,51,0.3)',
									//borderCapStyle: 'square',
									//borderDash: [5, 15], // try [5, 15] for instance
									//borderDashOffset: 0.0,
									//borderJoinStyle: 'miter',
									pointBorderColor: 'black',
									pointBackgroundColor: 'white',
									//pointBorderWidth: 1,
									pointHoverRadius: 6,
									//pointHoverBackgroundColor: 'yellow',
									pointHoverBorderColor: 'brown',
									pointRadius: 0,
									pointHitRadius: 5,
									data: [$graphNewRecovered]
									//spanGaps: true,
								},{
									//backgroundColor: ['#ff0000','#e65c00','#ff9933','#ffd633','#00ff00','#80ccff','#ffccff','#ffff80','#ccffcc','#f0f0f5','#99ff33','#80ff80','#99ffd6','#b3fff0','#b3ffff'],
									label: 'เสียชีวิต',
									//fill: false,
									//lineTension: 0.1,
									borderColor: 'rgba(0,0,0,1)', // The main line color
									//backgroundColor: 'rgba(225,0,0,0.4)',
									backgroundColor: 'rgba(230,230,230,0.3)',
									//borderCapStyle: 'square',
									//borderDash: [5, 15], // try [5, 15] for instance
									//borderDashOffset: 0.0,
									//borderJoinStyle: 'miter',
									pointBorderColor: 'black',
									pointBackgroundColor: 'white',
									//pointBorderWidth: 1,
									pointHoverRadius: 6,
									//pointHoverBackgroundColor: 'yellow',
									pointHoverBorderColor: 'brown',
									pointRadius: 0,
									pointHitRadius: 5,
									data: [$graphNewDeaths]
									//spanGaps: true,
								}]
								
							},
							options: {
								title: {
									display: true,
									fontSize: 30,
									padding: 12,
									text: 'ยอด COVID-19 ในแต่ละวัน'
								},
								scales: {
									yAxes: [{
										ticks: {
											beginAtZero:false,
											maxTicksLimit: 20,
											fontSize: 15
										},
										scaleLabel: {
											display: true,
											labelString: 'จำนวน',
											fontSize: 25
										}
									}],
									xAxes: [{
										ticks: {
											beginAtZero:false,
											maxTicksLimit: 50,
											fontSize: 14
										},
										scaleLabel: {
											display:false,
											labelString: 'วัน	',
											fontSize: 12 
										}
									}]
								},
								legend: {
									display: true,
									labels : {
										fontSize: 15
									}
								},
								layout: {
									padding: 30
								},
								plugins: {
									datalabels: {
										display : 'auto',
										anchor : 'end',
										align : 'top',
										offset : 4,
										clamp: true,
										backgroundColor: function(context) {
											return context.dataset.backgroundColor;
										},
										borderRadius: 5,
										clip: false,
										color: 'black',
										font: {
											weight: 'bold',
											size:  '13'
										},
										formatter: function(value) {
										/*	return value.y; */
											return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
										}
									}
								}
							}
						});
						
						
						
						new Chart(document.getElementById('line-chart2'), {
							type: 'line',
							data: {
								labels: 	$graphDate,
								datasets: [
								{
									//backgroundColor: ['#ff0000','#e65c00','#ff9933','#ffd633','#00ff00','#80ccff','#ffccff','#ffff80','#ccffcc','#f0f0f5','#99ff33','#80ff80','#99ffd6','#b3fff0','#b3ffff'],
									label: 'ผู้ป่วย',
									//fill: false,
									//lineTension: 0.1,
									borderColor: 'rgba(255,0,0,1)', // The main line color
									//backgroundColor: 'rgba(225,0,0,0.4)',
									backgroundColor: 'rgba(255,0,0,0.3)',
									//borderCapStyle: 'square',
									//borderDash: [5, 15], // try [5, 15] for instance
									//borderDashOffset: 0.0,
									//borderJoinStyle: 'miter',
									pointBorderColor: 'black',
									pointBackgroundColor: 'white',
									//pointBorderWidth: 1,
									pointHoverRadius: 6,
									//pointHoverBackgroundColor: 'yellow',
									pointHoverBorderColor: 'brown',
									pointRadius: 0,
									pointHitRadius: 5,
									data: [$graphConfirmed]
									//spanGaps: true,
								},{
									//backgroundColor: ['#ff0000','#e65c00','#ff9933','#ffd633','#00ff00','#80ccff','#ffccff','#ffff80','#ccffcc','#f0f0f5','#99ff33','#80ff80','#99ffd6','#b3fff0','#b3ffff'],
									label: 'ยังรักษาตัวในโรงพยาบาล',
									//fill: false,
									//lineTension: 0.1,
									borderColor: 'rgba(255,153,0,1)', // The main line color
									//backgroundColor: 'rgba(225,0,0,0.4)',
									backgroundColor: 'rgba(255,153,0,0.3)',
									//borderCapStyle: 'square',
									//borderDash: [5, 15], // try [5, 15] for instance
									//borderDashOffset: 0.0,
									//borderJoinStyle: 'miter',
									pointBorderColor: 'black',
									pointBackgroundColor: 'white',
									//pointBorderWidth: 1,
									pointHoverRadius: 6,
									//pointHoverBackgroundColor: 'yellow',
									pointHoverBorderColor: 'brown',
									pointRadius: 0,
									pointHitRadius: 5,
									data: [$graphHospitalized]
									//spanGaps: true,
								},{
									//backgroundColor: ['#ff0000','#e65c00','#ff9933','#ffd633','#00ff00','#80ccff','#ffccff','#ffff80','#ccffcc','#f0f0f5','#99ff33','#80ff80','#99ffd6','#b3fff0','#b3ffff'],
									label: 'หายแล้ว',
									//fill: false,
									//lineTension: 0.1,
									borderColor: 'rgba(51,204,51,1)', // The main line color
									//backgroundColor: 'rgba(225,0,0,0.4)',
									backgroundColor: 'rgba(51,204,51,0.3)',
									//borderCapStyle: 'square',
									//borderDash: [5, 15], // try [5, 15] for instance
									//borderDashOffset: 0.0,
									//borderJoinStyle: 'miter',
									pointBorderColor: 'black',
									pointBackgroundColor: 'white',
									//pointBorderWidth: 1,
									pointHoverRadius: 6,
									//pointHoverBackgroundColor: 'yellow',
									pointHoverBorderColor: 'brown',
									pointRadius: 0,
									pointHitRadius: 5,
									data: [$graphRecovered]
									//spanGaps: true,
								},{
									//backgroundColor: ['#ff0000','#e65c00','#ff9933','#ffd633','#00ff00','#80ccff','#ffccff','#ffff80','#ccffcc','#f0f0f5','#99ff33','#80ff80','#99ffd6','#b3fff0','#b3ffff'],
									label: 'เสียชีวิต',
									//fill: false,
									//lineTension: 0.1,
									borderColor: 'rgba(0,0,0,1)', // The main line color
									//backgroundColor: 'rgba(225,0,0,0.4)',
									backgroundColor: 'rgba(230,230,230,0.3)',
									//borderCapStyle: 'square',
									//borderDash: [5, 15], // try [5, 15] for instance
									//borderDashOffset: 0.0,
									//borderJoinStyle: 'miter',
									pointBorderColor: 'black',
									pointBackgroundColor: 'white',
									//pointBorderWidth: 1,
									pointHoverRadius: 6,
									//pointHoverBackgroundColor: 'yellow',
									pointHoverBorderColor: 'brown',
									pointRadius: 0,
									pointHitRadius: 5,
									data: [$graphDeaths]
									//spanGaps: true,
								}]
								
							},
							options: {
								title: {
									display: true,
									fontSize: 30,
									padding: 12,
									text: 'ยอดรวม COVID-19'
								},
								scales: {
									yAxes: [{
										ticks: {
											beginAtZero:false,
											maxTicksLimit: 20,
											fontSize: 15
										},
										scaleLabel: {
											display: true,
											labelString: 'จำนวน',
											fontSize: 25
										}
									}],
									xAxes: [{
										ticks: {
											beginAtZero:false,
											maxTicksLimit: 50,
											fontSize: 14
										},
										scaleLabel: {
											display:false,
											labelString: 'วัน	',
											fontSize: 12 
										}
									}]
								},
								legend: {
									display: true,
									labels : {
										fontSize: 15
									}
								},
								layout: {
									padding: 30
								},
								plugins: {
									datalabels: {
										display : 'auto',
										anchor : 'end',
										align : 'top',
										offset : 4,
										clamp: true,
										backgroundColor: function(context) {
											return context.dataset.backgroundColor;
										},
										borderRadius: 5,
										clip: false,
										color: 'black',
										font: {
											weight: 'bold',
											size:  '13'
										},
										formatter: function(value) {
										/*	return value.y; */
											return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
										}
									}
								}
							}
						});
						</script>
					";
?>	
</body>
</html>
