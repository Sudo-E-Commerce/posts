<?php

namespace Sudo\Post\Models;

use Sudo\Base\Models\BaseModel;

class PostCategory extends BaseModel
{
    public function parents() {
    	$table_name = self::getTable();
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenParents() {
    	$table_name = self::getTable();
        return $this->hasMany(self::class, 'parent_id')
        			->with('childrenParents')
        			->join('language_metas', 'language_metas.lang_table_id', $table_name.'.id')
        			->where('lang_table', self::getTable());
    }
}
