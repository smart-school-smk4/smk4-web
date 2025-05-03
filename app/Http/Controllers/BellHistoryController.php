<?php

namespace App\Http\Controllers;

use App\Models\BellHistory;
use Illuminate\Http\Request;

class BellHistoryController extends Controller
{
    public function history()
    {
        $histories = BellHistory::orderBy('ring_time', 'desc')
                     ->paginate(20);
        
        return view('admin.bel.history', compact('histories'));
    }
    
    public function filterHistory(Request $request)
    {
        $query = BellHistory::query();
    
        if ($request->has('hari')) {
            $query->where('hari', $request->hari);
        }
    
        if ($request->has('trigger_type')) {
            $query->where('trigger_type', $request->trigger_type);
        }
    
        $histories = $query->orderBy('ring_time', 'desc')
                     ->paginate(20);
    
        return view('admin.bel.history', compact('histories'));
    }
    
    public function destroy($id)
    {
        $history = BellHistory::findOrFail($id);
        $history->delete();
        
        return redirect()->route('bel.history.index')
            ->with('success', 'History bel berhasil dihapus');
    }
}