# Scheduler Request Cron
This module runs the [Scheduler](https://www.drupal.org/project/scheduler) cron job after automatically after requests. The interval between executions can be set.

## Prerequisites
This module requires the following Drupal modules:
* [Scheduler](https://www.drupal.org/project/scheduler)

## Installing
1. Create a folder named scheduler_request_cron in the modules directory of the Drupal installation.
2. Move the files within the created directory.
3. Activate module.

## Options
* Interval: The minimum interval in minutes between executions. The cron is only run when a page is requested. Defaults to 5 minutes.
* Log: Enables logging of the cron execution (additionally to Scheduler log). Defaults to false.
