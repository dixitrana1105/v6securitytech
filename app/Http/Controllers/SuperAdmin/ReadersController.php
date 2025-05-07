<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reader;
use App\Models\Building_Master;


class ReadersController extends Controller
{
    public function index_reader()
    {
        $readers = Reader::with('building')->latest('id')->paginate(10);
        return view('super-admin.reader.index', compact('readers'));
    }

    public function create_reader()
    {
        $buildings = Building_Master::all();
        return view('super-admin.reader.create', compact('buildings'));
    }

    public function store_reader(Request $request)
    {
        $request->validate([
            'building_id' => 'required',
            'serial_id' => 'required|unique:cards,serial_id'
        ]);
        Reader::create($request->all());
        return redirect()->route('super-admin.reader-index')->with('success', 'Reader created successfully.');
    }

    public function show(Reader $reader)
    {
        return view('super-admin.reader.show', compact('reader'));
    }

    public function edit_reader($id)
    {
        $reader = Reader::findOrFail($id);
        $buildings = Building_Master::all();
        return view('super-admin.reader.edit', compact('buildings', 'reader'));
    }

    public function update(Request $request, $id)
    {
        $reader = Reader::findOrFail($id);

        $request->validate([
            'building_id' => 'required',
            'serial_id' => 'required|unique:cards,serial_id,' . $reader->id,
            // 'assign_status' => 'required|in:assigned,unassigned',
        ]);
        $reader->update($request->all());
        return redirect()->route('super-admin.reader-index')->with('success', 'reader updated successfully.');
    }

    public function destroy(Reader $card)
    {
        $card->delete();
        return redirect()->route('super-admin.reader-index')->with('success', 'Card deleted successfully.');
    }

}

?>
