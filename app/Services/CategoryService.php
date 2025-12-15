<?php

namespace App\Services;

use App\Models\Category;
use App\Http\Requests\AiTalkHistoryResource;
use App\Http\Requests\StoreAiTalkHistoryRequest;
use App\Models\AiTalkHistory;

class CategoryService
{
    /**
     * @param Category
     * 'name' => 'string',
     * 'sort_order' => 'integer',
     * 'color' => 'string',
     * 'user_id' => 'integer'
     */
    public function get(int $userId)
    {
        $categories = json_encode(Category::orderBy("updated_at", "desc")->get());
        return $categories;
    }

    public function create($data)
    {
        $categories = Category::create($data);
        return response()->json($categories);;
    }

    public function update($data, Category $category)
    {
        $categories = $category->update($data);
        return response()->json($categories);
    }

    public function delete(Category $category)
    {
        $result = $category->delete();
        return response()->json($result);
    }
}
