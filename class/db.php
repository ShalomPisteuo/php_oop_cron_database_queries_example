<?php

class DBH {

  # database connection variables
  private $dsn = 'mysql:host=<host name here>;dbname=<db name here>';
  private $username = '<db username here>';
  private $password = '<db password here>';

  protected $connection;
  
  # csv file variables
  protected $fileName, $fileLocation, $csvFile;
  
  
  function __construct() {
    $this->connect();
    
    # prepare our csv file
    $this->fileName = 'ATS-VSR-' . date('d-m-Y') . '.csv';
    $this->fileLocation = realpath($_SERVER["DOCUMENT_ROOT"]) . '/ats-auto/reports/';
    $this->csvFile = fopen($this->fileLocation . $this->fileName, 'a');
  }
  
  function __destruct() {
    
    # close csv connection
    fclose($this->csvFile);
    
    # email csv file to the printer
    $this->emailCSV();
    
    # close database connection - not really needed
    $this->disconnect();
    
  }
  
  private function connect() {
    try {
      $this->connection = new PDO($this->dsn, $this->username, $this->password);
    } catch( PDOException $e ) {
      # log connection issues in log files and notify via email
      $logFile = realpath($_SERVER["DOCUMENT_ROOT"]) . '/ats-auto/logs/dbconnection.log';
      $errorMsg = array(
        'email' => "Hello! \n\n Administrator for Atlantic Tire VSR card database queries. Please examine $logFile We seem to be having technical difficulties. \n\n Regards, \n Friendly PHP Script",
        'log' =>  date('d/m/Y == H:i:s') . ' ' . $e->getMessage() . "\n"
      );
      error_log($errorMsg['log'], 3, $logFile);
      error_log($errorMsg['email'], 1, "digitalteam@sacherokee.com");
    }
    
  }
  
  public function disconnect() {
    $this->connection = null;
  }
  
  public function oilChangeSelect() {
    
    # set $serviceType variable for passing to csv file
    $serviceType = 'Oil Change';
    
    # set previous time period variables for this service type
    $dateArray = array(
      'first_day' => date( 'Y-m-01', strtotime( '-3 month' ) ),
      'last_day' => date( 'Y-m-t', strtotime( '-3 month' ) )
    );
    
    # build our SELECT statement
    $query = $this->connection->query("
      SELECT DISTINCT  customers.aa_id,  customers.first,  customers.last,  customers.other_first,  customers.street,  customers.city,  customers.state,  customers.zip,  customers.is_mailable , vehicles.model, vehicles.make, vehicles.submake
      FROM  customers
        JOIN  invoice
          ON  invoice.customer_id = customers.aa_id
        JOIN vehicles
          ON vehicles.tag = invoice.vehicle_id
      WHERE customers.non_human_name =  ''
        AND  customers.first !=  ''
        AND  customers.last !=  ''
        AND  customers.street !=  ''
        AND  customers.city !=  ''
        AND  customers.state !=  ''
        AND  customers.zip !=  ''
        AND  customers.is_mailable !=  'N'
        AND  invoice.line_items LIKE '%OIL, LUBE & FILTER-LABOR%'
        AND  invoice.invoice_date
          BETWEEN '" . $dateArray['first_day'] . "'
          AND '" .$dateArray['last_day'] . "'"
    );
    
    # setting the fetch mode
    $query->setFetchMode(PDO::FETCH_ASSOC);

    # fetch our data from the database
    $results = $query->fetchAll();
    
    # if we have $results put contents in csv file
    if (count($results) > 0) {
      $this->editCSV($results, $serviceType);
    }
    
  }
  
  public function stateInspectionSelect() {
    
    # set $serviceType variable for passing to csv file
    $serviceType = 'State Inspection';
    
    # set previous time period variables for this service type
    $dateArray = array(
      'first_day' => date( 'Y-m-01', strtotime( '-11 month' ) ),
      'last_day' => date( 'Y-m-t', strtotime( '-11 month' ) )
    );
    
    # build our SELECT statement
    $query = $this->connection->query("
      SELECT DISTINCT  customers.aa_id,  customers.first, customers.last, customers.other_first, customers.street,  customers.city, customers.state, customers.zip, customers.is_mailable , vehicles.model, vehicles.make, vehicles.submake
      FROM  customers
        JOIN  invoice
          ON  invoice.customer_id = customers.aa_id
        JOIN vehicles
          ON vehicles.tag = invoice.vehicle_id
      WHERE  customers.non_human_name =  ''
        AND  customers.first !=  ''
        AND  customers.last !=  ''
        AND  customers.street !=  ''
        AND  customers.city !=  ''
        AND  customers.state !=  ''
        AND  customers.zip !=  ''
        AND  customers.is_mailable =  'Y'
        AND
          (
            invoice.line_items LIKE '%NC STATE     INSPECTION%'
            OR  invoice.line_items LIKE '%NC STATE INSPECTION%'
            OR  invoice.line_items LIKE '%NCSI%'
          )
        AND  invoice.invoice_date
          BETWEEN '" . $dateArray['first_day'] . "'
          AND '" .$dateArray['last_day'] . "'"
    );
    
    # setting the fetch mode
    $query->setFetchMode(PDO::FETCH_ASSOC);

    # fetch our data from the database
    $results = $query->fetchAll();
    
    # if we have $results put contents in csv file
    if (count($results) > 0) {
      $this->editCSV($results, $serviceType);
    }
    
  }
  
  public function tireRotationSelect() {
    
    # set $serviceType variable for passing to csv file
    $serviceType = 'Tire Rotation';
    
    # set previous time period variables for this service type
    $dateArray = array(
      'first_day' => date( 'Y-m-01', strtotime( '-6 month' ) ),
      'last_day' => date( 'Y-m-t', strtotime( '-6 month' ) )
    );
    
    # build our SELECT statement
    $query = $this->connection->query("
      SELECT DISTINCT  customers.aa_id, customers.first, customers.last, customers.other_first, customers.street, customers.city, customers.state, customers.zip, customers.is_mailable , vehicles.model, vehicles.make, vehicles.submake
      FROM  customers
        JOIN  invoice
          ON  invoice.customer_id = customers.aa_id
        JOIN vehicles
          ON vehicles.tag = invoice.vehicle_id
      WHERE  customers.non_human_name =  ''
        AND  customers.first !=  ''
        AND  customers.last !=  ''
        AND  customers.street !=  ''
        AND  customers.city !=  ''
        AND  customers.state !=  ''
        AND  customers.zip !=  ''
        AND  customers.is_mailable =  '1'
        AND
        (
          invoice.line_items LIKE '%TIRE ROTATION+ BALANCE%'
        )
        AND  `invoice`.`invoice_date`
          BETWEEN '" . $dateArray['first_day'] . "'
          AND '" .$dateArray['last_day'] . "'"
    );
    
    # setting the fetch mode
    $query->setFetchMode(PDO::FETCH_ASSOC);

    # fetch our data from the database
    $results = $query->fetchAll();
    
    # if we have $results put contents in csv file
    if (count($results) > 0) {
      $this->editCSV($results, $serviceType);
    }
    
  }
  
  protected function editCSV($results, $serviceType) {
    
    $data = $results;
    $service = $serviceType;
    
    # write the csv file contents
    foreach ($data as $row) {
      unset($row['aa_id'], $row['is_mailable']);
      $row['service'] = $service;
      fputcsv($this->csvFile, $row);
    }
  }
  
  /*
   *
   * Email the VSR card recipients to the printer for mailing
   * 
   */
  protected function emailCSV() {
    
    # pull in PHPMailer to simplify the email task
    require_once( realpath($_SERVER["DOCUMENT_ROOT"]) . '/ats-auto/library/phpmailer/class.phpmailer.php');
    
    # create our mail object and define details
    $email = new PHPMailer();
    $email->From      = '<from email address>';
    $email->FromName  = '<from name>';
    $email->Subject   = '<email subject>';
    $email->Body      = "<email body>";
    $email->AddAddress( '<to address>' );
    $email->AddCC( '<cc\'d address' );
    
    # attach the report file
    $email->AddAttachment( $this->fileLocation . $this->fileName, $this->fileName );
    
    $email->Send();
  }
  
}

?>