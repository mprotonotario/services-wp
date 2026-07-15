#!/bin/bash

# Clear bootstrap cache files to avoid package discovery issues from host volume mount
rm -f bootstrap/cache/*.php

# Cache configuration for performance
php artisan config:cache
php artisan route:cache


# Start the queue worker in the background
echo "Starting Laravel queue worker..."
php artisan queue:work --verbose --tries=3 &

# Start the Laravel application server
echo "Starting Laravel application server..."
php artisan serve --host=0.0.0.0 --port=8000 &

# Keep script running and monitor background processes
wait -n

exit $?
