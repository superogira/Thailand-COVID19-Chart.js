<!doctype html>  
<html lang="th">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>กราฟแสดงภาพรวม COVID-19 ในประเทศไทย</title>
	<script type="text/javascript" src="js/Chart.js"></script>
	<script type="text/javascript" src="js/chartjs-plugin-datalabels.js"></script>
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
	
	#ข้อมูลประจำวัน
	$url = "https://covid19.th-stat.com/json/covid19v2/getTodayCases.json";
	$content = @file_get_contents($url);
	if ($content === false) {
		$content = file_get_contents("./getTodayCases.json");
	} else {
		$arrayDecoded = json_decode($content,true);
		if (count($arrayDecoded) > 9) {
			file_put_contents("./getTodayCases.json", fopen("https://covid19.th-stat.com/json/covid19v2/getTodayCases.json", 'r'));
		};
	};
	$arrayDecoded = json_decode($content);
	$todayCases = array($arrayDecoded->NewConfirmed,$arrayDecoded->NewRecovered,$arrayDecoded->NewHospitalized,$arrayDecoded->NewDeaths);
	//$todayCases = array($arrayDecoded[NewConfirmed],$arrayDecoded[NewRecovered],$arrayDecoded[NewHospitalized],$arrayDecoded[NewDeaths]);
	$todayCasesDate = $arrayDecoded->UpdateDate;
	$todayCasesGraphLabel = "'ผู้ป่วยใหม่','ผู้ป่วยที่เข้ารับการรักษา','ผู้ป่วยที่หายแล้ว','เสียชีวิต'";
	$todayCasesGraphCount = implode(",",$todayCases);
	
	
	#ข้อมูลทั้งหมด
	$url = "https://covid19.th-stat.com/json/covid19v2/getTimeline.json";
	$content = @file_get_contents($url);
	if ($content === false) {
		$content = file_get_contents("./getTimeline.json");
		$arrayDecoded = json_decode($content, true);
	} else {
		$arrayDecoded = json_decode($content,true);
		$content2 = @file_get_contents("./getTimeline.json");
		if (empty($content2)) {
			$content2 = '{"Data":[{}]}';
		};
		$arrayDecoded2 = json_decode($content2, true);
		if (count($arrayDecoded["Data"]) > count($arrayDecoded2["Data"])) {
			file_put_contents("./getTimeline.json", fopen("https://covid19.th-stat.com/json/covid19v2/getTimeline.json", 'r'));
		};
	};
	$daycount = count($arrayDecoded["Data"]);
	$allCases = $arrayDecoded["Data"];
	array_splice($allCases,0,$setquantity);
	
	$date = array();
	$newConfirmed = array();
	$newRecovered = array();
	$newHospitalized = array();
	$newDeaths = array();
	$confirmed = array();
	$recovered = array();
	$hospitalized = array();
	$deaths = array();

	foreach ($allCases as $each) {
		$date[] = $each["Date"];
		$newConfirmed[] = $each["NewConfirmed"];
		$newRecovered[] = $each["NewRecovered"];
		if ($each["NewHospitalized"] < 0) {
			$each["NewHospitalized"] = $each["NewHospitalized"] * -1;
		};
		$newHospitalized[] = $each["NewHospitalized"];
		$newDeaths[] = $each["NewDeaths"];
		$confirmed[] = $each["Confirmed"];
		$recovered[] = $each["Recovered"];
		$hospitalized[] = $each["Hospitalized"];
		$deaths[] = $each["Deaths"];
	};
	
	$graphDate = json_encode($date);
	$graphNewConfirmed = implode(",",$newConfirmed);
	$graphNewRecovered = implode(",",$newRecovered);
	$graphNewHospitalized = implode(",",$newHospitalized);
	$graphNewDeaths = implode(",",$newDeaths);
	$graphConfirmed = implode(",",$confirmed);
	$graphRecovered = implode(",",$recovered);
	$graphHospitalized = implode(",",$hospitalized);
	$graphDeaths = implode(",",$deaths);
	
/*	$arrayDecoded = json_decode($content, true);
	$count = array(end($newConfirmed),end($newRecovered),end($newHospitalized),end($newDeaths));
	$graphLabel = "'ผู้ป่วยใหม่','ผู้ป่วยที่เข้ารับการรักษา','ผู้ป่วยที่หายแล้ว','เสียชีวิต'";
	$graphCount = implode(",",$count);
	//$content =  json_decode($content);
	
	//echo '<pre>',print_r($content[Array],1),'</pre>';
	//echo '<pre>',print_r($arrayDecoded["Data"][0]["Date"],1),'</pre>';
	//var_dump($content);
*/
?>
<body>
<table width="99%" style="margin-left: auto; margin-right: auto;">
<tr >
	<td style="vertical-align: top;">
		<canvas id="pie-chart1"></canvas>
	</td>
</tr>
<tr >
	<td>
<form action="index.php" method="post">
  <label for="quantity">จำนวนวันที่ต้องการดูย้อนหลัง (สูงสุด <?=$daycount?> วัน):</label>
  <input type="number" id="quantity" name="quantity" min="1" max="<?=$daycount?>" value="<?=$quantity?>">
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
								labels: 	[$todayCasesGraphLabel],
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
									data: [$todayCasesGraphCount]
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
									text: 'ยอด COVID-19 ประจำวัน - $todayCasesDate'
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
											maxTicksLimit: 60,
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
											maxTicksLimit: 60,
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
