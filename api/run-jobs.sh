#!/bin/bash

php /optimy/api/artisan schedule:run > /dev/stdout 2>/dev/stderr ;
