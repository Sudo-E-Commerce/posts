@include('Table::components.image',['image' => $value->getImage()])
@include('Table::components.link',['text' => $value->name, 'url' => route('admin.posts.edit', $value->id)])
<td style="width: 200px;">
	@php
		$post_category_maps = collect($post_category_maps);
		$post_categorie_map = $post_category_maps->where('post_id', $value->id);
	@endphp
	@if (isset($post_categorie_map) && !empty($post_categorie_map))
		@foreach ($post_categorie_map as $category)
			<a href="{{ route('admin.post_categories.edit', $category->post_category_id) }}" class="btn btn-xs btn-success float-left text-white mr-1 mb-1" target="_blank">{{ $post_categories[$category->post_category_id] ?? '' }}</a>
		@endforeach
	@endif
</td>