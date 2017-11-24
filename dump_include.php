<?php

// create import file
$file = 'importable-dhcpd.conf';
$handle1 = fopen($file, 'w') or die('Cannot open file:  '.$file);



$handle = fopen("dhcpd.conf", "r");
if ($handle) {
   $i=1;
    while (($line = fgets($handle)) !== false) {
	$clean_line=trim($line);
	if(substr($clean_line, 0,9)==="include \""){
	   echo "include detected\n";
	   $include=split('"',$clean_line);
	   if (file_exists($include[1]) === false) { continue;  }
	   $content = file_get_contents($include[1]);
	   $handle1 = fopen($file, 'a') or die('Cannot open file:  '.$file);
	   fwrite($handle1, $content);
	}else{
	   $handle1 = fopen($file, 'a') or die('Cannot open file:  '.$file);
           fwrite($handle1, $line);
	}
    }
    fclose($handle1);
    fclose($handle);
} else {
    // error opening the file.
} 

?>