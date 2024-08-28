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

        $response = Http::get('https://api.builtwith.com/lists11/api.json?KEY=' . $BUILTWITH_API_KEY . '&TECH=' . $results->technology . '' . $Parameters);

        if ($response->successful()) {
            $xdata = $response->json(); // Decode JSON response

            // Now you can work with the $xdata array/object
            // For example, access a specific field:
            $offset = $xdata['NextOffset'];

            // Or iterate over an array of results:
            foreach ($xdata['Results'] as $result) {

                $newData = array();

                $domain = $result['D'];

                $newData['domain'] = $result['D'];
                $newData['Social'] = $result['D'];
                $newData['CompanyName'] = $result['D'];
                $newData['Telephones'] = $result['D'];
                $newData['Emails'] = $result['D'];
                $newData['Titles'] = $result['D'];
                $newData['State'] = $result['D'];
                $newData['Postcode'] = $result['D'];
                $newData['Country'] = $result['D'];
                $newData['Vertical'] = $result['D'];



                // if domain exists then update else insert new row
                echo '<pre>';
                print_r($result);
                echo '</pre>';
                //$this->updateOrInsertData($domain, $result);

            }
        } else {
            // Handle API errors gracefully
            // Log the error, return an error view, etc.
        }

        // Save offset to database so that we can continue from where we stopped


        return response()->json($results);
    }

    public function updateOrInsertData($domain, $newData)
    {
        if (Schema::hasColumn('data', 'domain')) {
            $existingData = DB::table('data')
                ->where('domain', $domain)
                ->first();

            if ($existingData) {
                // Decode JSON data from each column
                $socialData = json_decode($existingData->Social, true) ?? [];
                $telephonesData = json_decode($existingData->Telephones, true) ?? [];
                $emailsData = json_decode($existingData->Emails, true) ?? [];
                $titlesData = json_decode($existingData->Titles, true) ?? [];

                // Merge new data with existing data for each column
                $mergedSocial = array_merge($socialData, $newData['Social'] ?? []);
                $mergedTelephones = array_merge($telephonesData, $newData['Telephones'] ?? []);
                $mergedEmails = array_merge($emailsData, $newData['Emails'] ?? []);
                $mergedTitles = array_merge($titlesData, $newData['Titles'] ?? []);

                DB::table('data')
                    ->where('domain', $domain)
                    ->update([
                        'Social' => json_encode($mergedSocial),
                        'Telephones' => json_encode($mergedTelephones),
                        'Emails' => json_encode($mergedEmails),
                        'Titles' => json_encode($mergedTitles)
                    ]);
            } else {
                // Insert new record if it doesn't exist
                DB::table('data')->insert([
                    'domain' => $domain,
                    'Social' => json_encode($newData['Social'] ?? []),
                    'Telephones' => json_encode($newData['Telephones'] ?? []),
                    'Emails' => json_encode($newData['Emails'] ?? []),
                    'Titles' => json_encode($newData['Titles'] ?? []),
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
