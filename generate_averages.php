<?php

$inputfilename = 'history.txt';
$outputfilename = 'averages.txt';
$rows = 0;
// The nested array to hold all the arrays
$the_big_array = [];
$rowsum = [];
$count = 0;
$averages = [];
$firstrun = 1;
$firstobs = 0;
$firstrunoutput = "";
$finaloutput = "";
$furtheroutput = "";
$addoutput = "";
$interval = 600;

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

$firstobs = $the_big_array[0][0];
for ($j = 0; $j <= $rows; $j++) 
{
  if ($firstrun == 1)
  {  
    if ($the_big_array[$j][0]-$firstobs <= $interval)
    {
    $count++;
    for ($z = 0; $z < 9; $z++)
      {  
      $rowsum [$z] += $the_big_array [$j][$z];
      }
    }
    else
      {
		for ($z = 0; $z < 9; $z++)
        {
        $averages [$z] = round(($rowsum[$z] / $count),2);
        }
      $firstrunoutput = implode (",",$averages);
	  $firstrunoutput .= "\r\n";
      $firstrun = 0;
      $firstobs = $the_big_array [$j][0]; 
      for ($i = 0; $i < 9; $i++) 
			{
			unset($rowsum[$i]);
			unset($averages[$i]);
			}
      $count = 0;
      $j--;
	  }
  }
   
   
   else //i.e. subsequent runs
   {
   if ($the_big_array[$j][0]-$firstobs <= $interval)
	{
	$count++;
		for ($z = 0; $z < 9; $z++)
		{  
		$rowsum [$z]+= $the_big_array [$j][$z];
		}
			
	}
	else
		{
		
		for ($z = 0; $z < 9; $z++)
			{
			$averages [$z] = round(($rowsum[$z] / $count),2);
			}
		$addoutput = implode (",",$averages);
		$furtheroutput .= $addoutput."\r\n";
		$firstobs = $the_big_array [$j][0];
		for ($i = 0; $i < 9; $i++) 
			{
			unset($rowsum[$i]);
			unset($averages[$i]);
			}
		$count = 0;
		$j--;
		}
   }
		
}
$finaloutput = $firstrunoutput.$furtheroutput;
file_put_contents($outputfilename, $finaloutput);
echo "<h1>File with the average values has been created and is ready for download <a href='/weatherstation/averages.txt' download>here.</a></h1>";
?>	   
