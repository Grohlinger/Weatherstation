<?php

$inputfilename = 'history.txt';
$mailing_list_file = 'mailing_list.txt';
$rows = 0;
// The nested array to hold all the arrays
$the_big_array = [];
$rowsum = [];
$count = 0;
$averages_night = [];
$averages_day = [];
$maxima_night = [];
$minima_night = [];
$maxima_day = [];
$minima_day = [];
$begin_night = [];
$begin_day = [];
$lastobs = 0;
$output = "";
$addoutput = "";
$mailing_list = [];
$mailto = [];
$nr_addresses = 0;
$to = "";

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
for ($i = 0; $i < $nr_addresses; $i++)
  {
  $mailto[$i] = $mailing_list[$i][3];
  }
$to = implode(",",$mailto);
$to ="'".$to."'";

//end mailing list, continue script
$lastobs = $the_big_array [$rows-1][0];
$begin_night = $lastobs - 36000;
$begin_day = $lastobs - 86400;
$start_row_night = $rows-1;
$start_row_day = $rows-1;

for ($z = 0; $z < 9; $z++)
	{
		$maxima_night [$z] [0] = $the_big_array [$rows-1][$z];
		$minima_night [$z] [0]= $the_big_array [$rows-1][$z];
		$maxima_night [$z] [1] = $the_big_array [$rows-1][0];
		$minima_night [$z] [1]= $the_big_array [$rows-1][0];
		$maxima_day [$z] [0] = $the_big_array [$start_row_night-1][$z];
		$minima_day [$z] [0]= $the_big_array [$start_row_night-1][$z];
		$maxima_day [$z] [1] = $the_big_array [$start_row_night-1][0];
		$minima_day [$z] [1]= $the_big_array [$start_row_night-1][0];
	}
for (($j = ($rows-1)); $j >= 0; $j--) 
	{
		if ($the_big_array[$j][0] > $begin_night)
		$start_row_night--;
		
		if($the_big_array[$j][0] > $begin_day)
		$start_row_day--;
	}
  
for ($j = $rows-1; $j >= $start_row_night; $j--)
{ 	
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
    $count++;
    for ($z = 0; $z < 9; $z++)
      {  
      $rowsum [$z] += $the_big_array [$j][$z];
      }
}
for ($z = 0; $z < 9; $z++)
      {
      $averages_night [$z] = round(($rowsum[$z] / $count),2);
      }
$count=0;
for ($i = 0; $i < 9; $i++) 
			{
			unset($rowsum[$i]);
			}
		
//night time calculation finished. Now follows the same for day time

for ($j = ($start_row_night-1); $j >= $start_row_day; $j--)
{
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
    $count++;
    for ($z = 0; $z < 9; $z++)
      {  
      $rowsum [$z] += $the_big_array [$j][$z];
      }
}
for ($z = 0; $z < 9; $z++)
        {
        $averages_day [$z] = round(($rowsum[$z] / $count),2);
        }
$temp_average_day = $averages_day [1];

$subject = 'Daily Tassin-la-Demi-Lune Weather-Station Update'; 
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$message = '
<html>
<style> table, th, td {border: 1px solid black; border-collapse: collapse; margin-left:auto; margin-right:auto; padding:2px; border-spacing:20px}</style> 
<head>
  <title>Summary of the latest weather conditions</title>
</head>
<body>
  <h2>Dear Subscriber* of our Mailing List,</h2>
  <p>Here are the observations of the last 24 hours.</p>
  <p>Please note that day-time is considered between 6:00 and 20:00, while night-time is 20:00 until 6:00 in the morning of the following day.</p>  
  <table>
    <tr>
      <th>Condition</th><th>Yesterday Day-Time Average</th><th>Yesterday Day-Time Highs</th><th>@</th><th>Yesterday Day-Time Lows</th><th>@</th><th>This Night-Time Average</th><th>This Night Highs</th><th>@</th><th>This Night Lows</th><th>@</th>
    </tr>
    <tr>
      <td style="text-align:center">Temperature [° Celsius]</td><td style="text-align:center">'.$averages_day [1].'</td><td style="text-align:center">'.$maxima_day[1][0].'</td><td style="text-align:center">'.date('H',$maxima_day[1][1]).':'.date('i',$maxima_day[1][1]).'</td><td style="text-align:center">'.$minima_day[1][0].'</td><td style="text-align:center">'.date('H',$minima_day[1][1]).':'.date('i',$minima_day[1][1]).'</td><td style="text-align:center">'.$averages_night [1].'</td><td style="text-align:center">'.$maxima_night[1][0].'</td><td style="text-align:center">'.date('H',$maxima_night[1][1]).':'.date('i',$maxima_night[1][1]).'</td><td style="text-align:center">'.$minima_night [1][0].'</td><td style="text-align:center">'.date('H',$minima_night[1][1]).':'.date('i',$minima_night[1][1]).'</td>
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
   <p>We hope that you like the information we provide on the latest weather conditions and thank you very much for your interest.</p>
   </br>
   </br>
   </br>
   <p style="font-size:10px;">*)Should you wish to unsubscribe from our newsletter please contact our Sales Manager Umbuthuanga Odeth Sambawabde in our office in Linglongwe, Malawi, ph.: +387.65.45.73.91, during local office hours. 
</body>
</html>
';
  
mail($to, $subject, $message, $headers);

?>	   
