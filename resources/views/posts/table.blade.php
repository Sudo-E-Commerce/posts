@include('Table::components.image',['image' => $value->getImage()])
@include('Table::components.link',['text' => $value->name, 'url' => route('admin.posts.edit', $value->id)])