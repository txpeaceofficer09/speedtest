#!/usr/bin/env php
<?php

// Get the list of local servers
$data = `speedtest --servers`;

// Break the lines into an array
$arr = explode("\n", $data);
$servers = [];

// Servers you do not want to test against.
$blocked = [
        '47863', // City of Dayton - DayNet - Dayton, TX
];

// Iterate through the lines and get the server ID for each server
foreach ( $arr AS $line ) {
        $line = trim($line);

        if ( preg_match('/^([\d]+)/', $line, $matches) ) {
                // Add the server ID to the servers array if it is not one of the blocked servers
                if ( !in_array($matches[1], $blocked) ) {
                        $servers[] = $matches[1];
                }
        }
}

// Get a random server from the server list
$sid = $servers[array_rand($servers)];

// Connect to MySQL server.
$mysqli = new mysqli('SERVER_ADDRESS', 'USERNAME', 'PASSWORD', 'DATABASE') or die("Could not connect to MySQL server.\n");

// Create the table to store our data, if it does not already exist.
$mysqli->query("CREATE TABLE IF NOT EXISTS `results` ( `server` VARCHAR(128) , `isp` VARCHAR(128) , `download` VARCHAR(64) , `upload` VARCHAR(64) , `loss` VARCHAR(16) , `latency` VARCHAR(64) , `url` VARCHAR(1024) , `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP );");

// Run a speed test on the selected server and capture the results
$data = `speedtest --server-id={$sid}`;

// Break the lines of the test results into an array for processing
$arr = explode("\n", $data);

// Iterate through each line of the speed test results and find the data we are looking for
foreach ($arr AS $line) {
        $line = trim($line);
        if ( strpos($line, ':') !== false ) {
                $key = trim(substr($line, 0, strpos($line, ':')));
                $val = trim(substr($line, strlen($key)+1));

                switch ($key) {
                        case 'Server':
                                $server = $val;
                                break;
                        case 'ISP':
                                $isp = $val;
                                break;
                        case 'Download':
                                if ( preg_match('/^([\d\.]+ [mbpsgk]+)/i', $val, $matches) ) {
                                        $download = $matches[1];
                                } else {
                                        $download = $val;
                                }
                                break;
                        case 'Upload':
                                if ( preg_match('/^([\d\.]+ [mbpsgk]+)/i', $val, $matches) ) {
                                        $upload = $matches[1];
                                } else {
                                        $upload = $val;
                                }
                                break;
                        case 'Result URL':
                                $url = $val;
                                break;
                        case 'Packet Loss':
                                $loss = $val;
                                break;
                        case 'Idle Latency':
                                $latency = $val;
                                break;
                }
        }
}

// Write the results of our test to the MySQL database
$mysqli->query("INSERT INTO `speedtest`.`results` ( `server`, `isp`, `download`, `upload`, `loss`, `latency`, `url` ) VALUES ( '$server', '$isp', '$download', '$upload', '$loss', '$latency', '$url' );");

// Close the connection to the database since we are finished
$mysqli->close();

?>
