<?php

namespace App\Http\Controllers;

use App\Models\BellHistory;
use Illuminate\Http\Request;

class BellHistoryController extends Controller
{
    public function history(Request $request)
    {
        // Use the same filtering logic in both methods
        $query = BellHistory::query();
    
        if ($request->has('hari') && $request->hari != '') {
            $query->where('hari', $request->hari);
        }
    
        if ($request->has('trigger_type') && $request->trigger_type != '') {
            $query->where('trigger_type', $request->trigger_type);
        }
    
        $histories = $query->orderBy('ring_time', 'desc')
                     ->paginate(20)
                     ->appends(request()->query()); // Preserve filter parameters
    
        return view('admin.bel.history', compact('histories'));
    }
    
    public function filterHistory(Request $request)
    {
        // Just call the history method to avoid code duplication
        return $this->history($request);
    }
    
    public function destroy($id)
    {
        $history = BellHistory::findOrFail($id);
        $history->delete();
        
        return redirect()->route('bel.history.index')
            ->with('success', 'History bel berhasil dihapus');
    }
}