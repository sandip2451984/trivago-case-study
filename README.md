# __Trivago Case Study - Sandip Limbachiya__

## Database model
---
_(This model was done using mysql-workbench. The types may differ for SQLite. SQLite was used in the application.)_
![Graph](https://drive.google.com/uc?export=download&id=0B1HUczm7Goblb01NMEUwTmpBczA)

##### __Reviews__
Stores all the reviews and their total scores.  
If the review is yet to be analyzed, then total_score will be NULL.   

##### __Topics__  
Stores all the main topics. For example: hotel, bar, pool, staff, etc.  
The priority is used for detecting the sentence topic when more than one topic is found in the sentence.  
For example: _"The location of the hotel is great"_.  
Since the topic "location" has more priority than the topic "hotel", it will assign correctly the criteria "great" to the topic "location"  

##### __Topics aliases__  
Aliases for referring to the same topic.  
Example: the aliases "lavatory", "shower", "toilet" refer to the main topic "bathroom"  

##### __Criteria__  
The words (or group of words) with their score.  
By default, all positive criteria have a score of 100, and the negative criteria a score of -100.  
This way the score can be tweaked depending on the criteria.  

##### __Emphasizers__  
Words that emphasizes the criteria, with their score modifier.  
For example, "very", "really", "most", etc. By default, all the score modifiers are 0.5.  
So, if found "very good", then the total score will be 150 (100 + (0.5 * 100))  

##### __Analysis__  
Stores the score for a given topic for a given review.  

##### __Analysis criteria__  
Stores the found criteria (along with the emphasizer, if any, and with the negated flag if the criteria found is negated) for a given analysis (an analysis is the score for a given topic).  



## Dependencies and extensions  
---
- PHP 7.1
- SQLite with php7.1-sqlite3 extension
- php7.1-pspell extension for TypoFixer (optional)
- NodeJS (needed for Yarn package manager)
- Yarn for managing frontend dependencies
- Composer for managing backend dependencies
- php7.1-xdebug extension for phpunit code coverage (optional)
- Symfony requirements extensions
    - php7.1-mbstring
    - php7.1-xml
    - php7.1-intl (optional)


#### Backend composer dependencies (apart from symfony default ones)
- ICanBoogie/Inflector -> Used for pluralizing words
- phpunit/phpunit -> For running the tests
- jms/serializer-bundle -> For serializing doctrine entities into JSON

#### Frontend yarn dependencies (web/package.json)
- Bootstrap
- JQuery (with jsgrid and json-viewer plugins)
- Webpack (with css-loader)
 

## Setup
---
Make sure that PHP7.1 with the dependencies and extensions shown above are correctly installed.  
You can now clone the repository in a folder of your choice  
- _git clone git@github.com:m-gonalons-camps/trivago-case-study.git FOLDER_NAME_

or unzip the file with the code (which contains the cloned repository) in a folder of your choice.  

In the root folder you will find the file "bash-setup.sh".  
This bash script executes the needed commands for setting-up the application.  
It will install composer dependencies, yarn dependencies, create the database, create the tables using symfony's commands, populate the database with some topics, criteria and reviews and finally it will launch the server and listen in port 8000.  
During composer install, you will be asked for the database parameters. Just hit enter all the time for using the default ones.  
If everything went smoothly, (which it should :)) then everything is ready!  
The only thing left now is going to localhost:8000 for testing the application.  

___Important___  
You will notice that assetic bundle is not installed, and therefore we don't execute the command __"php bin/console assetic:dump"__. Instead, I used webpack for bundling the JS & CSS files.  
On another hand, regarding the command __"php app/console doctrine:migrations:migrate"__:
I realized that this was not generating the SQL for generating the foreign keys, even though they are correctly mapped in the entities.  
For generating the tables, I used the command __"php bin/console doctrine:schema:update --force"__, since this one generated the correct SQL for the foreign keys.  
The command __doctrine:migrations:migrate__ is still available and can be used, but it will not generate the foreign keys and therefore the onCascade mappings will not work properly.  

##### Tests with PHPUNIT  
If phpunit is already installed in your system, you can execute directly __"phpunit"__ in the root folder and the tests will run. If not, you can execute __"vendor/phpunit/phpunit/phpunit"__ since the application installs it as a composer dependency.  
If you have also the XDebug extension enabled, you can add the option --coverage-html
___phpunit --coverage-html var/phpunit___ for checking the tests coverage of the code.  


## Analyzer  
---
How the analyzer works, with some examples  

_"Found this hotel by reading over tripadvisor while planning a little beach getaway. Really good price by the beach. James the front desk manager was really fun, he made our stay more fun than we thought it would be. We are going to come back with our friends soon."_  

First of all, we divide the text into small strings where we can detect easily the topic and the criteria.  
We divide by the following characters / words: __! ? , and & but : ; .__  
After dividing, we have the following chunks:  
- Found this hotel by reading over tripadvisor while planning a little beach getaway  
- Really good price by the beach  
-  James the front desk manager was really fun  
-  he made our stay more fun than we thought it would be  
-  We are going to come back with our friends soon  

Now we can easily detect the topic and the criteria for each chunk.  
If we can't detect the topic in the chunk, we assign the criteria to the last known topic.  

If there is more than one topic in the same chunk, the topic with more priority wins.  
As I already stated above, let's see this example:  
_"The location of the hotel is great"_
Since we've configured in our application that the topic "location" has more priority than the topic "hotel", it will assign the criteria to the topic "location".  
The point of the priority is to assign high priority to more specific topics, like "location", "room" or "breakfast", and assign lower priority to more generic topics like "hotel".  

Let's take a look now at this other examples:  
_"Terrible. Old, not quite clean. Lost my reservation, then 'found' a smaller room"_  
- Terrible
- Old
- Not quite clean
- Lost my reservation
- then 'found' a smaller room
 
Until the last chunk, we don't know which topic is being referred to.  
I had to chose here: I could assign the criteria to a fallback generic topic, like "hotel", or assign the criteria to the topic "unknown" until I find a topic, in this case, "room".  
In this particular case, it would be kind of the same to assign the criteria to the fallback topic "hotel" or to assign it to the topic "room"  

_"Most  friendly and helpful receptionist"_  
- Most friendly
- helpful receptionist

In this other example, in my opinion it would have been an error to assign the criteria "friendly" to a fallback generic topic like "hotel".  

So I finally decided to wait until I find another topic and assign the criteria to that topic.  
If I cannot find any other topic, all the criteria found will remain under the topic "unknown"  


##### Emphasizers and negators
_"This is a very nice hotel"_  
Whenever I find a criteria in one of this chunks, I immediately check for emphasizers and negators.  
In this case, we have the emphasizer "very", so the total score would be:
_criteria_score + (emphasizer_modifier * criteria_score)_  

_"The restaurant isn't good"_  
_"The restaurant isn't bad"_  

Regarding negators, I decided to treat differently negated positive criteria and negated negative criteria.  
In my opinion, the two examples above should not be treated equally.  
Saying "something is not good" is clearly a negative thing, but saying "something is not bad" is not the contrary.  
So, whenever a good criteria is negated, his score is inverted. If good has 100 points, saying "not good" will result in a score of -100  
For negative criteria: if bad has -100 points, saying "not bad" will result in a score of 10.  
This score modifiers can be tweaked in the class Service/DefaultAnalyzerConstants.php  
