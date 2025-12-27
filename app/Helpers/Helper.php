<?php

use App\Models\Admin;
use App\Models\CertificateApprovalDate;
use App\Models\CertificateData;
use App\Models\Degree;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\TranscriptOfficial;
use App\Models\TranscriptsImport;
use App\Models\TranscriptsRequest;
use App\Services\GoogleSheetService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

function greetings(){
    $now = Carbon::now(); $message = ""; 
    if($now->hour < 12 ) { $message = "Morning"; }
    else if($now->hour < 18 ) { $message = "Afternoon"; }
    else { $message = "Evening"; }
    return "Good $message : "; 
}

 function admin_info($admin_id){
    $user = Admin::find($admin_id);   
    return ['fullname'=>$user->title.' '.$user->surname.',  '.$user->firstname.' '.$user->othername,
      'mobile'=>$user->mobile,'email'=>$user->email,'regno'=>$user->regno];     
}
       

function count_new_transcript_request(){
    $history = TranscriptsImport::latest()->first();            
    $newRow = (empty($history)) ? 2 :  2 + $history['cum_total'];
    try{
        # connect to google sheet and get new records           
        $range = "Sheet1!A{$newRow}:A";
        $service = new GoogleSheetService($range);           
        $counts = $service->countRows(); 
        return $counts;
     }
    catch (Exception $e) {
        Log::error("Google OAuth Token Error: ".$e->getMessage());
        return '--:--'; // Graceful fail   
       }
}

function count_total_transcript_request(){          
    $counts = TranscriptsRequest::where('bodies','postgraduate')->count();
    return $counts;
}

function count_total_completed_request(){          
    $counts = TranscriptsRequest::where('request_status','treated')->count();
    return $counts;
}

function last_imported_transcript_request(){          
    $counts = TranscriptsImport::latest()->first();;
    return $counts->rows;
}

 function get_current_approve_date(){
     $curr_approve_date = CertificateApprovalDate::where('is_current',1)->first();
     return $curr_approve_date ?? ''; 
 }
 
 function swapNameB4(string $raw_name){    
    // Normalize white spaces
    $raw_name1 = str_replace("(F)", "", $raw_name);
    $raw_name2 = str_replace(",", "", $raw_name1);
    $fullName = trim(preg_replace('/\s+/', ' ', $raw_name2));

    // Match name parts including hyphens, apostrophes, accents
    preg_match_all('/[\p{L}\p{M}\'\-]+/u', $fullName, $matches);
    $parts = $matches[0];

    $firstName = $middleName = $lastName = '';

    // Handle based on part count
    if (count($parts) === 1) {
        $firstName = $parts[0];
    } elseif (count($parts) === 2) {
        $lastName  = $parts[0];
        $firstName = $parts[1];
    } elseif (count($parts) >= 3) {
        $lastName   = $parts[0]; // Assume surname first
        $firstName  = $parts[1];
        $middleName = implode(' ', array_slice($parts, 2));
    }

    $result =  [
        'first_name'  => ucwords(strtolower($firstName)),        
        'middle_name' => ucwords(strtolower($middleName)),
        'surname'     => strtoupper(strtolower($lastName)),       
    ];
    
    return implode(" ", $result); 
 }
 
 function swapName(string $raw_name): string {
    // Remove markers/punctuation you don't want and normalize spaces
    $fullName = trim(preg_replace('/\s+/', ' ', str_replace(['(F)', ','], '', $raw_name)));

    // Match letters incl. accents + apostrophes/hyphens
    preg_match_all('/[\p{L}\p{M}\'\-]+/u', $fullName, $matches);
    $parts = $matches[0];

    $first = $middle = $last = '';

    $count = count($parts);
    if ($count === 1) {
        $first = $parts[0];
    } elseif ($count === 2) {
        $last  = $parts[0];     // assume "SURNAME First"
        $first = $parts[1];
    } else { // 3+
        $last   = array_shift($parts); // surname first
        $first  = array_shift($parts);
        $middle = implode(' ', $parts); // everything else is middle
    }

    // Unicode-safe casing
    $firstF  = $first  !== '' ? mb_convert_case(mb_strtolower($first,  'UTF-8'), MB_CASE_TITLE, 'UTF-8') : '';
    $middleF = $middle !== '' ? mb_convert_case(mb_strtolower($middle, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : '';
    $lastF   = $last   !== '' ? mb_strtoupper($last, 'UTF-8') : '';

    // Only include non-empty parts (prevents extra spaces when there’s no middle name)
    $out = array_values(array_filter([$firstF, $middleF, $lastF], static fn($v) => $v !== ''));

    return implode(' ', $out);
}

// for reverse of name
function smartSwapName(string $raw_name): string {
    $fullName = trim(preg_replace('/\s+/', ' ', str_replace(['(F)', ','], '', $raw_name)));
    preg_match_all('/[\p{L}\p{M}\'\-]+/u', $fullName, $matches);
    $parts = $matches[0];

    $count = count($parts);

    if ($count === 2) {
        // Just flip
        $first = mb_convert_case(mb_strtolower($parts[0], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $last  = mb_strtoupper($parts[1], 'UTF-8');
        return "$last $first";
    }

    if ($count > 2) {
        // Assume "First Middle Last"
        $first  = mb_convert_case(mb_strtolower($parts[0], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $last   = mb_strtoupper(array_pop($parts), 'UTF-8');
        $middle = implode(' ', array_map(fn($p) =>
            mb_convert_case(mb_strtolower($p, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'), array_slice($parts, 1)
        ));

        return trim("$last $first $middle");
    }

    // Single word (just return as-is)
    return $fullName;
}
 function highlightDifference($expected, $actual) {
    $expectedChars = mb_str_split($expected);
    $actualChars = mb_str_split($actual);

    $output = '';
    $len = max(count($expectedChars), count($actualChars));

    for ($i = 0; $i < $len; $i++) {
        $eChar = $expectedChars[$i] ?? '';
        $aChar = $actualChars[$i] ?? '';

        if ($eChar === $aChar) {
            $output .= $eChar; // match – keep as is
        } else {
            $output .= '<span class="text-danger">' . ($aChar ?: '_') . '</span>';
        }
    }

    return $output;
}

function text_diff(string $old, string $new): array
{
    // Find common prefix length
    $commonPrefixLen = strspn($old ^ $new, "\0");

    // Find common suffix length
    $commonSuffixLen = strspn(strrev($old) ^ strrev($new), "\0");

    $oldEnd = strlen($old) - $commonSuffixLen;
    $newEnd = strlen($new) - $commonSuffixLen;

    // Extract the different parts
    $start     = htmlspecialchars(substr($new, 0, $commonPrefixLen), ENT_QUOTES, 'UTF-8');
    $end       = htmlspecialchars(substr($new, $newEnd), ENT_QUOTES, 'UTF-8');
    $added     = htmlspecialchars(substr($new, $commonPrefixLen, $newEnd - $commonPrefixLen), ENT_QUOTES, 'UTF-8');
    $removed   = htmlspecialchars(substr($old, $commonPrefixLen, $oldEnd - $commonPrefixLen), ENT_QUOTES, 'UTF-8');

    // Determine output
    if ($added === '' && $removed === '') {
        return ['old' => '', 'new' => ''];
    }

    $newHtml = "{$start}<span class='bg-success font-weight-bold; text-white'>{$added}</span>{$end}";
    $oldHtml = "{$start}<span class='bg-primary text-white'>{$removed}</span>{$end}";

    return [
        'old' => $oldHtml,
        'new' => $newHtml,
    ];
}

    function extractDegreeInfo($text){
        $degrees = Degree::all()->sortByDesc(function ($degree) {
            return strlen($degree->short_name);
        });

        foreach ($degrees as $degree) {
            if (str_starts_with($text, $degree->short_name)) {
                $field = trim(substr($text, strlen($degree->short_name)));
                return [
                    'id' => $degree->id,
                    'acronymn' => $degree->short_name,
                    'name'=>$degree->full_name,
                    'field' => $field,
                ];
            }
        }
    return [
        'degree_id' => null,
        'field' => $text,
    ];
}

//function extractDegreeInfo($text)
//{
//    $originalText = trim($text);
//
//    // Auto-detect full written prefixes
//    $autoDegrees = [
//        'Master of'   => 'M.',
//        'Bachelor of' => 'B.',
//        'Doctor of'   => 'D.',
//    ];
//
//    // Step A: Convert natural language degrees into short form
//    foreach ($autoDegrees as $full => $short) {
//        if (str_starts_with($originalText, $full)) {
//            // Replace in the string
//            $originalText = preg_replace("/^" . preg_quote($full, '/') . "/i", $short, $originalText);
//            break;
//        }
//    }
//
//    // Step B: Use existing database recognition
//    $degrees = Degree::all()->sortByDesc(function ($degree) {
//        return strlen($degree->short_name);
//    });
//
//    foreach ($degrees as $degree) {
//        if (str_starts_with($originalText, $degree->short_name)) {
//            $field = trim(substr($originalText, strlen($degree->short_name)));
//            return [
//                'degree_id' => $degree->id,
//                'acronym'   => $degree->short_name,
//                'name'      => $degree->full_name,
//                'field'     => $field,
//            ];
//        }
//    }
//
//    // If nothing matches
//    return [
//        'degree_id' => null,
//        'acronym'   => null,
//        'name'      => null,
//        'field'     => $originalText,
//    ];
//}

function formatProgrammeName($programme)
{
    $text = trim($programme);
    $degrees = Degree::where('status', 1)
        ->orderByRaw('LENGTH(short_name) DESC')
        ->get();

    foreach ($degrees as $degree) {
        if (str_starts_with($text, $degree->short_name)) {
            // Extract the field part
            $field = trim(substr($text, strlen($degree->short_name)));

            if ($degree->category === 'professional') {
                // Professional => use full_name + prefix + field
                $prefix = trim($degree->prefix ?? 'of');
                return trim("{$degree->full_name} {$prefix} {$field}");
            } else {
                // Normal => use short_name + field
                return trim("{$degree->short_name} {$field}");
            }
        }
    }

    // If no match found, return original text
    return $text;
}


 function program_available($deg_id,$name){
     $exists = Programme::where('degree_id',$deg_id)
             ->where('name',$name)
             ->exists();
      if(!$exists):
           return [
                'available'=>false,                
            ];
          else:
              $info = Programme::where('degree_id',$deg_id)
             ->where('name',$name)->first();
          return [
                'available'=>true,
                'id'=>$info->id
            ];     
      endif;     
 }
 
 function programmeConfigured($rawProgramme){
     $approve_date_id = get_current_approve_date(); // get the approved date id 
     $prog_info = CertificateData::select('degree_id','programme_id')->where('raw_programme',$rawProgramme)
             ->where('approve_date_id',$approve_date_id->id)->first();
         # print "<pre>"; print_r($prog_info); print "</pre>";
         # print "<pre>"; print_r($approve_date_id->id); print "</pre>";
     return empty($prog_info->programme_id) ? false : true;  # "<span class='text-danger font-weight-bold'>Not Configured </span> " : "<span class='text-success font-weight-bold'>Configured </span>"; 
 }

   function sanitizeFileName($filename)
    {
        // Allow letters, numbers, dots, hyphens, and underscores
        return preg_replace('/[^A-Za-z0-9.\-_]/u', '_', $filename);
    }
    
   function cleanAndParseDate(string $date): ?string
    {
    // Remove ordinal suffixes (st, nd, rd, th)
    $cleanDate = preg_replace('/\b(\d+)(st|nd|rd|th)\b/i', '$1', $date);
    // Remove the word "of"
    $cleanDate = str_ireplace(' of ', ' ', $cleanDate);
    // Remove commas (important for Carbon parsing)
    $cleanDate = str_replace(',', '', $cleanDate);
    // Force parse with specific formats
    $formats = [
        'd F Y',  // 12 October 2002
        'j F Y',  // 9 June 2002
        'd M Y',  // 12 Oct 2002
        'j M Y',  // 9 Jun 2002
    ];

    foreach ($formats as $format) {
        try {
            $dt = Carbon::createFromFormat($format, trim($cleanDate));
            if ($dt !== false) {
                # return $dt->format('n/j/Y');
                # return $dt->format('j/n/Y');
                return $dt->format('d/m/Y');
             // return $dt->toDateString(); // always YYYY-MM-DD
            }
        } catch (Exception $e) {
            continue;
        }
    }

    // If still failing, fallback to parse
    try {
        $dt = Carbon::parse($cleanDate);
          return $dt->format('n/j/Y');
        //return $dt->year > now()->year ? null : $dt->toDateString(); // prevent future years like 2025
    } catch (Exception $e) {
        return null;
    }
}

    function cleanAndParseDate2(string $date): ?string  {
        try {
            return Carbon::createFromFormat('Y-m-d', trim($date))->format('n/j/Y');
        } catch (Exception $e) {
            return null; // return null if parsing fails
        }
    }
    
    function get_full_approve_date($id,$type=''){
        $date = CertificateApprovalDate::findOrFail($id);
       switch($type):
           case "normal": return Carbon::parse($date->app_date)->format('Y-m-d'); 
               break;
           default: return Carbon::parse($date->app_date)->format('D, jS F, Y'); 
               break;
       endswitch;               
       
    }
    
    function surname($name){
        return $surname = explode(" ",$name)[0];
    }
    
    
    function othername($name){
         $names = explode(" ",$name);
         $tot = count($names); 
         if($tot==2):
             $othername = $names[1];
         else:
             $othername = $names[1]." ".$names[2];
         endif;
         return ucwords(strtolower($othername));
    }
    
    function fact_name($id){
        $fact = Faculty::where('fact_id',$id)->first();
        return $fact->name; 
    }
    function dept_name($id){
        $dept = Department::where('dept_id',$id)->first();
        return $dept->name;
    }
          
    
    function officials_name($id){
        $name = TranscriptOfficial::where('regno',$id)->first();
        return $name->name;
    }
    
   function count_rrr($number){
        $total = TranscriptsRequest::where('rrr',$number)->count();
        return $total; 
    }
    
    function rewrite_phd($programme){
        $break = explode("Ph.D.",$programme);
        return implode("(Ph.D.) in ",$break);
    }
    
    