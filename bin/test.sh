#!/bin/bash

# Run a single test
php ../docroot/core/scripts/run-tests.sh --verbose --color --xml ../reports/tests/ --url http://cbd-toolkit.local.ro/ $@

