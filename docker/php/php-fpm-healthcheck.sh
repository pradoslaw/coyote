#!/bin/bash
env --ignore-environment /usr/bin/cgi-fcgi -bind -connect localhost:9000 || exit 1
