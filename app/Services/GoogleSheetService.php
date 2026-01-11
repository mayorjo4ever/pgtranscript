<?php

namespace App\Services;
# use Google\Auth\HttpHandler\Guzzle6HttpHandler;


use Exception;
use Google\Client;
use Google\Service\Sheets;
use function config;

class GoogleSheetService
{
    /**
     * Create a new class instance.
     */
     protected $client, $service;
     protected $spreadsheetId, $sheetName, $range; 
     
     
     public function __construct(string $formKey)
    {
        $this->spreadsheetId = config("google-sheets.$formKey.spreadsheet_id");
        $this->sheetName     = config("google-sheets.$formKey.sheet_name");

        if (!$this->spreadsheetId || !$this->sheetName) {
            throw new Exception("Google Sheet config missing for [$formKey]");
        }

        $this->client = new Client();
        $this->client->useApplicationDefaultCredentials();
        $this->client->addScope(Sheets::SPREADSHEETS_READONLY);

        $this->service = new Sheets($this->client);
    }

     public function read(string $range = null)
    {
        $fullRange = $range
            ? "{$this->sheetName}!{$range}"
            : "{$this->sheetName}!A2:Z";

        $response = $this->service
            ->spreadsheets_values
            ->get($this->spreadsheetId, $fullRange);

        return $response->getValues() ?? [];
    }

    /**
     * Count rows within a range
     */
    public function countRows(string $range = null)
    {
        return count($this->read($range));
    }

    public function readInChunks(int $startRow, int $chunkSize, string $endColumn = 'Z')
    {
        $rows = [];
        $currentRow = $startRow;

        while (true) {
            $endRow = $currentRow + $chunkSize - 1;
            $range = "A{$currentRow}:{$endColumn}{$endRow}";

            $chunk = $this->read($range);

            if (empty($chunk)) {
                break;
            }

            $rows = array_merge($rows, $chunk);
            $currentRow = $endRow + 1;
        }

        return $rows;
    }
     
     
     /** refactor 
    public function __construct($range = "Sheet1!A1:AD31")
    {
        $this->client = new Client();
        $this->spreadsheetId = "1H6s8zo-bym20Dm2T7Mq3oBzjUOSdIO3C49naVeOm7lE";
        $this->client->useApplicationDefaultCredentials();         
        $this->client->addScope(Sheets::SPREADSHEETS_READONLY);             
        $this->service = new Sheets($this->client);
        $this->range = $range;        
    }
    **/
     
     /**  new constructor for multiple forms
      public function __construct(string $spreadsheetId, string $sheetName)
        {
            $this->spreadsheetId = $spreadsheetId;
            $this->sheetName = $sheetName;

            $this->client = new Client();
            $this->client->useApplicationDefaultCredentials();
            $this->client->addScope(Sheets::SPREADSHEETS_READONLY);

            $this->service = new Sheets($this->client);
        }
        
        
    
      *  refactor too      
      public function readSheet()
        {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $this->range);
            return $response->getValues();
        }
       
        
         public function read(string $range = 'A1:AD31')
            {
                $fullRange = "{$this->sheetName}!{$range}";

                $response = $this->service
                    ->spreadsheets_values
                    ->get($this->spreadsheetId, $fullRange);

                return $response->getValues() ?? [];
            }
     
     public function countRows()
        {
            return count($this->read());
        }
 */ 
     /****
    public function readInChunks(int $startRow, int $chunkSize, string $endColumn = 'Z')
    {
        $rows = [];
        $currentRow = $startRow;

        while (true) {
            $endRow = $currentRow + $chunkSize - 1;
            $range = "A{$currentRow}:{$endColumn}{$endRow}";

            $chunk = $this->read($range);

            if (empty($chunk)) {
                break;
            }

            $rows = array_merge($rows, $chunk);
            $currentRow = $endRow + 1;
        }

        return $rows;
    }
            
      refactored 
     public function countRows()
        {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $this->range);
            return !empty($response->getValues())?count($response->getValues()):0 ;
        }
        
        
        
    public function readSheetInChunks($startRow, $chunkSize, $totalColumns)
    {
        $rows = [];
        $currentRow = $startRow;
        
        while (true) {
            $endRow = $currentRow + $chunkSize - 1;
            $chunk_range = "Sheet1!A{$currentRow}:{$totalColumns}{$endRow}";
            $chunk = $this->readSheet($this->spreadsheetId, $chunk_range);
            
            if (empty($chunk)) {
                break;
            }

            $rows = array_merge($rows, $chunk);
            $currentRow = $endRow + 1;
        }

        return $rows;
    } ***/
    
    
        public function writeSheet($values) {
            
            $body = new valueRange([
                'values'=>$values
            ]);
            $params = [
                'valueInputOption'=>'Raw'
            ]; 
            
            $result = $this->service->spreadsheets_values->update(
                    $this->spreadsheetId,$this->range,$body,$params 
             );
            
        }
}   
       
