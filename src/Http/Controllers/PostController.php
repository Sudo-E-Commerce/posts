<?php

namespace Sudo\Post\Http\Controllers;
use Sudo\Base\Http\Controllers\AdminController;

use Illuminate\Http\Request;
use ListData;
use Form;
use ListCategory;

class PostController extends AdminController
{
    function __construct() {
        $this->models = new \Sudo\Post\Models\Post;
        $this->table_name = $this->models->getTable();
        $this->module_name = 'Bài viết';
        $this->has_seo = true;
        $this->has_locale = true;
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $requests) {
        $listdata = new ListData($requests, $this->models, 'Post::posts.table', $this->has_locale);
        // Build Form tìm kiếm
        $listdata->search('name', 'Tên', 'string');
        $listdata->search('status', 'Trạng thái', 'array', config('app.status'));
        // Build các button hành động
        $listdata->btnAction('status', 1, __('Table::table.active'), 'primary', 'fas fa-edit');
        $listdata->btnAction('status', 0, __('Table::table.no_active'), 'warning', 'fas fa-edit');
        $listdata->btnAction('delete', -1, __('Table::table.trash'), 'danger', 'fas fa-trash');
        // Build bảng
        $listdata->add('image', 'Ảnh', 0);
        $listdata->add('name', 'Tên', 1);
        $listdata->add('', 'Thời gian', 0, 'time');
        $listdata->add('status', 'Trạng thái', 1, 'status');
        $listdata->add('', 'Language', 0, 'lang');
        $listdata->add('', 'Sửa', 0, 'edit');
        $listdata->add('', 'Xóa', 0, 'delete');

        return $listdata->render();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        // Danh mục
        $categories = new ListCategory('post_categories', $this->has_locale, Request()->lang_locale ?? \App::getLocale());
        // Khởi tạo form
        $form = new Form;
        $form->text('name', '', 1, 'Tiêu đề');
        $form->slug('slug', '', 1, 'Đường dẫn');
        $form->multiCheckbox('category_id', [], 1, 'Danh mục', $categories->data(), 'Chọn nhiều danh mục');
        $form->image('image', '', 0, 'Ảnh đại diện');
        $form->editor('detail', '', 0, 'Nội dung');
        $form->tags('tags', [], 0, 'Tags', 'Điền tên tags và nhấn Thêm');
        $form->checkbox('status', 1, 1, 'Trạng thái');
        $form->action('add');
        // Hiển thị form tại view
        return $form->render('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $requests)
    {
        // Xử lý validate
        validateForm($requests, 'name', 'Tiêu đề không được để trống.');
        validateForm($requests, 'category_id', 'Danh mục không được để trống.');
        validateForm($requests, 'slug', 'Đường dẫn không được để trống.');
        validateForm($requests, 'slug', 'Đường dẫn đã bị trùng.', 'unique', 'unique:posts');
        // Các giá trị mặc định
        $status = 0;
        // Đưa mảng về các biến có tên là các key của mảng
        extract($requests->all(), EXTR_OVERWRITE);
        // Chuẩn hóa lại dữ liệu

        // Thêm vào DB
        $created_at = $updated_at = date('Y-m-d H:i:s');
        $compact = compact('name','slug','image','detail','status','created_at','updated_at');
        $id = $this->models->createRecord($requests, $compact, $this->has_seo, true);
        // Cập nhật tags
        if (isset($tags) && !empty($tags)) {
            tags($tags, $this->table_name, $id);
        }
        // Cập nhật danh mục
        $this->categoryHandle($requests, $id);
        // Điều hướng
        return redirect(route('admin.'.$this->table_name.'.'.$redirect, $id))->with([
            'type' => 'success',
            'message' => __('Core::admin.create_success')
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        // Dẽ liệu bản ghi hiện tại
        $data_edit = $this->models->where('id', $id)->first();
        // Danh mục
        $post_category_maps = \DB::table('post_category_maps')->where('post_id', $data_edit->id)->get()->pluck('post_category_id')->toArray();
        // Ngôn ngữ bản ghi hiện tại
        $language_meta = \DB::table('language_metas')->where('lang_table', $this->table_name)->where('lang_table_id', $data_edit->id)->first();
        // danh mục ứng với ngôn ngữ
        $categories = new ListCategory('post_categories', $this->has_locale, $language_meta->lang_locale ?? null);
        // Khởi tạo form
        $form = new Form;
        $form->text('name', $data_edit->name, 1, 'Tiêu đề');
        $form->slug('slug', $data_edit->slug, 1, 'Đường dẫn', '', 'false');
        $form->multiCheckbox('category_id', $post_category_maps, 1, 'Danh mục', $categories->data(), 'Chọn nhiều danh mục');
        $form->image('image', $data_edit->image, 0, 'Ảnh đại diện');
        $form->editor('detail', $data_edit->detail, 0, 'Nội dung');
        // Tags
        $tags = getTagList($this->table_name, $id)->pluck('name')->toArray();
        $form->tags('tags', $tags, 0, 'Tags', 'Điền tên tags và nhấn Thêm');

        $form->checkbox('status', $data_edit->status, 1, 'Trạng thái');
        $form->action('edit');
        // Hiển thị form tại view
        return $form->render('edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $requests
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $requests, $id) {
        // Xử lý validate
        validateForm($requests, 'name', 'Tiêu đề không được để trống.');
        validateForm($requests, 'category_id', 'Danh mục không được để trống.');
        validateForm($requests, 'slug', 'Đường dẫn không được để trống.');
        validateForm($requests, 'slug', 'Đường dẫn đã bị trùng.', 'unique', 'unique:posts,slug,'.$id);
        // Lấy bản ghi
        $data_edit = $this->models->where('id', $id)->first();
        // Các giá trị mặc định
        $status = 0;
        // Đưa mảng về các biến có tên là các key của mảng
        extract($requests->all(), EXTR_OVERWRITE);
        // Chuẩn hóa lại dữ liệu
        // Các giá trị thay đổi
        $created_at = $updated_at = date('Y-m-d H:i:s');
        $compact = compact('name','slug','image','detail','status','updated_at');
        // Cập nhật tại database
        $this->models->updateRecord($requests, $id, $compact, $this->has_seo);
        // Cập nhật tags
        if (isset($tags) && !empty($tags)) {
            tags($tags, $this->table_name, $id);
        }
        // Cập nhật danh mục
        $this->categoryHandle($requests, $id);
        // Điều hướng
        return redirect(route('admin.'.$this->table_name.'.'.$redirect, $id))->with([
            'type' => 'success',
            'message' => __('Core::admin.update_success')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Xử lý đa danh mục
     * @param  requests         $requests: dữ liệu form
     * @param  int              $id: ID post_id
     */
    public function categoryHandle($requests, $id) {
        // Đưa mảng về các biến có tên là các key của mảng
        extract($requests->all(), EXTR_OVERWRITE);
        // Kiểm tra có tồn tại category_id không
        if (isset($category_id) && !empty($category_id)) {
            // Xóa post_id hiện tại
            \DB::table('post_category_maps')->where('post_id', $id)->delete();
            // Thêm mảng check mới
            $post_category_maps = [];
            foreach ($category_id as $post_category_id) {
                $post_category_maps[] = [
                    'post_id' => $id,
                    'post_category_id' => $post_category_id,
                ];
            }
            \DB::table('post_category_maps')->insert($post_category_maps);
        }
    }
}
