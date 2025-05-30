#!/bin/bash

# This is a sample shell script
# It prints a greeting and lists files in the current directory.

echo "Hello from the script!"

# Check if a parameter is provided
if [ -n "$1" ]; then
    echo "Parameter provided: $1"
else
    echo "No parameter provided."
fi

echo "Listing files in $(pwd):"
ls -la

# End of script
exit 0
