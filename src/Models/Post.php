<?php

namespace Sudo\Post\Models;

use Sudo\Base\Models\BaseModel;

class Post extends BaseModel
{
	public function queryAdmin($show_data, $requests) {
		// Lọc theo danh mục
		if (isset($requests->category_id)) {
			$search_value = $requests->category_id;
			$show_data = $show_data->join('post_category_maps', 'post_category_maps.post_id', 'id')
									->where('post_category_maps.post_category_id', $search_value)
									->select('posts.*');
		}
        return $show_data;
    }
}
