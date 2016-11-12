<?php

interface IDatabaseModel {
    const DATABASEMODEL_SESSION = "config_dbmodels";

    /**
     * Use this method to register a new IDatabaseModel to the session using $_SESSION[$DATABASEMODEL_SESSION]['yourclassnamehere'].
     * This is neccessary to make it reachable in createDatabaseTables() call. Check out PollModel.php for a example implementation.
     */
    /* public function registerDatabaseModel(); */

    /**
     * Executes all database queries needed to set up the database tables necessary for a certain module.
     *
     * @param boolean $overwriteIfExists
     */
    public function createDatabaseTables($overwriteIfExists);
}