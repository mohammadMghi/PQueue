<?php
require 'Queue.php';
require 'SendEmailJob.php';

$pdo = new PDO("mysql:host=localhost;dbname=test", "root", "852456");
$queue = new Queue($pdo);
 

function parseCommandArgs(array $argv): array {
    array_shift($argv);  
    $args = [];

    foreach ($argv as $arg) { 
        if (preg_match('/^--([^=]+)(=(.*))?$/', $arg, $matches)) {
            $key = $matches[1];
            $value = $matches[3] ?? true; 
            $args[$key] = $value;
        }
     
        elseif (preg_match('/^-([a-zA-Z])$/', $arg, $matches)) {
            $key = $matches[1];
            $value = true; 
            $args[$key] = $value;
        }
    }

    return $args;
}

$args = parseCommandArgs($argv);
$retry     = $args['retry']     ?? 0;
$runEvery  = $args['run-every'] ?? 1;
$queueName = $args['queue-name'] ?? 'default';
 

// while(true) {
//     $job = $queue->pop('default');

//     sleep(10);
// }

while (true) {

    print($queueName);
    if (isset($queueName)) {
        $jobData = $queue->pop($queueName);
    } else {
        $jobData = $queue->pop('default');
    }
    
    if ($jobData) {
        $job = unserialize(base64_decode($jobData['payload']));
        try {
            $job->handle();
            $queue->delete($jobData['id']);
            echo "Processed job #{$jobData['id']}\n";
        } catch (Exception $e) {
            $queue->markJobAsFailed($jobData['id']);
        }
    }

    
    $runEvery ? sleep($runEvery) : sleep(1);
}