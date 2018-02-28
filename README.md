# rechargeable_battery_manager
This project aims to manage your rechargeable batteries. I have written this for my own needs but it might benefit others, so I'm posting it here.

I have a lot of rechargeable batteries and it became a mess to know which are good and which are bad. This little app enables you to monitor the status of your rechargeable batteries, if you keep a thorough record of what you are doing with them: when you charged them last, how much time they stayed powering a device, and how much mAh their capacity is if you have an intelligent charger that can measure that. You will need to give each of your batteries a number to achieve this.

Prerequisites: a web server with Mysql and Php.

It is still at an early stage, so there are still some actions that need to be performed directly through SQL commands.

To install, deploy the create_xxx.sql script that will create the necessary tables (beware that you have to create the user/database first, the names are "batteries" by default but can be changed in the sql script).
Add a user of your choice. As long as you connect through a local network (192.168.x.x) it auto-connects to user with ID 1, otherwise a login is required.
