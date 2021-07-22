<?php
	
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

	$data = array($content->UpdateDate,$content->Confirmed,$content->Recovered,$content->Hospitalized,$content->Deaths,
					$content->NewConfirmed,$content->NewRecovered,$content->NewHospitalized,$content->NewDeaths);
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
			//end($covid19data);
			//$key = key($covid19data);
			////reset($covid19data);
			//$covid19data[$key] = $textdata;
			
			// load the data and delete the line from the array 
			$lines = file('covid19data.txt'); 
			$last = sizeof($lines) - 1 ;
			//unset($lines[$last]); 
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
	//echo '<pre>',print_r($covid19data,1),'</pre>';	

	//$fp = fopen('./covid19data.txt', 'a');
	//$data = implode("\n",$fp);
	//echo $fp;
	//fwrite($fp,$data);
	
/*	
	$jsondatadate = reset($data);
	file_put_contents('./todaycovid19.txt', $data);
	
	$covid19data = file_get_contents('./covid19data.txt', true);
	$covid19data = explode("-", $covid19data);
	$covid19data = end($covid19data);
	$latestHumidity = $covid19data[0];
	
	
	file_put_contents('./todaycovid19.txt', $data);
	$latestdata = reset($data);
	echo $latestdata;
	
	//$datetime = explode(" ", $data[0]);
	//$date = $datetime[0];
	
	//echo '<pre>',print_r($data,1),'</pre>';
	//var_dump($covid19data);
	*/

?>