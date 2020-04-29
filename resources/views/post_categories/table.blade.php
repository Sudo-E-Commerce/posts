@include('Table::components.image', ['image' => getImage($value->image ?? '')])
@php
	$prefix = '';
	for ($i=0; $i < $value->level; $i++) { 
		$prefix .= '|— ';
	}
@endphp
@include('Table::components.link', ['text' => $prefix.$value->name, 'url' => route('admin.post_categories.edit', $value->id)])