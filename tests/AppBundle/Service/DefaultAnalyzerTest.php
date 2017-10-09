<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Criteria;
use AppBundle\Entity\Emphasizer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultAnalyzerTest extends WebTestCase {

    private $DefaultAnalyzer;

    public function __construct() {
        parent::__construct();
        self::bootKernel();
        $this->DefaultAnalyzer = static::$kernel->getContainer()->get('AppBundle.DefaultAnalyzer');
    }

    public function testDefaultAnalyzer() {
        $testCases = $this->getTestCases();

        foreach ($testCases as $testCase) {
            $result = $this->DefaultAnalyzer->analyze($testCase['review'])->getFullResults();
            $this->assertEquals(ksort($testCase['expectedResult']), ksort($result));
        }
    }


    private function getTestCases() {
        $cases =  [[
            'review' => '
                The room was great and the staff was nice.
                The restaurant was not bad.
                The room is also very spacious.
            ',
            'expectedResult' => [
                'room' => [
                    'score' => 250,
                    'criteria' => [[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('spacious'),
                        'emphasizer' => $this->getEmphasizer('very'),
                        'negated' => FALSE
                    ]]
                ],
                'staff' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('nice'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'restaurant' => [
                    'score' => 10,
                    'criteria' => [[
                        'entity' => $this->getCriteria('bad'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ]]
                ]
            ]
        ],[
            'review' => 'The restaurant is fantastic!',
            'expectedResult' => [
                'restaurant' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('fantastic'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                Most friendly and helpful receptionist ever, so lovely and great first impression of hotel. 
                Couldn\'t have been more sweet, giving me directions to a function I was attending along.
                Fortunately everything about the hotel was exceptional, and I don\'t give praise lightly.
                It was clean, stylish, roomy with excellent service in both bar where we had lunch and restaurant 
                where we had dinner. Food was beyond good and great value for money and service in both places.
                Room itself was well equipped and comfortable. 
            ',
            'expectedResult' => [
                'staff' => [
                    'score' => 350,
                    'criteria' => [[
                        'entity' => $this->getCriteria('friendly'),
                        'emphasizer' => $this->getEmphasizer('most'),
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('helpful'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('excellent'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'hotel' => [
                    'score' => 300,
                    'criteria' => [[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('exceptional'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('clean'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'food' => [
                    'score' => 200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'room' => [
                    'score' => 200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('well equipped'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('comfortable'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                How can a place so awful be a part of such a beautiful city?
                As soon as we pulled up outside and looked at the dirty,
                holey curtains hanging like rags behind the stinking glass of the rotting windows 
                we should have turned and run.
                The room was tiny and stank, as did the rest of the building.
                It was a combination of cats, mould, rot, damp, the local petting
                farm and a pair of Zoo Keepers’ wellies.
                We went out early and stayed out as late as we could manage.
                Next morning we were up and out, didn’t have breakfast as we saw the state of the 
                kitchen when we parked the car around the back in ‘Steptoes’ yard! 
                Which was not only full of junk but also half the cat population of Oxford, who incidentally, 
                made themselves very at home by laying all over my car.
            ',
            'expectedResult' => [
                'room' => [
                    'score' => -650,
                    'criteria' => [[
                        'entity' => $this->getCriteria('awful'),
                        'emphasizer' => $this->getEmphasizer('so'),
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('dirty'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('stinking'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('rotting'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('tiny'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('stank'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'hotel' => [
                    'score' => -200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('mould'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('rot'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'breakfast' => [
                    'score' => -100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('junk'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                The most disgusting and creepy hotel imaginable.
                Only place that had vacancies. dirty sheets, porn on the TV. weird screams in the morning, 
                possible blood drips on plastic mattress covering. 
                This was the most frightening experience, seriously debated sleeping in Central Park instead.
                This was worse than anything I’ve ever seen! Feared for my life!
            ',
            'expectedResult' => [
                'hotel' => [
                    'score' => -400,
                    'criteria' => [[
                        'entity' => $this->getCriteria('disgusting'),
                        'emphasizer' => $this->getEmphasizer('most'),
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('frightening'),
                        'emphasizer' => $this->getEmphasizer('most'),
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('worse'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'bed' => [
                    'score' => -200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('dirty'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('blood'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
            ]
        ],[
            'review' => '
                This is not a good hotel to stay in. It is not very bad, but it is not good neither. 
                The food isn\'t great and the bed wasn\'t clean.
                The stay wasn\'t a nightmare but it was not a good experience.
            ',
            'expectedResult' => [
                'hotel' => [
                    'score' => -280,
                    'criteria' => [[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ],[
                        'entity' => $this->getCriteria('bad'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ],[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ],[
                        'entity' => $this->getCriteria('nightmare'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ],[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ]]
                ],
                'food' => [
                    'score' => -100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ]]
                ],
                'bed' => [
                    'score' => -100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('clean'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ]]
                ]
            ]
        ],[
            'review' => '
                This hotel is not only a very enjoyable place, 
                but also has the best food ever.
                The pool isn\'t especially very good
            ',
            'expectedResult' => [
                'hotel' => [
                    'score' => 150,
                    'criteria' => [[
                        'entity' => $this->getCriteria('enjoyable'),
                        'emphasizer' => $this->getEmphasizer('very'),
                        'negated' => FALSE
                    ]]
                ],
                'food' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('best'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'pool' => [
                    'score' => -100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ]]
                ]
            ]
        ],[
            'review' => '
                The linens is good and the sheets were very clean.
                The managers are not helpful though.
                Pools aren\'t dirty.
            ',
            'expectedResult' => [
                'bed' => [
                    'score' => 250,
                    'criteria' => [[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('clean'),
                        'emphasizer' => $this->getEmphasizer('very'),
                        'negated' => FALSE
                    ]]
                ],
                'staff' => [
                    'score' => -100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('helpful'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ]]
                ],
                'pool' => [
                    'score' => 10,
                    'criteria' => [[
                        'entity' => $this->getCriteria('dirty'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ]]
                ]
            ]
        ],[
            'review' => '
                Located just near the fort in city(perfect location),the hotel is clean and provides u great hospitality.
                The owner Mr.Mukesh is very helpful and will guide u throughout your stay.
                The staff were very helpful. Provides u almost every facilities from good rooms to desert safari,
                jeep safari,camel safari and tent stay.
                The rooftop cafeteria was great.wen i say food was great(i mean it :)).
                Overall experience was awesome.
                I\'ll recommend dis hotel to every couple and families:).
                You wont regret,just be there:)
            ',
            'expectedResult' => [
                'location' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('perfect'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'hotel' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('clean'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'staff' => [
                    'score' => 400,
                    'criteria' => [[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('helpful'),
                        'emphasizer' => $this->getEmphasizer('very'),
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('helpful'),
                        'emphasizer' => $this->getEmphasizer('very'),
                        'negated' => FALSE
                    ]]
                ],
                'room' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'bar' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'food' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                We were disapointed with the standard of the room. 
                We were travelling with our little son, and I found it quite annoying that we couldnt 
                bring him into the bar for a afternoon soft drink or so. 
                The lobby would have been much better with 3-4 areas with comfy chairs and tables, 
                it was impossible to talk over the huge coffee table..
            ',
            'expectedResult' => [
                'bar' => [
                    'score' => -100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('annoying'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'room' => [
                    'score' => -100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('disappointed'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                Found this hotel by reading over tripadvisor while planning a little beach getaway. 
                Really good price by the beach. 
                James the front desk manager was really fun, he made our stay more fun than we thought it would be. 
                We are going to come back with our friends soon.
            ',
            'expectedResult' => [
                'hotel' => [
                    'score' => 150,
                    'criteria' => [[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => $this->getEmphasizer('really'),
                        'negated' => FALSE
                    ]]
                ],
                'staff' => [
                    'score' => 450,
                    'criteria' => [[
                        'entity' => $this->getCriteria('fun'),
                        'emphasizer' => $this->getEmphasizer('really'),
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('made our stay'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('fun'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('going to come back'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                Across the road from Santa Monica Pier is exactly where you want to be when visiting Santa Monica, 
                as well as not far from lots of shops and restaurants/bars.
                Hotel itself is very new & modern, rooms were great.
                Comfortable beds & possibly the best shower ever!
            ',
            'expectedResult' => [
                'bed' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('comfortable'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'hotel' => [
                    'score' => 150,
                    'criteria' => [[
                        'entity' => $this->getCriteria('new'),
                        'emphasizer' => $this->getEmphasizer('very'),
                        'negated' => FALSE
                    ]]
                ],
                'room' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'bathroom' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('best'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'restaurant' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('not far from'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                I have stayed here 4 or 5 times while visiting LA. 
                Despite travelling all over the world and staying in some of the best international hotels ( for work),
                Hotel Caliornia is one of my absolute favourites.
                Perfect location, right on the beach.
                I love the way you can just open your door and be outside, no elevators, corridors big glass windows.
                The ambience is so nice, retro perfect.
                As for the staff, I have had consistently superb service, 
                with much more personal service and attention to detail than is usual in bigger hotels.
                Service have been exemplary this time but really everyone is terrific.
                Can\'t recommend it highly enough.
            ',
            'expectedResult' => [
                'hotel' => [
                    'score' => 450,
                    'criteria' => [[
                        'entity' => $this->getCriteria('best'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('absolute favourite'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('nice'),
                        'emphasizer' => $this->getEmphasizer('so'),
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('perfect'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'location' => [
                    'score' => 200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('perfect'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('love'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'staff' => [
                    'score' => 300,
                    'criteria' => [[
                        'entity' => $this->getCriteria('superb'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('exemplary'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('terrific'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                Terrible. Old, not quite clean. Lost my reservation, then "found" a smaller room, for the same price, of course.
                Noisy. Absolutely no parking, unless you luck out for the $10 spaces (of which there are 12).
                Water in bathroom sink would not turn off. Not hair dryer, no iron in room.
                Miniscule shower- better be thin to use it!
            ',
            'expectedResult' => [
                'room' => [
                    'score' => -400,
                    'criteria' => [[
                        'entity' => $this->getCriteria('terrible'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('old'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('clean'),
                        'emphasizer' => NULL,
                        'negated' => TRUE
                    ],[
                        'entity' => $this->getCriteria('noisy'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'bathroom' => [
                    'score' => -200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('minuscule'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('thin'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                I was excited to stay at this Hotel. It looked cute and was reasonable. 
                It turned out to be terrible. We were woken up both mornings at 5:45 AM by noisy neighbors.
                The shower was clogged up...the room was sooooo small we kept tripping over each other.
                The saving grace was the pool at the Loews next door.
                I wish we had paid an extra $50 and stayed at a nicer place. This motel should cost no more than $99 a night.
            ',
            'expectedResult' => [
                'hotel' => [
                    'score' => -200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('terrible'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('noisy'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'bathroom' => [
                    'score' => -100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('clogged'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'room' => [
                    'score' => -100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('small'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ],[
            'review' => '
                Excellent location of the hotel in the center of city.
                Good food and good hospitality by Mr. Bhatia.
                Dessert safari was a great experience arranged by the hotel.
                I recommend this hotel to everyone, who is looking for a good stay, good food and great hospitality
            ',
            'expectedResult' => [
                'location' => [
                    'score' => 100,
                    'criteria' => [[
                        'entity' => $this->getCriteria('excellent'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'food' => [
                    'score' => 200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'staff' => [
                    'score' => 200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ],
                'hotel' => [
                    'score' => 200,
                    'criteria' => [[
                        'entity' => $this->getCriteria('great'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ],[
                        'entity' => $this->getCriteria('good'),
                        'emphasizer' => NULL,
                        'negated' => FALSE
                    ]]
                ]
            ]
        ]];

        return $cases;
    }


    private function getCriteria(string $name) : Criteria {
        self::bootKernel();
        return static::$kernel->getContainer()->get('doctrine')->getManager()
               ->getRepository('AppBundle:Criteria')->findBy(['keyword' => $name])[0];
    }

    private function getEmphasizer(string $name) : Emphasizer {
        self::bootKernel();
        return static::$kernel->getContainer()->get('doctrine')->getManager()
               ->getRepository('AppBundle:Emphasizer')->findBy(['name' => $name])[0];
    }
}
