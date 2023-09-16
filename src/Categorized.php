<?php

namespace Melsaka\Categorist;

use Melsaka\Categorist\Models\Category;
use Illuminate\Support\Collection;

trait Categorized
{
    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorized', config('categorist.morph_table', 'categorized'))->where('type', get_class($this));
    }

    public function firstCategory()
    {
        return $this->categories->sortBy('parent_id')->first();
    }

    public function parentCategories()
    {
        return $this->categories->whereNull('parent');
    }

    public function treeCategories()
    {
        return $this->categories->toTree();
    }

    public function categoriesList($tree = false) 
    {
        return Category::ofThis($this, $tree);
    }
        
    public function attachCategories($categories): self
    {
        return $this->categoriesAction($categories, 'attach');
    }

    public function detachCategories($categories): self
    {
        return $this->categoriesAction($categories, 'detach');
    }

    public function syncCategories($categories): self
    {
        return $this->categoriesAction($categories, 'sync');
    }

    public function hasCategories(...$categories): bool
    {
        $categoriesIds = Category::getIds($categories, get_class($this), true); 

        return (bool) $this->categories->whereIn('id', $categoriesIds)->count();
    }

    public function hasAllCategories(...$categories) 
    {
        $categoriesIds = Category::getIds($categories, get_class($this), true); 

        $categoriesModelHave = $this->categories->whereIn('id', $categoriesIds)->pluck('id')->toArray();
        
        return $categoriesIds === $categoriesModelHave;
    }

    private function categoriesAction($categories, $method): self
    {
        $categoriesIds = Category::getIds($categories, get_class($this)); 

        if ($method === 'attach') {
            $categoriesIds = $this->filterCategoriesIds($categoriesIds);
        }

        $this->categories()->{$method}($categoriesIds);

        return $this;
    }

    private function filterCategoriesIds($categoriesIds) 
    {
        $existedCategoiresIds = $this->categories()
                                    ->whereIn('id', $categoriesIds)
                                    ->get()
                                    ->pluck('id')
                                    ->toArray();
        return array_diff($categoriesIds, $existedCategoiresIds);
    }
}
