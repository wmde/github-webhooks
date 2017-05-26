# WMDE GitHub webhooks

[![Build Status](https://secure.travis-ci.org/wmde/github-webhooks.png?branch=master)](http://travis-ci.org/wmde/github-webhooks)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wmde/github-webhooks/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wmde/github-webhooks/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/github-webhooks/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wmde/github-webhooks/?branch=master)

Continuous deployment for the WMDE fundraising projects.

At present only the master and production branches of the FundraisingFrontend application are supported. 

## High level workflow

* The application exposes a `/deploy` endpoint which gets called by GitHub webhooks.
* The `/deploy` endpoint saves the data it got, when relevant, into a database table.
* Something (i.e. Cron) invokes one of the deployment scripts in `/cli`
* The deployment script checks if there is something that needs deploying,
  and if so, executes an (Ansible) command that takes care of deployment.

## Running the tests

For tests only

    composer test

For style checks only

	composer cs

For a full CI run

	composer ci