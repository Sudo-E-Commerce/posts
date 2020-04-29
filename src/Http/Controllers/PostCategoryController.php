<?php

namespace Sudo\Post\Http\Controllers;
use Sudo\Base\Http\Controllers\AdminController;

use Illuminate\Http\Request;
use ListData;
use Form;
use ListCategory;

class PostCategoryController extends AdminController
{
    function __construct() {
        $this->models = new \Sudo\Post\Models\PostCategory;
        $this->table_name = $this->models->getTable();
        $this->module_name = 'Danh mục bài viết';
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
        $listdata = new \Sudo\Category\MyClass\ListDataCategory($requests, $this->models, 'Post::post_categories.table', $this->has_locale);
        // Build Form tìm kiếm
        $listdata->search('name', 'Tên', 'string');
        $listdata->search('created_at', 'Ngày tạo', 'range');
        $listdata->search('status', 'Trạng thái', 'array', config('app.status'));
        // Build các button hành động
        $listdata->btnAction('status', 1, __('Table::table.active'), 'primary', 'fas fa-edit');
        $listdata->btnAction('status', 0, __('Table::table.no_active'), 'warning', 'fas fa-edit');
        $listdata->btnAction('delete_custom', -1, __('Table::table.trash'), 'danger', 'fas fa-trash');
        // Build bảng
        $listdata->add('image', 'Ảnh', 0);
        $listdata->add('name', 'Tên', 1);
        $listdata->add('order', 'Sắp xếp', 1, 'order');
        $listdata->add('', 'Thời gian', 0, 'time');
        $listdata->add('status', 'Trạng thái', 0, 'status');
        $listdata->add('', 'Language', 0, 'lang');
        $listdata->add('', 'Sửa', 0, 'edit');
        $listdata->add('', 'Xóa', 0, 'delete_custom');
        
        return $listdata->render();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // danh mục
        $categories = new ListCategory('post_categories', $this->has_locale, Request()->lang_locale ?? \App::getLocale());
        // Khởi tạo form
        $form = new Form;
        $form->text('name', '', 1, 'Tiêu đề');
        $form->slug('slug', '', 1, 'Đường dẫn');
        $form->select('parent_id', '', 0, 'Danh mục cha', $categories->data_select(), 0);
        $form->image('image', '', 0, 'Ảnh đại diện');
        $form->editor('detail', '', 0, 'Nội dung');
        $form->checkbox('status', 1, 1, 'Trạng thái');
        $form->action('edit');
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
        validateForm($requests, 'slug', 'Đường dẫn không được để trống.');
        validateForm($requests, 'slug', 'Đường dẫn đã bị trùng.', 'unique', 'unique:post_categories');
        // Các giá trị mặc định
        $status = 0;
        // Đưa mảng về các biến có tên là các key của mảng
        extract($requests->all(), EXTR_OVERWRITE);
        // Chuẩn hóa lại dữ liệu
        // Thêm vào DB
        $created_at = $updated_at = date('Y-m-d H:i:s');
        $compact = compact('parent_id','name','slug','image','detail','status','created_at','updated_at');
        $id = $this->models->createRecord($requests, $compact, $this->has_seo, true);
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
    public function edit($id)
    {
        // Dẽ liệu bản ghi hiện tại
        $data_edit = $this->models->where('id', $id)->first();
        // Ngôn ngữ bản ghi hiện tại
        $language_meta = \DB::table('language_metas')->where('lang_table', $this->table_name)->where('lang_table_id', $data_edit->id)->first();
        // danh mục ứng với ngôn ngữ
        $categories = new ListCategory('post_categories', $this->has_locale, $language_meta->lang_locale ?? null);
        // Khởi tạo form
        $form = new Form;
        $form->text('name', $data_edit->name, 1, 'Tiêu đề');
        $form->slug('slug', $data_edit->slug, 1, 'Đường dẫn', '', 'false');
        $form->select('parent_id', $data_edit->parent_id, 0, 'Danh mục cha', $categories->data_select(), 0, [ $data_edit->id ]);
        $form->image('image', $data_edit->image, 0, 'Ảnh đại diện');
        $form->editor('detail', $data_edit->detail, 0, 'Nội dung');
        $form->checkbox('status', $data_edit->status, 1, 'Trạng thái');
        $form->action('edit');
        // Hiển thị form tại view
        return $form->render('edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $requests, $id) {
        // Xử lý validate
        validateForm($requests, 'name', 'Tiêu đề không được để trống.');
        validateForm($requests, 'slug', 'Đường dẫn không được để trống.');
        validateForm($requests, 'slug', 'Đường dẫn đã bị trùng.', 'unique', 'unique:post_categories,slug,'.$id);
        // Lấy bản ghi
        $data_edit = $this->models->where('id', $id)->first();
        // Các giá trị mặc định
        $status = 0;
        // Đưa mảng về các biến có tên là các key của mảng
        extract($requests->all(), EXTR_OVERWRITE);
        // Chuẩn hóa lại dữ liệu
        // Các giá trị thay đổi
        $created_at = $updated_at = date('Y-m-d H:i:s');
        $compact = compact('parent_id','name','slug','image','detail','status','updated_at');
        // Cập nhật tại database
        $this->models->updateRecord($requests, $id, $compact, $this->has_seo);
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
        // Bản ghi cần xóa hiện tại
        $record = $this->models->find($id);
        // Toàn bộ bản ghi con của nó
        $child_record = $this->models->where('parent_id', $record->id)->get();
        // Mảng id của bản ghi con
        $child_record_array_id = $child_record->pluck('id');
        // Cập nhật parent_id của bản ghi con bằng bản ghi cha của bản ghi hiện tại
        $this->models->whereIn('id', $child_record_array_id)->update([
            'parent_id' => $record->parent_id ?? null,
        ]);
        // Cập nhật bản ghi hiện tại  không thuộc cha và có trạng thái xóa [-1]
        $this->models->where('id', $id)->update([
            'parent_id' => null,
            'status'    => -1,
        ]);
        // Trả về
        return [
            'status' => 1,
            'message' => __('Core::admin.delete_success')
        ];
    }
}
