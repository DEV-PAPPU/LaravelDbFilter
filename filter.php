public function adsCollection(Request $request){
        $paginate = $request->paginate ?? 10;
        $filter_by = $request->filter_by ?? false;
        $sort_by = $request->sort_by ?? false;
        $query = Ad::with('city', 'category')->where('status', '!=', 'expired');

         // Category filter
         if ($request->has('category') && $request->category != null) {
             $category = $request->category;

             $query->whereHas('category', function ($q) use ($category) {
                 $q->where('slug', $category);
                });
            }

        // Subcategory filter
        if ($request->has('subcategory') && $request->subcategory != null) {
            $subcategory = $request->subcategory;

            $query->whereHas('subcategory', function ($q) use ($subcategory) {
                $q->whereIn('slug', $subcategory);
            });
        }

        // Keyword search
        if ($request->has('keyword') && $request->keyword != null) {
            $query->where('title', 'LIKE', "%$request->keyword%");
        }

        // City filter
        if ($request->has('city') && $request->city != null) {
            $query->whereHas('city', function ($q) {
                $q->where('name', request('city'));
            });
        }

        // Town filter
        if ($request->has('town') && $request->town != null) {
            $query->whereHas('town', function ($q) {
                $q->where('name', request('town'));
            });
        }

        // Condition filter
        if ($request->has('condition') && $request->condition != null) {
            $query->where('condition', $request->condition);
        }

        // Price filter
        if ($request->has('price_min') && $request->price_min != null) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && $request->price_max != null) {
            $query->where('price', '<=', $request->price_max);
        }

        // Filter by ads
        if ($filter_by && $filter_by == 'featured') {
            $query->where('featured', 1);
        }else if($filter_by && $filter_by == 'popular'){
            $query->latest('total_views');
        }

        // Sort by ads
        if ($sort_by && $sort_by == 'latest') {
            $query->latest();
        }else if($sort_by && $sort_by == 'oldest'){
            $query->oldest();
        }

        return [
            'ads' => $query->paginate($paginate)->withQueryString(),
            'adMaxPrice' => \DB::table('ads')->max('price'),
        ];
}