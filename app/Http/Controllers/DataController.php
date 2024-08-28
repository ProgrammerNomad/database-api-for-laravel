<?php

namespace App\Http\Controllers;

use App\Models\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function index()
    {


        $results = DB::table('technologies')
            ->select('technology', 'technology') // Specify columns you want to retrieve
            ->where('status', '=', 0)
            ->where('offset', '!=','END')
            //->orderBy('column_to_sort', 'asc') // Optional sorting
            ->first(); // Retrieve the results

       // $data = Data::all();
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
