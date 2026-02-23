<?php

namespace App\Http\Controllers;
use App\Models\Towns;
use Illuminate\Http\Request;

class TownController extends Controller
{
    /**
 * @api {get} /towns Települések listázása
 * @apiName ListTowns
 * @apiGroup Település
 * @apiSuccess {Object[]} towns A települések listája
 */
    // GET /api/towns
    public function index()
    {
        $towns = Towns::with('county:id,name')->get(['id', 'name', 'zip_code', 'county_id']);
        return response()->json(['towns' => $towns]);
    }
/**
 * @api {post} /towns Új település létrehozása
 * @apiName CreateTown
 * @apiGroup Település
 * @apiHeader {String} Authorization Bearer token (Sanctum)
 * @apiParam {String} name A település neve
 * @apiParam {Number} county_id A megye azonosítója
 * @apiSuccess {Number} id A település azonosítója
 * @apiSuccess {String} name A település neve
 */
    // POST /api/towns
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'zip_code' => 'required|string|unique:towns,zip_code',
            'county_id' => 'required|exists:counties,id',
        ]);

        $town = Towns::create($request->only(['name', 'zip_code', 'county_id']));

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['town' => $town], 201);
        }

        return back()->with('success', 'Town created successfully.');
    }
/**
 * @api {put} /towns/:id Település módosítása
 * @apiName UpdateTown
 * @apiGroup Település
 * @apiHeader {String} Authorization Bearer token (Sanctum)
 * @apiParam {String} name A település neve
 * @apiParam {Number} county_id A megye azonosítója
 * @apiSuccess {Number} id A település azonosítója
 * @apiSuccess {String} name A település neve
 */
    // PUT or PATCH /api/towns/{id}
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'zip_code' => 'required|string|unique:towns,zip_code,' . $id,
            'county_id' => 'required|exists:counties,id',
        ]);

        $town = Towns::findOrFail($id);
        $town->update($request->only(['name', 'zip_code', 'county_id']));

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['town' => $town]);
        }

        return back()->with('success', 'Town updated successfully.');
    }
/**
 * @api {delete} /towns/:id Település törlése
 * @apiName DeleteTown
 * @apiGroup Település
 * @apiHeader {String} Authorization Bearer token (Sanctum)
 * @apiSuccess {String} message Sikeres törlés üzenete
 */
    // DELETE /api/towns/{id}
    public function destroy(Request $request, $id)
    {
        $town = Towns::findOrFail($id);
        $town->delete();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Town deleted']);
        }

        return back()->with('success', 'Town deleted successfully.');
    }
/**
 * @api {get} /towns/:id Település lekérése
 * @apiName GetTown
 * @apiGroup Település
 * @apiHeader {String} Authorization Bearer token (Sanctum)
 * @apiSuccess {Number} id A település azonosítója
 * @apiSuccess {String} name A település neve
 */
    // GET /api/towns/3?Lookup-Type=id 
    public function show(Request $request, $id = null)
    {
        if ($id === null) {
            $id = $request->query('id', $request->query('name', $request->query('zip_code')));
        }

        if ($id === null) {
            return response()->json(['message' => 'Town not found'], 404);
        }

        $lookupType = $request->header('Lookup-Type', null); 
        if (!$lookupType) {
            // autodetect
            if (is_numeric($id)) {
                $lookupType = 'id';
            } else {
                $lookupType = 'name';
            }
        }
        if ($lookupType === 'name') {
            $search = strtolower(trim($id));
            $towns = Towns::whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])->get();
        } elseif ($lookupType === 'zip_code') {
            $towns = Towns::where('zip_code', $id)->get();
        } else {
            $towns = Towns::where('id',$id)->get();
        }
    
        if ($towns->isEmpty()) {
            return response()->json(['message' => 'Town not found'], 404);
        }
    
        return response()->json(['towns' => $towns]);
    }
    
    

}
