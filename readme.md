# Hướng dẫn sử dụng Sudo Post #

## Cài đặt để sử dụng ##

## Cấu hình tại Menu ##

	[
    	'type' 				=> 'multiple',
    	'name' 				=> 'Bài viết',
		'icon' 				=> 'fas fa-file-alt',
		'childs' => [
			[
				'name' 		=> 'Thêm mới',
				'route' 	=> 'admin.posts.create',
				'role' 		=> 'posts_create'
			],
			[
				'name' 		=> 'Danh sách',
				'route' 	=> 'admin.posts.index',
				'role' 		=> 'posts_index',
				'active' 	=> [ 'admin.posts.show', 'admin.posts.edit' ]
			],
			[
				'name' 		=> 'Danh mục',
				'route' 	=> 'admin.post_categories.index',
				'role' 		=> 'post_categories_index',
				'active' 	=> [ 'admin.post_categories.create', 'admin.post_categories.show', 'admin.post_categories.edit' ]
			]
		]
    ],

## Cấu hình tại Module ##
	
	'posts' => [
		'name' 			=> 'Bài viết',
		'permision' 	=> [
			[ 'type' => 'index', 'name' => 'Truy cập' ],
			[ 'type' => 'create', 'name' => 'Thêm' ],
			[ 'type' => 'edit', 'name' => 'Sửa' ],
			[ 'type' => 'restore', 'name' => 'Lấy lại' ],
			[ 'type' => 'delete', 'name' => 'Xóa' ],
		],
	],
	'post_categories' => [
		'name' 			=> 'Danh mục bài viết',
		'permision' 	=> [
			[ 'type' => 'index', 'name' => 'Truy cập' ],
			[ 'type' => 'create', 'name' => 'Thêm' ],
			[ 'type' => 'edit', 'name' => 'Sửa' ],
			[ 'type' => 'restore', 'name' => 'Lấy lại' ],
			[ 'type' => 'delete', 'name' => 'Xóa' ],
		],
	],

## Publish ##

## Sử dụng ##
