<?php

namespace App\Http\Controllers;

use App\Models\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;

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

        // Start Writer 

        $writer = Writer::createFromPath(env('CSV_DIR') . '/' . $results->technology . '.csv', 'w+');

        // Now get data from builwith API
        if (!in_array($results->offset, $NotInArray)) {

            $Parameters = '&OFFSET=' . $results->offset . '&META=yes';

        } else {

            $Parameters = '&META=yes';

            // Create header of CSV File



            $writer->insertOne(['domain', 'Social', 'CompanyName', 'Telephones', 'Emails', 'Titles', 'State', 'Postcode', 'Country', 'Vertical', 'Technologies']);

        }

        $ApiUrl = 'https://api.builtwith.com/lists11/api.json?KEY=' . $BUILTWITH_API_KEY . '&TECH=' . $results->technology . '' . $Parameters;

        echo $ApiUrl;

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

                $newData[] = $result['D'];
                $newData[] = implode("|", array_unique($result['META']['Social'] ?? [])); // JSON data
                $newData[] = $result['META']['CompanyName'] ?? '';
                $newData[] = implode("|", array_unique($result['META']['Telephones'] ?? [])); // JSON data
                $newData[] = implode("|", array_unique($result['META']['Emails'] ?? [])); // JSON data
                $newData[] = implode("|", array_unique($result['META']['Titles'] ?? [])); // JSON data
                $newData[] = $result['META']['State'] ?? '';
                $newData[] = $result['META']['Postcode'] ?? '';
                $newData[] = $result['META']['Country'] ?? '';
                $newData[] = $result['META']['Vertical'] ?? '';
                $newData[] = $results->technology;

                //Save to CSV
                $writer->insertOne($newData);

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

        return response()->json($results);
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
