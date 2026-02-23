<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Laravel\Pail\ValueObjects\Origin\Console;
use App\Models\Counties;
class CountyController extends Controller{
    /**
 * @api {get} /counties Megyék listázása
 * @apiName ListCounties
 * @apiGroup Megye
 * @apiSuccess {Object[]} counties A megyék listája
 */
    public function index()
    {
        $counties = Counties::all(['id', 'name']);
        return response()->json(['counties' => $counties]);
    }
    /**
 * @api {post} /counties Új megye létrehozása
 * @apiName CreateCounty
 * @apiGroup Megye
 * @apiHeader {String} Authorization Bearer token (Sanctum)
 * @apiParam {String} name A megye neve
 * @apiSuccess {Number} id A megye azonosítója
 * @apiSuccess {String} name A megye neve
 */

    // POST /api/counties
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:counties,name',
        ]);

        $county = Counties::create(['name' => $request->name]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['county' => $county], 201);
        }

        return back()->with('success', 'County created successfully.');
    }
/**
 * @api {put} /counties/:id Megye módosítása
 * @apiName UpdateCounty
 * @apiGroup Megye
 * @apiHeader {String} Authorization Bearer token (Sanctum)
 * @apiParam {String} name A megye neve
 * @apiSuccess {Number} id A megye azonosítója
 * @apiSuccess {String} name A megye neve
 */
    // PUT or PATCH /api/counties/{id}
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:counties,name,' . $id,
        ]);

        $county = Counties::findOrFail($id);
        $county->name = $request->name;
        $county->save();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['county' => $county]);
        }

        return back()->with('success', 'County updated successfully.');
    }
/**
 * @api {delete} /counties/:id Megye törlése
 * @apiName DeleteCounty
 * @apiGroup Megye
 * @apiHeader {String} Authorization Bearer token (Sanctum)
 * @apiSuccess {String} message Sikeres törlés üzenete
 */
    // DELETE /api/counties/{id}
    public function destroy(Request $request, $id)
    {
        $county = Counties::findOrFail($id);
        $county->delete();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'County deleted']);
        }

        return back()->with('success', 'County deleted successfully.');
    }
/**
 * @api {get} /counties/:id Megye lekérése
 * @apiName GetCounty
 * @apiGroup Megye
 * @apiHeader {String} Authorization Bearer token (Sanctum)
 * @apiSuccess {Number} id A megye azonosítója
 * @apiSuccess {String} name A megye neve
 */
public function show(Request $request, $id = null)
{
    if ($id === null) {
        $id = $request->query('id', $request->query('name'));
    }

    if ($id === null) {
        return response()->json(['message' => 'County not found'], 404);
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
        // kisbetűs összehasonlítás, szóköz eltávolítás
        $search = strtolower(trim($id));
        $counties = Counties::whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])->get();
        if ($counties->isEmpty()) {
            return response()->json(['message' => 'County not found'], 404);
        }

        return response()->json(['counties' => $counties]);
    } else {
        $county = Counties::where('id', $id)->first();
        if (!$county) {
            return response()->json(['message' => 'County not found'], 404);
        }

        return response()->json(['county' => $county]);
    }
}

    
}