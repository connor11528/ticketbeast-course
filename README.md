# Ticketbeast application

## What should build first?

what is the most important thing the application needs to do

- ~~Inviting promoters~~
- ~~Create accounts for promoters~~
- ~~Logging in as a promoter~~
- ~~Add concerts to the system~~
- ~~Editing concerts~~
- ~~Publishing concerts~~
- ~~Integrating with Stripe Connect to do direct payouts~~
- Purchasing tickets

Focus on value you’re trying to deliver. Do we actually have to build this feature?

## What should we test first?

- Purchasing tickets
	- View the concert listing
	- Allowing people to view published concerts
	- Not allowing people to view unpublished concerts
- Pay for the tickets
- View their purchased tickets in the browser
- Send an email confirmation w/a link back to the tickets

## Run tests

``` 
./vendor/bin/phpunit
```