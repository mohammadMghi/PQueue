# PQueue
PQueue is a job queue system with php for educational purpose

## Run
```
    php Worker.php
```
```
    php Enqueue.php
```

## Params
Run with a queue name, if you don't pass this param it run default queue
```
    php Worker --queue-name="queue_name"
```

Run specific job with id 
```
    php Worker.php --id=1 --retry
```

Done application after job become empty
```
    php Worker.php --stop-when-empty
```

## Job
Inside Enqueue.php you can define your job First part is your job class and second is queue name and last is priority If you want to run this queue you have to pass queue name to CLI argument

```
    $job = new SendEmailJob("user@example.com", "Welcome!");
    $queue->push($job , 'queue_name',1);
```
Run 

```
    php Worker.php --queue-name=queue_name
```
