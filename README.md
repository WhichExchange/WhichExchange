## WhichExchange

[Try it out!](http://kwikthinkers.xyz/whichexchange/)

A simple web app game in which the player must determine which site on the StackExchange network the given question comes from.

Questions are pulled from a random site (of the pre-determined set of sites to pull from) using the StackExchange/StackOverflow API.

The player may select the time window from which questions are pulled. In other words, you can choose how recent you want the questions to be, from within the past month, to all time (for example).

The questions that are pulled are from the set of the highest-scoring questions of size <strong><i>n</i></strong> within the given time frame, where <strong>n</strong> is a positive integer that I don't remember the value of.

## Running the Server
Run the webserver locally:

    php -S localhost:8000 -c /etc/php.ini.default

Then point a web browser at:

    localhost:8000

## API Key & Rate Limiting
An API key is optional. The [StackExchange API](https://api.stackexchange.com/) currently allows for around 300 un-keyed requests before it will stop responding to requests, hence the counter on the page. With a basic API key registered for an app, the request increases to about 10,000.

## Can't I just Google the questions?
Yes, fun-killer.

## Future Stuff
* More time windows, or better/more flexible way to specify
 * The page should show the currently selected window
* Scoring/high scores/user accounts
* Customization of the sites that can be pulled from (currently a hardcoded set)
* Get a logo

## About
Created by [Giovanni Carvelli](https://github.com/gcarvelli) and [Chris Sprague](https://github.com/chrissprague) at HackNJIT 2015.

Based on an idea of the same premise discussed in the StackExchange podcast episode #57
