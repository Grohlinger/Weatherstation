<?php

$inputfilename = 'history.txt';
$mailing_list_file = 'mailing_list.txt';
$rows = 0;
// The nested array to hold all the arrays
$the_big_array = [];
$rowsum_night = [];
$rowsum_day = [];
$night_row_count = 0;
$day_row_count = 0;
$averages_night = [];
$averages_day = [];
$maxima_night = [];
$minima_night = [];
$maxima_day = [];
$minima_day = [];
$begin_night = 0;
$begin_day = 0;
$lastobs = 0;
$mailing_list = [];
$nr_addresses = 0;
$dev_x = [];
$dev_y = [];
$trends =array("deteriotating","stable","improving"); //short term weather trends according to the recent slope of the air pressure development

// Open the file for reading
if (($h = fopen("{$inputfilename}", "r")) !== FALSE) 
{
  // Each line in the file is converted into an individual array that we call $data
  // The items of the array are comma separated
  while (($data = fgetcsv($h, 1000, ",")) !== FALSE)
 {
    // Each individual array is being pushed into the nested array
    $the_big_array[] = $data;
    $rows++;
  }
  
  // Close the file
  fclose($h);
}

// read mailing_list
if (($k = fopen("{$mailing_list_file}", "r")) !== FALSE) 
{
  // Each line in the file is converted into an individual array that we call $data
  // The items of the array are comma separated
  while (($contacts = fgetcsv($k, 1000, ",")) !== FALSE)
 {
    // Each individual array is being pushed into the nested array
    $mailing_list[] = $contacts;
    $nr_addresses++;
  }

  // Close the file
  fclose($k);
}

//end mailing list, continue script
$lastobs = $the_big_array [$rows-1][0];
$begin_night = $lastobs - 36000;
$begin_day = $lastobs - 86400;


$j = $rows-1;
for ($z = 0; $z < 9; $z++)
	{
	$maxima_night [$z] [0] = $the_big_array [$j][$z];
	$minima_night [$z] [0]= $the_big_array [$j][$z];
	$maxima_night [$z] [1] = $the_big_array [$j][0];
	$minima_night [$z] [1]= $the_big_array [$j][0];
	}
		while($the_big_array[$j][0] >= $begin_night)
		{
		$night_row_count++;
			
			for ($z = 0; $z < 9; $z++)
			{
				if ($the_big_array [$j][$z] > $maxima_night [$z][0])
					{
					$maxima_night [$z][0] = $the_big_array [$j][$z];
					$maxima_night [$z][1] = $the_big_array [$j][0];
					}
				if ($the_big_array [$j][$z] <= $minima_night [$z][0])
					{
					$minima_night [$z][0]= $the_big_array [$j][$z];
					$minima_night [$z][1]= $the_big_array [$j][0];
					}
			}
		
		for ($z = 0; $z < 9; $z++)
		$rowsum_night [$z] += $the_big_array [$j][$z];
		
		$j--;
		}
		
for ($z = 0; $z < 9; $z++)
	{		
	$maxima_day [$z] [0] = $the_big_array [$j][$z];
	$minima_day [$z] [0]= $the_big_array [$j][$z];
	$maxima_day [$z] [1] = $the_big_array [$j][0];
	$minima_day [$z] [1]= $the_big_array [$j][0];		
	}
	
		while($the_big_array[$j][0] >= $begin_day)
		{
		$day_row_count++;
		
			for ($z = 0; $z < 9; $z++)
			{
				if ($the_big_array [$j][$z] > $maxima_day [$z][0])
					{
					$maxima_day [$z][0] = $the_big_array [$j][$z];
					$maxima_day [$z][1] = $the_big_array [$j][0];
					}
      
				if ($the_big_array [$j][$z] <= $minima_day [$z] [0])
					{
					$minima_day [$z][0]= $the_big_array [$j][$z];
					$minima_day [$z][1]= $the_big_array [$j][0];
					}
			}
		for ($z = 0; $z < 9; $z++)
        $rowsum_day [$z] += $the_big_array [$j][$z];
	
		$j--;
		}
	

for ($z = 0; $z < 9; $z++)
	{
	$averages_night [$z] = round(($rowsum_night[$z] / $night_row_count),2);
    $averages_day [$z] = round(($rowsum_day[$z] / $day_row_count),2); 
	}

//run regression to determine slope of atmospheric pressure

for ($z = $start_row_night; $z <= $rows-1; $z++)
	{
	$dev_x [$z]= $the_big_array [$z][0]- $averages_night [0];
	$dev_y [$z]= $the_big_array [$z][2]- $averages_night [2];
	$dev_x_dev_y[$z] = $dev_x[$z]*$dev_y[$z];
	$sum_dev_x_dev_y += $dev_x_dev_y[$z];
	$dev_x_squared [$z]= $dev_x[$z]*$dev_x[$z];
	$covar_x += $dev_x_squared[$z];
	}
$slope=$sum_dev_x_dev_y/$covar_x;
//to print out the weather trend in the newsletter
if ($slope <= -0.001){
	$current_trend = $trends[0];}
else if ($slope >= 0.001){
	$current_trend = $trends[2];}
else {$current_trend = $trends [1];}

//generate emails to recipients of the mailing list
for ($i = 0; $i < $nr_addresses; $i++)
{

$subject = "Daily Tassin-la-Demi-Lune Weather Outpost Update"; 
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$message = '
<html>
<style> table, th, td {border: 1px solid black; border-collapse: collapse; margin-left:auto; margin-right:auto; padding:2px; border-spacing:20px}</style> 
<head>
  <title>Summary of the latest weather conditions</title>
</head>
<body>
  <h2>Good Morning Dear '.$mailing_list[$i][2].',</h2>
  <p>Here is our newsletter with today\'s weather forecast for Tassin-la-Demi-Lune and the environmental observations of the last 24 hours.</p>
  <p>Please note that yesterday\'s day-time is triggered 24 hours ago, i.e. yesterday at about 6:00 in the morning. Night-time starts yesterday at about 20:00 and lasts until now.</p>  
  <table>
    <tr>
      <th>Condition</th><th>Yesterday Day-Time Average</th><th>Yesterday Day-Time Highs</th><th>@</th><th>Yesterday Day-Time Lows</th><th>@</th><th>This Night Average</th><th>This Night Highs</th><th>@</th><th>This Night Lows</th><th>@</th>
    </tr>
    <tr>
      <td style="text-align:center">Temperature [°C]</td><td style="text-align:center">'.$averages_day [1].'</td><td style="text-align:center">'.$maxima_day[1][0].'</td><td style="text-align:center">'.date('H',$maxima_day[1][1]).':'.date('i',$maxima_day[1][1]).'</td><td style="text-align:center">'.$minima_day[1][0].'</td><td style="text-align:center">'.date('H',$minima_day[1][1]).':'.date('i',$minima_day[1][1]).'</td><td style="text-align:center">'.$averages_night [1].'</td><td style="text-align:center">'.$maxima_night[1][0].'</td><td style="text-align:center">'.date('H',$maxima_night[1][1]).':'.date('i',$maxima_night[1][1]).'</td><td style="text-align:center">'.$minima_night [1][0].'</td><td style="text-align:center">'.date('H',$minima_night[1][1]).':'.date('i',$minima_night[1][1]).'</td>
    </tr>
	<tr>
      <td style="text-align:center">Pressure [Pa]</td><td style="text-align:center">'.$averages_day [2].'</td><td style="text-align:center">'.$maxima_day[2][0].'</td><td style="text-align:center">'.date('H',$maxima_day[2][1]).':'.date('i',$maxima_day[2][1]).'</td><td style="text-align:center">'.$minima_day[2][0].'</td><td style="text-align:center">'.date('H',$minima_day[2][1]).':'.date('i',$minima_day[2][1]).'</td><td style="text-align:center">'.$averages_night [2].'</td><td style="text-align:center">'.$maxima_night[2][0].'</td><td style="text-align:center">'.date('H',$maxima_night[2][1]).':'.date('i',$maxima_night[2][1]).'</td><td style="text-align:center">'.$minima_night [2][0].'</td><td style="text-align:center">'.date('H',$minima_night[2][1]).':'.date('i',$minima_night[2][1]).'</td>
    </tr>
	<tr>
      <td style="text-align:center">Humidity [%]</td><td style="text-align:center">'.$averages_day [3].'</td><td style="text-align:center">'.$maxima_day[3][0].'</td><td style="text-align:center">'.date('H',$maxima_day[3][1]).':'.date('i',$maxima_day[3][1]).'</td><td style="text-align:center">'.$minima_day[3][0].'</td><td style="text-align:center">'.date('H',$minima_day[3][1]).':'.date('i',$minima_day[3][1]).'</td><td style="text-align:center">'.$averages_night [3].'</td><td style="text-align:center">'.$maxima_night[3][0].'</td><td style="text-align:center">'.date('H',$maxima_night[3][1]).':'.date('i',$maxima_night[3][1]).'</td><td style="text-align:center">'.$minima_night [3][0].'</td><td style="text-align:center">'.date('H',$minima_night[3][1]).':'.date('i',$minima_night[3][1]).'</td>
    </tr>
	<tr>
      <td style="text-align:center">LPG [PPM]</td><td style="text-align:center">'.$averages_day [4].'</td><td style="text-align:center">'.$maxima_day[4][0].'</td><td style="text-align:center">'.date('H',$maxima_day[4][1]).':'.date('i',$maxima_day[4][1]).'</td><td style="text-align:center">'.$minima_day[4][0].'</td><td style="text-align:center">'.date('H',$minima_day[4][1]).':'.date('i',$minima_day[4][1]).'</td><td style="text-align:center">'.$averages_night [4].'</td><td style="text-align:center">'.$maxima_night[4][0].'</td><td style="text-align:center">'.date('H',$maxima_night[4][1]).':'.date('i',$maxima_night[4][1]).'</td><td style="text-align:center">'.$minima_night [4][0].'</td><td style="text-align:center">'.date('H',$minima_night[4][1]).':'.date('i',$minima_night[4][1]).'</td>
    </tr>
	<tr>
      <td style="text-align:center">Smoke [PPM]</td><td style="text-align:center">'.$averages_day [5].'</td><td style="text-align:center">'.$maxima_day[5][0].'</td><td style="text-align:center">'.date('H',$maxima_day[5][1]).':'.date('i',$maxima_day[5][1]).'</td><td style="text-align:center">'.$minima_day[5][0].'</td><td style="text-align:center">'.date('H',$minima_day[5][1]).':'.date('i',$minima_day[5][1]).'</td><td style="text-align:center">'.$averages_night [5].'</td><td style="text-align:center">'.$maxima_night[5][0].'</td><td style="text-align:center">'.date('H',$maxima_night[5][1]).':'.date('i',$maxima_night[5][1]).'</td><td style="text-align:center">'.$minima_night [5][0].'</td><td style="text-align:center">'.date('H',$minima_night[5][1]).':'.date('i',$minima_night[5][1]).'</td>
    </tr>
	<tr>
      <td style="text-align:center">Dust-Particles [Pcs/l]</td><td style="text-align:center">'.$averages_day [6].'</td><td style="text-align:center">'.$maxima_day[6][0].'</td><td style="text-align:center">'.date('H',$maxima_day[6][1]).':'.date('i',$maxima_day[6][1]).'</td><td style="text-align:center">'.$minima_day[6][0].'</td><td style="text-align:center">'.date('H',$minima_day[6][1]).':'.date('i',$minima_day[6][1]).'</td><td style="text-align:center">'.$averages_night [6].'</td><td style="text-align:center">'.$maxima_night[6][0].'</td><td style="text-align:center">'.date('H',$maxima_night[6][1]).':'.date('i',$maxima_night[6][1]).'</td><td style="text-align:center">'.$minima_night [6][0].'</td><td style="text-align:center">'.date('H',$minima_night[6][1]).':'.date('i',$minima_night[6][1]).'</td>
    </tr>
	<tr>
      <td style="text-align:center">Oxygen-Concentration [%]</td><td style="text-align:center">'.$averages_day [7].'</td><td style="text-align:center">'.$maxima_day[7][0].'</td><td style="text-align:center">'.date('H',$maxima_day[7][1]).':'.date('i',$maxima_day[7][1]).'</td><td style="text-align:center">'.$minima_day[7][0].'</td><td style="text-align:center">'.date('H',$minima_day[7][1]).':'.date('i',$minima_day[7][1]).'</td><td style="text-align:center">'.$averages_night [7].'</td><td style="text-align:center">'.$maxima_night[7][0].'</td><td style="text-align:center">'.date('H',$maxima_night[7][1]).':'.date('i',$maxima_night[7][1]).'</td><td style="text-align:center">'.$minima_night [7][0].'</td><td style="text-align:center">'.date('H',$minima_night[7][1]).':'.date('i',$minima_night[7][1]).'</td>
    </tr>
	<tr>
      <td style="text-align:center">CO-Concentration [PPM]</td><td style="text-align:center">'.$averages_day [8].'</td><td style="text-align:center">'.$maxima_day[8][0].'</td><td style="text-align:center">'.date('H',$maxima_day[8][1]).':'.date('i',$maxima_day[8][1]).'</td><td style="text-align:center">'.$minima_day[8][0].'</td><td style="text-align:center">'.date('H',$minima_day[8][1]).':'.date('i',$minima_day[8][1]).'</td><td style="text-align:center">'.$averages_night [8].'</td><td style="text-align:center">'.$maxima_night[8][0].'</td><td style="text-align:center">'.date('H',$maxima_night[8][1]).':'.date('i',$maxima_night[8][1]).'</td><td style="text-align:center">'.$minima_night [8][0].'</td><td style="text-align:center">'.date('H',$minima_night[8][1]).':'.date('i',$minima_night[8][1]).'</td>
    </tr>
   </table>
   <p>The current environmental observations can be checked <a href="http://family-groh.eu/weatherstation/current_weather.html">here.</a></p>
   <p>The weather forecast for Tassin-la-Demi-Lune for today is: <b>'.$current_trend.'!</b></p>
  
</body>
</html>'
;
  
mail($mailing_list[$i][3], $subject, $message, $headers);
sleep(1);

}

?>	   
