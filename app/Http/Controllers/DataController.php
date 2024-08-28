<?php

namespace App\Http\Controllers;

use App\Models\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;

class DataController extends Controller
{
    public function index()
    {

        $Server = env('SERVER');
        $BUILTWITH_API_KEY = env('BUILTWITH_API_KEY');

        $results = DB::table('technologies')
            ->select('id', 'technology', 'offset')
            ->where('status', '=', 0)
            ->where('server', '=', $Server)
            ->where('offset', '!=', 'END')
            ->first();

        $NotInArray = array('END', 0, null, '');

        // Now get data from builwith API
        if (!in_array($results->offset, $NotInArray)) {

            $Parameters = '&OFFSET=' . $results->offset;

        } else {

            $Parameters = '&META=yes';

        }

        $ApiUrl = 'https://api.builtwith.com/lists11/api.json?KEY=' . $BUILTWITH_API_KEY . '&TECH=' . $results->technology . '' . $Parameters;

        //echo $ApiUrl;

        //die();

        $response = Http::get($ApiUrl);

        if ($response->successful()) {
            $xdata = $response->json(); // Decode JSON response

            // Now you can work with the $xdata array/object

            $offset = $xdata['NextOffset'];

            // Or iterate over an array of results:
            foreach ($xdata['Results'] as $result) {

                $newData = array();

                $domain = $result['D'];

                $newData['domain'] = $result['D'];
                $newData['Social'] = $result['META']['Social'] ?? []; // JSON data
                $newData['CompanyName'] = $result['META']['CompanyName'] ?? '';
                $newData['Telephones'] = $result['META']['Telephones'] ?? []; // JSON data
                $newData['Emails'] = $result['META']['Emails'] ?? []; // JSON data
                $newData['Titles'] = $result['META']['Titles'] ?? []; // JSON data
                $newData['State'] = $result['META']['State'] ?? '';
                $newData['Postcode'] = $result['META']['Postcode'] ?? '';
                $newData['Country'] = $result['META']['Country'] ?? '';
                $newData['Vertical'] = $result['META']['Vertical'] ?? '';

                // if domain exists then update else insert new row
                //echo '<pre>';
                //print_r($result);
                //echo '</pre>';
                $this->updateOrInsertData($domain, $newData);

            }
        } else {
            // Handle API errors gracefully
            // Log the error, return an error view, etc.
        }

        // Save offset to database so that we can continue from where we stopped

        DB::table('technologies')
            ->where('id', $results->id)
            ->update([
                'offset' => $offset,
                'updated_at' => now(),
            ]);


        // After the loop or data processing is complete:
        if (Schema::hasColumn('data', 'domain')) {
            if (!Schema::hasIndex('data', 'domain')) {
                DB::statement('ALTER TABLE `data` ADD INDEX(`domain`)');
            }
        } else {
            // Handle case where the 'domain' column doesn't exist
            \Log::error("Column 'domain' not found in table 'data'");
        }

        return response()->json($results);
    }

    public function updateOrInsertData($domain, $newData)
    {
        if (Schema::hasColumn('data', 'domain')) {
            $existingData = DB::table('data')
                ->select('Social', 'Telephones', 'Emails', 'Titles')
                ->where('domain', $domain)
                ->first();

            if ($existingData) {
                // Decode JSON data from each column
                $socialData = json_decode($existingData->Social, true) ?? [];
                $telephonesData = json_decode($existingData->Telephones, true) ?? [];
                $emailsData = json_decode($existingData->Emails, true) ?? [];
                $titlesData = json_decode($existingData->Titles, true) ?? [];

                // Merge new data with existing data for each column
                $mergedSocial = array_unique(array_merge($socialData, $newData['Social'] ?? []));
                $mergedTelephones = array_unique(array_merge($telephonesData, $newData['Telephones'] ?? []));
                $mergedEmails = array_unique(array_merge($emailsData, $newData['Emails'] ?? []));
                $mergedTitles = array_unique(array_merge($titlesData, $newData['Titles'] ?? []));

                DB::table('data')
                    ->where('domain', $domain)
                    ->update([
                        'Social' => json_encode($mergedSocial),
                        'CompanyName' => $newData['CompanyName'] ?? '',
                        'Telephones' => json_encode($mergedTelephones),
                        'Emails' => json_encode($mergedEmails),
                        'Titles' => json_encode($mergedTitles),
                        'State' => $newData['State'] ?? '',
                        'Postcode' => $newData['Postcode'] ?? '',
                        'Country' => $newData['Country'] ?? '',
                        'Vertical' => $newData['Vertical'] ?? '',
                        'updated_at' => now(),
                    ]);
            } else {
                // Insert new record if it doesn't exist
                DB::table('data')->insert([
                    'domain' => $domain,
                    'Social' => json_encode($newData['Social'] ?? []),
                    'CompanyName' => $newData['CompanyName'] ?? '',
                    'Telephones' => json_encode($newData['Telephones'] ?? []),
                    'Emails' => json_encode($newData['Emails'] ?? []),
                    'Titles' => json_encode($newData['Titles'] ?? []),
                    'State' => $newData['State'] ?? '',
                    'Postcode' => $newData['Postcode'] ?? '',
                    'Country' => $newData['Country'] ?? '',
                    'Vertical' => $newData['Vertical'] ?? '',
                    'created_at' => now(),
                ]);
            }
        } else {
            // Handle case where the 'domain' column doesn't exist
            // You might want to log an error or create the column
            // For example:
            \Log::error("Column 'domain' not found in table 'data'");
        }
    }

    public function store(Request $request)
    {
        $data = Data::create($request->all());
        return response()->json($data, 201);
    }

    public function show($id)
    {
        $data = Data::find($id);
        if (is_null($data)) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $data = Data::find($id);
        if (is_null($data)) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        $data->update($request->all());
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = Data::find($id);
        if (is_null($data)) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        $data->delete();
        return response()->json(null, 204);
    }
}
