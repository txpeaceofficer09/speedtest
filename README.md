# speedtest
Script to automate the CLI speedtest from Ookla and write those results to a database.

You can create a cron job to run periodically and capture speed test results.

The script will randomize the local server that it tests against so it doesn't always just show you results from one server near you.  If you don't want it to randomize, you can simply remove the `--server-id={$sid}` and speedtest will run and find the "best" server near you.

You will need the Ookla speedtest CLI client and you will probably need to run it once and accept the EULA type stuff.
