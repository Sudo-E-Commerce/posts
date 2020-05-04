## Hướng dẫn sử dụng Sudo Post ##

**Giới thiệu:** Đây là package dùng để quản lý bài viết của SudoCms.

Mặc định package sẽ tạo ra giao diện quản lý cho toàn bộ bài viết và danh mục bài viết được đặt tại `/{admin_dir}/posts` và `/{admin_dir}/post_categories`, trong đó admin_dir là đường dẫn admin được đặt tại `config('app.admin_dir')`

### Cài đặt để sử dụng ###

- Package cần phải có base `sudo/core` để có thể hoạt động không gây ra lỗi
- Để có thể sử dụng Package cần require theo lệnh `composer require sudo/post`
- Chạy `php artisan migrate` để tạo các bảng phục vụ cho package

### Cấu hình tại Menu ###

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
 
- Vị trí cấu hình được đặt tại `config/SudoMenu.php`
- Để có thể hiển thị tại menu, chúng ta có thể đặt đoạn cấu hình trên tại `config('SudoMenu.menu')`

### Cấu hình tại Module ###
	
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

- Vị trí cấu hình được đặt tại `config/SudoModule.php`
- Để có thể phân quyền, chúng ta có thể đặt đoạn cấu hình trên tại `config('SudoModule.modules')`
 