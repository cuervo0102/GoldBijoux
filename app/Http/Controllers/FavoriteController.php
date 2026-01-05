<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with(['product.category'])
            ->get();
        
        $products = $favorites->map(function($favorite) {
            return $favorite->product;
        });

        return response()->json([
            'success' => true,
            'data' => $products,  
            'count' => $products->count()
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $exists = Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Produit déjà dans les favoris'
            ], 400);
        }

        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté aux favoris',
            'data' => $favorite->load('product')
        ], 201);
    }

    public function destroy(Request $request, $productId)
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé dans les favoris'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit retiré des favoris'
        ], 200);
    }

    public function check(Request $request, $productId)
    {
        $isFavorite = Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite
        ], 200);
    }

    public function clear(Request $request)
    {
        Favorite::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tous les favoris ont été supprimés'
        ], 200);
    }
}
