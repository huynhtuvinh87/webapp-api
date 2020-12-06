# Contractor Compliance - Webapp API - README

This document serves as additional information to developers.
This file was reset to provide more project-specific information, rather than cookie-cutter information from the default laravel project.
For the old readme, see `old_Readme.md` for details.

## Init

Please refer to old_Readme.md for now

## CLI Commands

### Backups

Files are stored on AWS - S3

#### Backup DB
`php artisan backup:db`

* Stores backup in `/storage/backups`
* Creates record in `backups` table

#### Backup Files
`php artisan backup:upload`

* Syncs file with AWS

#### Restoring DB
`php artisan restore:db`

* Checks `backups` table for file in `/storage/backups/`
* If file doesn't exist, marks backup as `deleted`.

### Importing Contractors

To import a list of contractors, create an excel spreadheet with the following columns (in order):
* Company Name
* Email
* Facility

Then use the following command to import the file

`php artisan import:contractors`

## Branches

`master`
`stable`
`production`

`feature/*`
`bugfix/*`
`hotfix/*`

## Development Cycle

<!-- TODO: Explain development cycle -->

## Versioning

* Using semantic versioning: {Major}.{Minor}.{Patch}
* Major
	* Changing the entire project, or something like that
* Minor
	* New feature
	* **NEW** Any migration
* Patch
	* Small change
	* `Bug` task types from JIRA
