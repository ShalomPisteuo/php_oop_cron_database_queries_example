<?php

  # load the DB class
  require_once('class/db.php');

  # make a connection to the database
  $connection = new DBH();
  
  # gather oil change customer data and place in csv
  $connection->oilChangeSelect();
  
  # gather state inspection customer data and place in csv
  $connection->stateInspectionSelect();
  
  # gather tire rotation customer data and place in csv
  $connection->tireRotationSelect();
    
?>