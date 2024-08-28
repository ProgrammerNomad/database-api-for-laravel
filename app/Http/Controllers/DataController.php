<?php

namespace App\Http\Controllers;

use App\Models\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Now get data from builwith API
die($results->technology);
        $response = Http::get('https://api.builtwith.com/lists11/api.json?KEY=6aaa8a5d-a749-4643-833b-faeb235e05de&TECH=Joomla!&META=yes');

        if ($response->successful()) {
            $xdata = $response->json(); // Decode JSON response

            // Now you can work with the $xdata array/object
            // For example, access a specific field:
            $someValue = $xdata['some_field'];

            // Or iterate over an array of results:
            foreach ($xdata['results'] as $result) {
                // ... do something with each $result
            }
        } else {
            // Handle API errors gracefully
            // Log the error, return an error view, etc.
        }


        // check for existing data in the database


        // if data exists, update the data for only email and phone number


        // Save offset to database so that we can continue from where we stopped


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
