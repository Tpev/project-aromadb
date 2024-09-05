<?php
namespace App\Http\Controllers;

use App\Models\HuileHE;
use App\Models\HuileHV;
use App\Models\Recette;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, $type, $id)
    {
        $user = auth()->user();
        $modelClass = $this->getModelClass($type);

        // Find the favoritable model
        $favoritable = $modelClass::findOrFail($id);

        // Check if the favorite already exists
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('favoritable_type', $modelClass)
            ->where('favoritable_id', $id)
            ->first();

        if ($existingFavorite) {
            // If it exists, remove it
            $existingFavorite->delete();
            return response()->json(['success' => true, 'action' => 'removed']);
        } else {
            // Otherwise, add it
            $user->favorites()->create([
                'user_id' => $user->id,
                'favoritable_id' => $id,
                'favoritable_type' => $modelClass,
            ]);
            return response()->json(['success' => true, 'action' => 'added']);
        }
    }

    // Helper method to resolve model class based on type
    private function getModelClass($type)
    {
        switch ($type) {
            case 'huilehe':
                return HuileHE::class;
            case 'huilehv':
                return HuileHV::class;
            case 'recette':
                return Recette::class;
            default:
                abort(404, 'Unknown type');
        }
    }
}
