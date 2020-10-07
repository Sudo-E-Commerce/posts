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

        $post_categories = new ListCategory('post_categories');
        $this->post_categories = $post_categories->data();
        \View::share('post_categories', $this->post_categories);
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
        $listdata->search('category_id', 'Danh mục', 'array', $this->post_categories);
        $listdata->search('created_at', 'Ngày tạo', 'range');
        $listdata->search('status', 'Trạng thái', 'array', config('app.status'));
        // Build các button hành động
        $listdata->btnAction('status', 1, __('Translate::table.active'), 'success', 'fas fa-edit');
        $listdata->btnAction('status', 0, __('Translate::table.no_active'), 'info', 'fas fa-window-close');
        $listdata->btnAction('delete', -1, __('Translate::table.trash'), 'danger', 'fas fa-trash');
        // Build bảng
        $listdata->add('image', 'Ảnh', 0);
        $listdata->add('name', 'Tên', 1);
        $listdata->add('category_id', 'Danh mục', 0);
        $listdata->add('home', 'Ghim trang chủ', 1, 'pin');
        $listdata->add('', 'Thời gian', 0, 'time');
        $listdata->add('status', 'Trạng thái', 1, 'status');
        $listdata->add('', 'Language', 0, 'lang');
        $listdata->add('', 'Sửa', 0, 'edit');
        $listdata->add('', 'Xóa', 0, 'delete');
        // Lấy dữ liệu data
        $data = $listdata->data();
        $show_data = $data['show_data'];
        $show_data_array_id = $show_data->pluck('id')->toArray();
        // Từ dữ liệu data ở trên lấy ra toàn bộ danh mục thuộc các bài viết
        $post_category_maps = \Sudo\Post\Models\PostCategoryMap::whereIn('post_id', $show_data_array_id)->get();
        // Trả về views
        return $listdata->render(compact('data', 'post_category_maps'));
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
        $form->card('col-lg-9');
            $form->lang($this->table_name);
            $form->text('name', '', 1, 'Tiêu đề');
            $form->slug('slug', '', 1, 'Đường dẫn');
            $form->editor('detail', '', 0, 'Nội dung');
            $form->tags('tags', [], 0, 'Tags', 'Điền tên tags và nhấn Thêm');
        $form->endCard();
        $form->card('col-lg-3', '');
            $form->action('add');
            $form->radio('status', 1, 'Trạng thái', config('app.status'));
            $form->datetimepicker('created_at', date('Y-m-d H:i:s'), 0, 'Thời gian đăng bài');
            $form->multiCheckbox('category_id', [], 1, 'Danh mục', $categories->data(), 'Chọn nhiều danh mục');
            $form->image('image', '', 0, 'Ảnh đại diện');
        $form->endCard();
        // Hiển thị form tại view
        $form->hasFullForm();
        return $form->render('create_multi_col');
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
        $created_at = $created_at ?? date('Y-m-d H:i:s');
        // Thêm vào DB
        $updated_at = date('Y-m-d H:i:s');
        $compact = compact('name','slug','image','detail','status','created_at','updated_at');
        $id = $this->models->createRecord($requests, $compact, $this->has_seo, $this->has_locale);
        // Cập nhật tags
        tags($tags ?? [], $this->table_name, $id);
        // Cập nhật danh mục
        $this->categoryHandle($requests, $id);
        // Điều hướng
        return redirect(route('admin.'.$this->table_name.'.'.$redirect, $id))->with([
            'type' => 'success',
            'message' => __('Translate::admin.create_success')
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

        $form->card('col-lg-9');
            $form->text('name', $data_edit->name, 1, 'Tiêu đề');
            $form->slug('slug', $data_edit->slug, 1, 'Đường dẫn', '', 'false');
            $form->editor('detail', $data_edit->detail, 0, 'Nội dung');
            // Tags
            $tags = getTagList($this->table_name, $id)->pluck('name')->toArray();
            $form->tags('tags', $tags, 0, 'Tags', 'Điền tên tags và nhấn Thêm');
        $form->endCard();
        $form->card('col-lg-3', '');
            // lấy link xem
            $link = (config('app.post_models')) ? config('app.post_models')::where('id', $id)->first()->getUrl() : '';
            $form->action('edit', $link);
            $form->radio('status', $data_edit->status, 'Trạng thái', config('app.status'));
            $form->datetimepicker('created_at', $data_edit->created_at, 0, 'Thời gian đăng bài');
            $form->multiCheckbox('category_id', $post_category_maps, 1, 'Danh mục', $categories->data(), 'Chọn nhiều danh mục');
            $form->image('image', $data_edit->image, 0, 'Ảnh đại diện');
        $form->endCard();
        // Hiển thị form tại view
        $form->hasFullForm();
        // Hiển thị form tại view
        return $form->render('edit_multi_col', compact('id'));
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
        $created_at = $created_at ?? date('Y-m-d H:i:s');
        // Các giá trị thay đổi
        $updated_at = date('Y-m-d H:i:s');
        $compact = compact('name', 'slug', 'image', 'detail', 'status', 'created_at', 'updated_at');
        // Cập nhật tại database
        $this->models->updateRecord($requests, $id, $compact, $this->has_seo);
        // Cập nhật tags
        tags($tags ?? [], $this->table_name, $id);
        // Cập nhật danh mục
        $this->categoryHandle($requests, $id);
        // Điều hướng
        return redirect(route('admin.'.$this->table_name.'.'.$redirect, $id))->with([
            'type' => 'success',
            'message' => __('Translate::admin.update_success')
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
