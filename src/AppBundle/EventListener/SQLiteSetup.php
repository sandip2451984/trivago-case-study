<?php

namespace AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Query\ResultSetMapping;

class SQLiteSetup {

    /**
     * SQLite does not have foreign keys enabled by default.
     * In order to get the onDelete="CASCADE" ORM mappings in entities to work,
     * we have to enable foreign keys by firing this query after connecting to SQLite.
     */
    public function postLoad(LifecycleEventArgs $args) {
        $args->getEntityManager()
        ->createNativeQuery('PRAGMA foreign_keys = ON;', new ResultSetMapping())
        ->getResult();
    }

}