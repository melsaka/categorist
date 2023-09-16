<?php

namespace Melsaka\Categorist\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use NodeTrait;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function getTable()
    {
        $tableName = config('categorist.table', 'categories');

        return $tableName;
    }

    public function categorized()
    {
        return $this->morphedByMany($this->type, 'categorized', config('categorist.morph_table', 'categorized'));
    }

    public static function add(string $model, array $data, bool $getIfExist = true) 
    {
        $data['type'] = $model;

        $category = null;

        if ($getIfExist) {
            $category = static::where(['type' => $model, 'slug' => $data['slug']])->first();
        }

        if (array_key_exists('children', $data)) {
            $data['children'] = self::assigntypeToChildren($data['children'], $model);
        }

        return $category ?: static::create($data);
    }
     
     public static function isNew(Category $category) 
    {
        if (array_key_exists('_rgt', $category->attributes)) {
            return false;
        }

        return true;
    }

    public static function edit(Category $category, array $data): bool
    {
        return $category->update($data);
    }
        
    public static function remove(Category $category): bool
    {
        return $category->delete();
    }

    public static function syncWith(Model $model, $category): bool
    {
        $ids = self::getIds($category, get_class($model));

        $synced = $model->categories()->sync($ids);

        return self::isSyncedNotEmpty($synced);
    }

    public static function has(Model $model, $category): bool
    {
        $ids = self::getIds($category, get_class($model), true);

        return $model->categories->whereIn('id', $ids)->count();
    }

    public static function list(string $model) 
    {
        return static::where('type', $model)->get();
    }

    public static function treeList(string $model) 
    {
        return static::list($model)->toTree();
    }

    public static function ofThis(Model $model, bool $tree = true) 
    {
        return $tree ? $model->categories->toTree(): $model->categories;
    }
    
    private static function assigntypeToChildren($children, $model) 
    {
        foreach ($children as $key => $child) {
            $children[$key]['type'] = $model;

            if (array_key_exists('children', $children[$key])) {
                $children[$key]['children'] = self::assigntypeToChildren($children[$key]['children'], $model);
            }
        }

        return $children;
    }

    public static function getIds($target, string $model, bool $onlyParent = false) 
    {
        if (is_int($target)) {
            return self::getIdsForInteger($target, $onlyParent);
        }

        if (is_string($target)) {
            return self::getIdsForString($target, $model, $onlyParent);
        }

        if ($target instanceof Model) {
            return self::getIdsForModel($target, $onlyParent);
        }

        if ($target instanceof Collection || is_array($target)) {
            return self::getIdsForCollectionOrArray($target, $model, $onlyParent);
        }
        
        return [];
    }

    public static function isSyncedNotEmpty($synced) {
        $notEmpty = false;

        foreach ($synced as $array) {
            empty($array) ?: $notEmpty = true;
        }

        return $notEmpty;
    }

    private static function getIdsForInteger($target, $onlyParent) 
    {
        return $onlyParent ? [$target] : self::getDescendantsAndMerge($target);
        return $onlyParent ? [$target] : Category::descendantsAndSelf($target)->pluck('id')->toArray();
    }
        
    private static function getIdsForString($target, $model, $onlyParent) 
    {
        $category = Category::where('slug', $target)->where('type', $model)->first();

        if (!$category) {
            return [];
        }

        return $onlyParent ? [$category->id] : Category::descendantsAndSelf($category->id)->pluck('id')->toArray();
    }

    private static function getIdsForModel($target, $onlyParent) 
    {
        return $onlyParent ? [$target->id] : self::getDescendantsAndMerge($target->id);
    }

    private static function getIdsForCollectionOrArray($target, $onlyParent, $model) 
    {
        $ids = [];

        foreach ($target as $category) {
            $categoryIds = self::getIds($category, $model, $onlyParent);
        }

        return $ids;
    }

    public static function getDescendantsAndMerge($id, $ids = []) 
    {
        $categoryIds = Category::descendantsAndSelf($id)->pluck('id')->toArray();
        return array_merge($ids, $categoryIds);
    }
}
