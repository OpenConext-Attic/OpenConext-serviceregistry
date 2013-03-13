#!/bin/sh
echo "stats" | nc -w 1 localhost 11211 | awk '$2 == "bytes" { printf("Memcache uses %6.1f MB\n", $3/1048576) }'