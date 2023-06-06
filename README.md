This module has been renamed to **Scheduler Request Cron** and transfered to git.drupalcode.org for the purpose of making it an Open-Source Drupal.org project.   
   
Link to Drupalcode Repository: https://git.drupalcode.org/project/scheduler_request_cron   
Drupal.org Scheduler Request Cron: https://www.drupal.org/project/scheduler_request_cron   

# Scheduler Cron
This module runs the [Scheduler](https://www.drupal.org/project/scheduler) cron job after automatically after requests. The interval between executions can be set.

## Prerequisites
This module requires the following Drupal modules:
* [Scheduler](https://www.drupal.org/project/scheduler)

## Installing
1. Create a folder named scheduler_cron in the modules directory of the Drupal installation.
2. Move the files within the created directory.
3. Activate module.

## Options
* Interval: The minimum interval in minutes between executions. The cron is only run when a page is requested. Defaults to 5 minutes.
* Log: Enables logging of the cron execution (additionally to Scheduler log). Defaults to false.
