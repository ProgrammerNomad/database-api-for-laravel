<?php
namespace App\Http\Controllers;

use App\Models\Technology;
use Illuminate\Http\Request;

class TechnologyController extends Controller
{
    public function index()
    {
        $technologies = Technology::all();
        return response()->json($technologies);
    }

    public function store(Request $request)
    {
        $technology = Technology::create($request->all());
        return response()->json($technology, 201);
    }

    public function show($id)
    {
        $technology = Technology::find($id);
        if (is_null($technology)) {
            return response()->json(['message' => 'Technology not found'], 404);
        }
        return response()->json($technology);
    }

    public function update(Request $request, $id)
    {
        $technology = Technology::find($id);
        if (is_null($technology)) {
            return response()->json(['message' => 'Technology not found'], 404);
        }
        $technology->update($request->all());
        return response()->json($technology);
    }

    public function destroy($id)
    {
        $technology = Technology::find($id);
        if (is_null($technology)) {
            return response()->json(['message' => 'Technology not found'], 404);
        }
        $technology->delete();
        return response()->json(null, 204);
    }
}