# Tab40 Location Label Import

## Requirements
 * The tab40 file is present on the local file system
 * You have access to the command line to run a cli script

## Import Label
 * Open command line and navigate to the project root
 * Run the tab40import script to convert a tab40 file into a vufind language file
 * Clear the language cache

## Example
	cli/tab40import.sh <NETWORK> <LOCALE> <SOURCE>
	cli/tab40import.sh idsbb de path/to/tab40.xxx

## Notes
 * run the script for all four locales even if terms are the same (as in IDSBB)