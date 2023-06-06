# Scheduler Request Cron
This module runs the [Scheduler](https://www.drupal.org/project/scheduler) cron job after automatically after requests. The interval between executions can be set.

## Prerequisites
This module requires the following Drupal modules:
* [Scheduler](https://www.drupal.org/project/scheduler)

## Installing
Install the module using composer: `composer require drupal/scheduler_request_cron`

## Options
* Interval: The minimum interval in minutes between executions. The cron is only run when a page is requested. Defaults to 5 minutes.
* Log: Enables logging of the cron execution (additionally to Scheduler log). Defaults to false.
