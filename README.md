# WMDE GitHub webhooks

[![Build Status](https://secure.travis-ci.org/wmde/github-webhooks.png?branch=master)](http://travis-ci.org/wmde/github-webhooks)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wmde/github-webhooks/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wmde/github-webhooks/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/github-webhooks/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wmde/github-webhooks/?branch=master)

This repo contains code to handle GitHub webhooks. Actions that have long processing times are done by a CLI script.

Functionality:

- Handle pushes to the master and production branch of the Fundraising application to deploy the new version to the test and production servers.

## Running the tests

For tests only

    composer test

For style checks only

	composer cs

For a full CI run

	composer ci