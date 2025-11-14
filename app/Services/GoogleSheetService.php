<?php

namespace App\Services;
use Google\Client;
# use Google\Auth\HttpHandler\Guzzle6HttpHandler;
use Google\Service\Sheets;

class GoogleSheetService
{
    /**
     * Create a new class instance.
     */
     protected $client, $service;
     protected $spreadsheetId, $range; 

    public function __construct($range = "Sheet1!A1:AD31")
    {
         $this->client = new Client();
        $this->spreadsheetId = "1H6s8zo-bym20Dm2T7Mq3oBzjUOSdIO3C49naVeOm7lE";
        $this->client->useApplicationDefaultCredentials();         
        $this->client->addScope(Sheets::SPREADSHEETS_READONLY);             
        $this->service = new Sheets($this->client);
        $this->range = $range;        
    }
    
     public function readSheet()
        {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $this->range);
            return $response->getValues();
        }
        
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
    }
    
    
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
       
